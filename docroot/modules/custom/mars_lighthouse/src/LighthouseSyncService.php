<?php

namespace Drupal\mars_lighthouse;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\file\FileInterface;
use Drupal\mars_lighthouse\Controller\LighthouseAdapter;
use Drupal\media\MediaInterface;

/**
 * Class LighthouseSyncService.
 */
class LighthouseSyncService {

  use DependencySerializationTrait;

  /**
   * Date format required by API.
   */
  const DATE_FORMAT = 'Y-m-d-H-i-s T';

  /**
   * Lighthouse bundle name.
   */
  const LIGHTHOUSE_IMAGE_BUNDLE = 'lighthouse_image';

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
   * Media Type.
   *
   * @var string
   */
  protected $mediaType = 'image';

  /**
   * Media Type config array.
   *
   * @var array
   */
  private $mediaConfig = [
    'image' => [
      'bundle' => 'lighthouse_image',
      'field' => 'field_media_image',
    ],
    'video' => [
      'bundle' => 'lighthouse_video',
      'field' => 'field_media_video_file_1',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    LighthouseClientInterface $lighthouse_client,
    LighthouseInterface $lighthouse,
    FileSystemInterface $file_system,
    LoggerChannelFactoryInterface $logger_factory,
    StateInterface $state
  ) {
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->configFactory = $config_factory;
    $this->lighthouseClient = $lighthouse_client;
    $this->mapping = $this->configFactory->get(LighthouseAdapter::CONFIG_NAME);
    $this->lighthouseAdapter = $lighthouse;
    $this->fileSystem = $file_system;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('mars_lighthouse');
    $this->state = $state;
  }

  /**
   * Sync media bulk.
   */
  public function syncLighthouseSiteBulk() {
    $media_objects = $this->mediaStorage->loadByProperties([
      'bundle' => self::LIGHTHOUSE_IMAGE_BUNDLE,
    ]);
    if (!empty($media_objects)) {
      $assets_ids = [];
      foreach ($media_objects as $media) {
        $assets_ids[] = $media->field_external_id->value;
      }

      $params = $this->lighthouseAdapter->getToken();
      try {
        $data = $this->lighthouseClient->getAssetsByIds($assets_ids, $this->getLatestModifiedDate(), $params);
      }
      catch (TokenIsExpiredException $e) {
        // Try to refresh token.
        $params = $this->lighthouseAdapter->refreshToken();
        $data = $this->lighthouseClient->getAssetsByIds($assets_ids, $this->getLatestModifiedDate(), $params);
      }
      catch (LighthouseAccessException $e) {
        // Try to force request new token.
        $params = $this->lighthouseAdapter->getToken(TRUE);
        $data = $this->lighthouseClient->getAssetsByIds($assets_ids, $this->getLatestModifiedDate(), $params);
      }
      $external_ids = [];
      foreach ($data as $item) {
        $media_objects = $this->mediaStorage->loadByProperties([
          'bundle' => self::LIGHTHOUSE_IMAGE_BUNDLE,
          'field_external_id' => $item['assetId'],
        ]);
        foreach ($media_objects as $media) {
          if (isset($item['versionIdOTMM']) &&
            $item['versionIdOTMM'] != $media->field_version_id->value) {
            $external_ids[] = $media->field_external_id->value;
            $this->updateMediaData($media, $item);
          }
        }
      }
      if ($external_ids) {
        $this->state->set('system.sync_lighthouse_last', date('m/d/Y'));
        $this->logger->info($this->t('@count results processed. List of entities with external ids were updated @external_ids', [
          '@count' => count($data),
          '@external_ids' => implode(', ', array_unique($external_ids)),
        ]));
      }
    }
  }

  /**
   * Get latest modified date.
   */
  public function getLatestModifiedDate() {
    if ($this->state->get('system.sync_lighthouse_last')) {
      $date = $this->state->get('system.sync_lighthouse_last');
    }
    else {
      $array_last_modified = [];
      $media_objects = $this->mediaStorage->loadByProperties([
        'bundle' => self::LIGHTHOUSE_IMAGE_BUNDLE,
      ]);
      foreach ($media_objects as $media) {
        $array_last_modified[] = $media->field_last_mod_date->value;
      }
      $latest_modified_date = min($array_last_modified);
      $date = \DateTime::createFromFormat(self::DATE_FORMAT, $latest_modified_date);
      $date = $date->format('m/d/Y');
    }
    return $date;
  }

  /**
   * Sync media.
   */
  public function syncLighthouseSite(bool $drush = FALSE) {
    $media_objects = $this->mediaStorage->loadByProperties([
      'bundle' => self::LIGHTHOUSE_IMAGE_BUNDLE,
    ]);
    if (!empty($media_objects)) {
      $operations = [];
      $numOperations = 0;
      $batchId = 1;
      foreach ($media_objects as $media_object) {
        $mid = $media_object->id();
        $operations[] = [
          [$this, 'processMediaSync'],
          [
            $mid,
            $this->t('Media @media', ['@mid' => $mid]),
          ],
        ];
        $batchId++;
        $numOperations++;
      }
      $batch = [
        'title' => $this->t('Updating @num node(s)', ['@num' => $numOperations]),
        'operations' => $operations,
        'finished' => [$this, 'processMediaSyncFinished'],
      ];

      batch_set($batch);
      if ($drush) {
        drush_backend_batch_process();
      }
      else {
        $batch =& batch_get();
        $batch['progressive'] = FALSE;
        batch_process();
      }
    }

  }

  /**
   * Batch process callback.
   *
   * @param int $mid
   *   Id of the media entity.
   * @param string $operation_details
   *   Details of the operation.
   * @param object $context
   *   Context for operations.
   */
  public function processMediaSync($mid, $operation_details, &$context) {
    $media = $this->mediaStorage->load($mid);
    if ($media instanceof MediaInterface) {
      $external_id = $media->field_external_id->value;
      $params = $this->lighthouseAdapter->getToken();
      try {
        $data = $this->lighthouseClient->getAssetById($external_id, $params);
      }
      catch (TokenIsExpiredException $e) {
        // Try to refresh token.
        $params = $this->lighthouseAdapter->refreshToken();
        $data = $this->lighthouseClient->getAssetById($external_id, $params);
      }
      catch (LighthouseAccessException $e) {
        // Try to force request new token.
        $params = $this->lighthouseAdapter->getToken(TRUE);
        $data = $this->lighthouseClient->getAssetById($external_id, $params);
      }

      if (!empty($data) &&
        isset($data['versionIdOTMM']) &&
        $data['versionIdOTMM'] != $media->field_version_id->value) {
        $this->updateMediaData($media, $data);
        $context['results'][$mid] = $external_id;
        // Optional message displayed under the progressbar.
        $context['message'] = $this->t('Running Batch "@mid" @details',
          ['@mid' => $mid, '@details' => $operation_details]
        );
      }
    }
  }

  /**
   * Batch Finished callback.
   *
   * @param bool $success
   *   Success of the operation.
   * @param array $results
   *   Array of results for post processing.
   * @param array $operations
   *   Array of operations.
   */
  public function processMediaSyncFinished($success, array $results, array $operations) {
    if ($success) {
      // Here we could do something meaningful with the results.
      // We just display the number of nodes we processed...
      $this->logger->info($this->t('@count results processed. List of entities with external ids were updated @external_ids', [
        '@count' => count($results),
        '@external_ids' => implode(', ', array_unique($results)),
      ]));
    }
    else {
      // An error occurred.
      // $operations contains the operations that remained unprocessed.
      $error_operation = reset($operations);
      $this->logger->error($this->t('An error occurred while processing @operation with arguments : @args',
        [
          '@operation' => $error_operation[0],
          '@args' => print_r($error_operation[0], TRUE),
        ]
      ));
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
  public function updateMediaData(MediaInterface $media, array $data) {
    if (!$data) {
      return NULL;
    }

    $file_mapping = $this->mapping->get('media');
    $field_config = $this->mediaConfig[$this->mediaType];
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

}
