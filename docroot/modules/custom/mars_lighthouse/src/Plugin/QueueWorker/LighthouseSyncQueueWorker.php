<?php

namespace Drupal\mars_lighthouse\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mars_lighthouse\Controller\LighthouseAdapter;
use Drupal\mars_lighthouse\LighthouseClientInterface;
use Drupal\mars_lighthouse\LighthouseException;
use Drupal\mars_lighthouse\LighthouseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;

/**
 * Process a queue.
 *
 * @QueueWorker(
 *   id = "lighthouse_sync_queue",
 *   title = @Translation("Lighthouse sync queue worker"),
 *   cron = {"time" = 3600}
 * )
 */
class LighthouseSyncQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Date format required by API.
   */
  const DATE_FORMAT = 'Y-m-d-H-i-s T';

  /**
   * Media storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Lighthouse client.
   *
   * @var \Drupal\mars_lighthouse\LighthouseClientInterface
   */
  protected $lighthouseClient;

  /**
   * Fields mapping.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $mapping;

  /**
   * File entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $fileStorage;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Lighthouse adapter.
   *
   * @var \Drupal\mars_lighthouse\LighthouseInterface
   */
  protected $lighthouseAdapter;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Logger for this channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * A config object for the system performance configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Media Type config array.
   *
   * @var array
   */
  private $mediaConfig = [
    'lighthouse_image' => [
      'bundle' => 'lighthouse_image',
      'field' => 'field_media_image',
    ],
    'lighthouse_video' => [
      'bundle' => 'lighthouse_video',
      'field' => 'field_media_video_file_1',
    ],
  ];

  /**
   * LighthouseQueueWorker constructor.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    LighthouseClientInterface $lighthouse_client,
    LighthouseInterface $lighthouse,
    FileSystemInterface $file_system,
    LoggerChannelFactoryInterface $logger_factory,
    StateInterface $state) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->configFactory = $config_factory;
    $this->lighthouseClient = $lighthouse_client;
    $this->mapping = $config_factory->get(LighthouseAdapter::CONFIG_NAME);
    $this->lighthouseAdapter = $lighthouse;
    $this->fileSystem = $file_system;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('mars_lighthouse');
    $this->state = $state;
    $this->config = $config_factory->get('mars_lighthouse.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('lighthouse.client'),
      $container->get('lighthouse.adapter'),
      $container->get('file_system'),
      $container->get('logger.factory'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $sync_mode = $this->config->get('sync_mode');

    if ($sync_mode) {
      $this->syncLighthouseSiteBulk($data);
    }
    else {
      /** @var \Drupal\media\MediaInterface $media */
      foreach ($data as $media) {
        try {
          $this->processMediaSync($media);
        }
        catch (\Exception $exception) {
          $this->logger->error("Can't sync media with external id @external_id", [
            '@external_id' => $media->field_external_id->value,
          ]);
        }
      }
    }
  }

  /**
   * Update media entity from API response.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media item.
   * @param array $data
   *   Response data with one entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   Media entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateMediaData(MediaInterface $media, array $data): ?MediaInterface {
    if (!$data) {
      return NULL;
    }

    // Condition to prevent wrong image extensions like (*.psd, *.iso)
    // from lighthouse side.
    if ($media->bundle() === 'lighthouse_image') {
      $this->lighthouseAdapter->prepareImageExtension($data);
    }

    $file_mapping = $this->mapping->get('media');
    $field_config = $this->mediaConfig[$media->bundle()];
    $field_file = $field_config['field'];
    $fid = $media->$field_file->target_id;
    $this->updateFileEntity($fid, $data);

    foreach ($file_mapping as $field_name => $path_to_value) {
      $media->set($field_name, $data[$path_to_value]);
    }
    $media->save();

    return $media;
  }

  /**
   * Update file entity from API response.
   *
   * @param int $fid
   *   Id of the file entity.
   * @param array $data
   *   Response data with one entity.
   *
   * @return string
   *   ID of File entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function updateFileEntity($fid, array $data): string {
    $file = $this->fileStorage->load($fid);
    $this->clearFileCache($file);
    $file_mapping = $this->mapping->get('file');

    foreach ($file_mapping as $field_name => $path_to_value) {
      $path_to_value = explode('.', $path_to_value);
      $value = $data[array_shift($path_to_value)] ?? NULL;
      while ($path_to_value) {
        $value = $value[array_shift($path_to_value)] ?? NULL;
      }
      $file->set($field_name, $value);
    }

    $file->save();
    return $file->id();
  }

  /**
   * Clear file cache.
   */
  protected function clearFileCache(FileInterface $file) {
    // Get origin image URI.
    $image_uri = $file->getFileUri();
    $styles = $this->entityTypeManager->getStorage('image_style')->loadMultiple();
    /** @var \Drupal\image\ImageStyleInterface $style */
    foreach ($styles as $style) {
      // Get URI.
      $uri = $style->buildUri($image_uri);
      if (is_file($uri) && file_exists($uri)) {
        $this->fileSystem->unlink($uri);
      }
    }
  }

  /**
   * Get latest modified date.
   */
  public function getLatestModifiedDate(array $media_objects) {
    $date = date('m/d/Y');
    if ($this->state->get('system.sync_lighthouse_last')) {
      $date = $this->state->get('system.sync_lighthouse_last');
    }
    else {
      $array_last_modified = [];
      foreach ($media_objects as $media) {
        $last_mod_date = $media->field_last_mod_date->value;
        $date_object = \DateTime::createFromFormat(self::DATE_FORMAT, $last_mod_date);
        if ($date_object instanceof \DateTimeInterface) {
          $array_last_modified[] = $last_mod_date;
        }
      }
      if (!empty($array_last_modified)) {
        $latest_modified_date = min($array_last_modified);
        $date = \DateTime::createFromFormat(self::DATE_FORMAT, $latest_modified_date);
        $date = $date->format('m/d/Y');
      }
    }
    return $date;
  }

  /**
   * Sync media bulk.
   */
  public function syncLighthouseSiteBulk($media_objects) {
    $request_data = [];
    /* @var \Drupal\media\Entity\Media $media */
    foreach ($media_objects as $media) {
      if (!empty($media->field_external_id->value) && !empty($media->field_original_external_id->value)) {
        $request_data[$media->field_external_id->value] = $media->field_original_external_id->value;
      }
      elseif (!empty($media->field_external_id->value)) {
        $request_data[$media->field_external_id->value] = $media->field_external_id->value;
      }
    }

    $latest_modified_date = $this->getLatestModifiedDate($media_objects);

    try {
      $data = $this->lighthouseClient->getAssetsByIds($request_data, $latest_modified_date);
    }
    catch (LighthouseException $exception) {
      $this->logger->error('Failed to run sync getAssetsByIds "%error"', ['%error' => $exception->getMessage()]);
    }

    $external_ids = [];
    foreach ($data as $item) {
      $media_objects = $this->mediaStorage->loadByProperties([
        'field_original_external_id' => $item['origAssetId'],
      ]);
      foreach ($media_objects as $media) {
        $external_ids[] = $media->field_external_id->value;
        if (!empty($item) && isset($item['assetId'])) {
          $this->updateMediaData($media, $item);
        }
      }
    }
    if ($external_ids) {
      $this->state->set('system.sync_lighthouse_last', date('m/d/Y'));
      $this->logger->info($this->t('@count results processed. List of entities with external ids were updated @external_ids. Check date is @check_date.', [
        '@count' => count($data),
        '@external_ids' => implode(', ', array_unique($external_ids)),
        '@check_date' => $latest_modified_date,
      ]));
    }
    else {
      $this->logger->info($this->t('Checked chunk of media with non updated information. Check date is @check_date.', [
        '@check_date' => $latest_modified_date,
      ]));
    }
  }

  /**
   * Process media sync one by one.
   */
  public function processMediaSync(MediaInterface $media) {
    $external_id = $media->field_external_id->value;
    if (empty($external_id)) {
      $this->logger->info($this->t('Media with id: @media_id has empty field_external_id', [
        '@media_id' => $media->id(),
      ]));
      return [];
    }

    try {
      $data = $this->lighthouseClient->getAssetById($external_id);
    }
    catch (LighthouseException $exception) {
      $this->logger->error('Failed to run sync getAssetById "%error"', ['%error' => $exception->getMessage()]);
    }

    if (!empty($data) && isset($data['assetId'])) {
      $this->updateMediaData($media, $data);

      $this->logger->info($this->t('Result processed. Media with external id was updated @external_id', [
        '@external_id' => $external_id,
      ]));
    }
  }

}
