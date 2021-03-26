<?php

namespace Drupal\mars_lighthouse\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_lighthouse\LighthouseInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\mars_lighthouse\LighthouseClientInterface;
use Drupal\media\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Class LighthouseView.
 *
 * Provides render functions of lighthouse view.
 *
 * @package Drupal\mars_lighthouse\Controller
 */
class LighthouseAdapter extends ControllerBase implements LighthouseInterface {

  /**
   * Fields mapping name.
   */
  const CONFIG_NAME = 'mars_lighthouse.mapping';

  /**
   * Default image extension.
   */
  const DEFAULT_IMAGE_EXTENSION = '.jpeg';

  /**
   * Lighthouse client.
   *
   * @var \Drupal\mars_lighthouse\LighthouseClientInterface
   */
  protected $lighthouseClient;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Media entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $mediaStorage;

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
   * Media Type.
   *
   * @var string
   */
  protected $mediaType = 'image';

  /**
   * File entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $fileStorage;

  /**
   * Fields mapping.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $mapping;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('lighthouse.client'),
      $container->get('cache.default'),
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * LighthouseAdapter constructor.
   *
   * @param \Drupal\mars_lighthouse\LighthouseClientInterface $lighthouse_client
   *   Lighthouse API client.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache container.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    LighthouseClientInterface $lighthouse_client,
    CacheBackendInterface $cache,
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->lighthouseClient = $lighthouse_client;
    $this->entityTypeManager = $entity_type_manager;
    $this->mediaStorage = $this->entityTypeManager->getStorage('media');
    $this->fileStorage = $this->entityTypeManager->getStorage('file');
    $this->configFactory = $config_factory;
    $this->mapping = $this->configFactory->get(self::CONFIG_NAME);
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public function getMediaDataList(&$total_found, $text = '', $filters = [], $sort_by = [], $offset = 0, $limit = 12, $media_type = 'image'): array {
    $this->mediaType = $media_type;
    $response = $this->lighthouseClient->search($total_found, $text, $filters, $sort_by, $offset, $limit, $media_type);
    return $this->prepareMediaDataList($response);
  }

  /**
   * {@inheritdoc}
   */
  public function getMediaEntity($id): ?MediaInterface {
    if ($media = $this->mediaStorage->loadByProperties(['field_external_id' => $id])) {
      return array_shift($media);
    }
    $data = $this->lighthouseClient->getAssetById($id);
    try {
      return $this->createMediaEntity($data);
    }
    catch (EntityStorageException $e) {
      // Smth went wrong. API response was incorrect.
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getBrands(): array {
    if ($options = $this->cache->get('mars_lighthouse_brands')) {
      return $options->data;
    }
    $data = $this->lighthouseClient->getBrands();
    $options = ['' => '-- Any --'];
    foreach ($data as $v) {
      $options[$v] = $v;
    }

    $this->cache->set('mars_lighthouse_brands', $options, strtotime("+60 minutes"));
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getMarkets(): array {
    if ($options = $this->cache->get('mars_lighthouse_markets')) {
      return $options->data;
    }
    $data = $this->lighthouseClient->getMarkets();
    $options = ['' => '-- Any --'];
    foreach ($data as $v) {
      $options[$v] = $v;
    }

    $this->cache->set('mars_lighthouse_markets', $options, strtotime("+60 minutes"));
    return $options;
  }

  /**
   * Prepare search response for rendering.
   *
   * @param array $data
   *   Raw response array.
   *
   * @return array
   *   Array ready for render.
   *
   * @codeCoverageIgnore
   */
  protected function prepareMediaDataList(array $data) {
    $data_list = [];
    foreach ($data as $item) {
      $data_list[] = [
        'uri' => $item['urls']['001tnmd'] ?? NULL,
        'name' => $item['assetName'] ?? '',
        'assetId' => $item['assetId'] ?? '',
      ];
    }
    return $data_list;
  }

  /**
   * Creates media entity from API response.
   *
   * @param array $data
   *   Response data with one entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   Media entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @codeCoverageIgnore
   */
  protected function createMediaEntity(array $data): ?MediaInterface {
    if (!$data) {
      return NULL;
    }
    // Condition to prevent wrong image extensions like (*.psd, *.iso)
    // from lighthouse side.
    if ($this->mediaType === 'image') {
      $this->prepareImageExtension($data);
    }
    $file_mapping = $this->mapping->get('media');
    $file_id = $this->createFileEntity($data);

    $field_config = $this->mediaConfig[$this->mediaType];
    $fields_values = [
      'bundle' => $field_config['bundle'],
      $field_config['field'] => ['target_id' => $file_id],
      'status' => TRUE,
    ];

    foreach ($file_mapping as $field_name => $path_to_value) {
      $fields_values[$field_name] = $data[$path_to_value] ?? NULL;
    }

    $media = $this->mediaStorage->create($fields_values);
    $media->save();

    return $media;
  }

  /**
   * Creates file entity from API response.
   *
   * @param array $data
   *   Response data with one entity.
   *
   * @return string
   *   ID of File entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @codeCoverageIgnore
   */
  protected function createFileEntity(array $data): string {
    $file_mapping = $this->mapping->get('file');
    $fields_values = [];

    foreach ($file_mapping as $field_name => $path_to_value) {
      $path_to_value = explode('.', $path_to_value);
      $value = $data[array_shift($path_to_value)] ?? NULL;
      while ($path_to_value) {
        $value = $value[array_shift($path_to_value)] ?? NULL;
      }
      $fields_values[$field_name] = $value;
    }

    // Replace file scheme with a 001default URI scheme for creating the file
    // if 001orig scheme URI value is longer than 255 symbols.
    $remote_media_file_uri_scheme = explode('.', $file_mapping['uri']);
    if (!empty($remote_media_file_uri_scheme[1]) && strlen($data['urls'][$remote_media_file_uri_scheme[1]]) >= 255) {
      $this->messenger()->addWarning('We are trying to get the default LightHouse media component URL because the original one is longer than 255 symbols. Filename: @filename', ['@filename' => $fields_values['filename']]);
      if (!empty($data['urls']['001default'])) {
        $fields_values['uri'] = $data['urls']['001default'];
      }
      else {
        $this->messenger()->addWarning('Alternative video URL not found. Please contact administrators to check the LightHouse response. Filename: @filename', ['@filename' => $fields_values['filename']]);
        return '';
      }
    }

    $file = $this->fileStorage->create($fields_values);
    $file->save();
    return $file->id();
  }

  /**
   * Prepare image extension.
   *
   * @param array $data
   *   Response data with one entity.
   */
  public function prepareImageExtension(array &$data) {
    if (isset($data['assetName'])) {
      $data['assetName'] = $this->changeExtension($data['assetName']);
    }
    if (isset($data['urls']) && isset($data['urls']['001orig'])) {
      $data['urls']['001orig'] = $this->changeExtension($data['urls']['001orig']);
    }
  }

  /**
   * Change image extension.
   *
   * @param string $data
   *   Image name or url.
   *
   * @return string|null
   *   Image url or null.
   *
   * @codeCoverageIgnore
   */
  protected function changeExtension(string $data) {
    if (empty($data)) {
      return NULL;
    }
    if (preg_match('/' . self::DEFAULT_IMAGE_EXTENSION . '$/', $data)) {
      return $data;
    }
    $data .= self::DEFAULT_IMAGE_EXTENSION;
    return $data;
  }

}
