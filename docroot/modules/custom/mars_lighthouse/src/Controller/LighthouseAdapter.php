<?php

namespace Drupal\mars_lighthouse\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileInterface;
use Drupal\mars_lighthouse\Client\LighthouseDefaultsProvider;
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
   * Preview tenant for api version 1.
   */
  const PREVIEW_TENANT_API_VERSION_1 = '001tnmd';

  /**
   * Preview tenant for api version 2.
   */
  const PREVIEW_TENANT_API_VERSION_2 = 'cust_cf_originating_preview_jpg';

  /**
   * Source tenant transparent for api version 2.
   */
  const SOURCE_TENANT_TRANSPARENT_API_VERSION_2 = 'cust_cf_originating_src_png';

  /**
   * Source tenant png for api version 2.
   */
  const SOURCE_TENANT_HIGH_DIMENSION_API_VERSION_2 = 'cust_cf_originating_src_jpg';

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('lighthouse.client'),
      $container->get('cache.default'),
      $container->get('file_system'),
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
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
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
    FileSystemInterface $file_system,
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
    $this->fileSystem = $file_system;
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
    $data = $this->lighthouseClient->getAssetById($id);

    if ($media = $this->mediaStorage->loadByProperties(['field_external_id' => $id])) {
      $media = array_shift($media);
      $this->updateMediaData($media, $data);
      return $media;
    }

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
   */
  protected function prepareMediaDataList(array $data) {
    $config = $this->config('mars_lighthouse.settings');
    $api_version = $config->get('api_version') ?? LighthouseDefaultsProvider::API_KEY_VERSION_1;
    $preview = '';
    if ($api_version == LighthouseDefaultsProvider::API_KEY_VERSION_1) {
      $preview = static::PREVIEW_TENANT_API_VERSION_1;
    }
    elseif ($api_version == LighthouseDefaultsProvider::API_KEY_VERSION_2) {
      $preview = static::PREVIEW_TENANT_API_VERSION_2;
    }

    $data_list = [];
    foreach ($data as $item) {
      $data_list[] = [
        'uri' => $item['urls'][$preview] ?? NULL,
        'name' => $item['assetName'] ?? '',
        'assetId' => $item['assetId'] ?? '',
        'dimensions' => $item['dimensions'] ?? '',
        'transparent' => $item['isTransparent'] ?? '',
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
    if (strpos($data['dimensions'], ' x ')) {
      list($width, $height) = explode(' x ', $data['dimensions']);
      $media->set('field_dimension', [
        'width' => $width,
        'height' => $height,
      ]);
    }
    if ($this->mediaType === 'image') {
      $media->set('field_is_transparent', strtolower($data['isTransparent'] ?? NULL));
    }
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
   */
  protected function createFileEntity(array $data): string {
    $file_mapping = $this->getFileMapping($data);

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

    $config = $this->config('mars_lighthouse.settings');
    $api_version = $config->get('api_version') ?? LighthouseDefaultsProvider::API_KEY_VERSION_1;
    if ($api_version == LighthouseDefaultsProvider::API_KEY_VERSION_1 && !empty($remote_media_file_uri_scheme[1]) && strlen($data['urls'][$remote_media_file_uri_scheme[1]]) >= 255) {
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
    $config = $this->config('mars_lighthouse.settings');
    $api_version = $config->get('api_version') ?? LighthouseDefaultsProvider::API_KEY_VERSION_1;
    if (isset($data['assetName'])) {
      $data['assetName'] = $this->changeExtension($data['assetName']);
    }

    if ($api_version == LighthouseDefaultsProvider::API_KEY_VERSION_1) {
      if (isset($data['urls']) && isset($data['urls']['001orig'])) {
        $data['urls']['001orig'] = $this->changeExtension($data['urls']['001orig']);
      }
    }
    elseif ($api_version == LighthouseDefaultsProvider::API_KEY_VERSION_2) {
      $asset_type_class = static::getAssetTypeClass($data);
      $tenant_name = static::getTenantNameByAssetType($asset_type_class);
      if (isset($data['urls']) && isset($data['urls'][$tenant_name])) {
        $data['urls'][$tenant_name] = $this->changeExtension($data['urls'][$tenant_name]);
      }
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
   */
  protected function changeExtension(string $data) {
    if (empty($data)) {
      return NULL;
    }
    if (preg_match('/(.png|.jpg|.jpeg)$/', $data)) {
      return $data;
    }
    $data .= self::DEFAULT_IMAGE_EXTENSION;
    return $data;
  }

  /**
   * Return file field mapping.
   *
   * @return array
   *   Return file field mapping.
   */
  protected function getFileMapping(array $data): array {
    $file_mapping = [];
    $config = $this->config('mars_lighthouse.settings');
    $api_version = $config->get('api_version') ?? LighthouseDefaultsProvider::API_KEY_VERSION_1;
    if ($api_version == LighthouseDefaultsProvider::API_KEY_VERSION_1) {
      $file_mapping = $this->mapping->get('file');
    }
    elseif ($api_version == LighthouseDefaultsProvider::API_KEY_VERSION_2) {
      $asset_type_class = static::getAssetTypeClass($data);
      $tenant_name = static::getTenantNameByAssetType($asset_type_class);
      $uri = 'urls.' . $tenant_name;
      $file_mapping = [
        'filename' => 'assetName',
        'uri' => $uri,
        'filemime' => 'fileType',
      ];
    }
    return $file_mapping;
  }

  /**
   * Get asset type class.
   *
   * @param array $item
   *   Asset array data.
   *
   * @return string|null
   *   Return asset type.
   */
  public static function getAssetTypeClass(array $item): ?string {
    $class_asset_type = NULL;
    if ((isset($item['transparent']) && strtolower($item['transparent']) == 'yes') ||
      (isset($item['isTransparent']) && strtolower($item['isTransparent']) == 'yes')) {
      $class_asset_type = 'transparent';
    }
    else {
      if (strpos($item['dimensions'], ' x ')) {
        list($width, $height) = explode(' x ', $item['dimensions']);
        if ($width > 1600 && $height > 1200) {
          $class_asset_type = 'high-dimension';
        }
      }
    }
    return $class_asset_type;
  }

  /**
   * Get tenant name by asset type.
   *
   * @param string $asset_type_class
   *   Asset type.
   *
   * @return string
   *   Return tenant name.
   */
  public static function getTenantNameByAssetType(string $asset_type_class = NULL) {
    if ($asset_type_class == 'transparent') {
      $tenant_name = static::SOURCE_TENANT_TRANSPARENT_API_VERSION_2;
    }
    elseif ($asset_type_class == 'high-dimension') {
      $tenant_name = static::SOURCE_TENANT_HIGH_DIMENSION_API_VERSION_2;
    }
    else {
      $tenant_name = static::PREVIEW_TENANT_API_VERSION_2;
    }
    return $tenant_name;
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
      $this->prepareImageExtension($data);
    }

    $file_mapping = $this->mapping->get('media');
    $field_config = $this->mediaConfig[$media->bundle()];
    $field_file = $field_config['field'];
    $fid = $media->$field_file->target_id;
    $this->updateFileEntity($fid, $data);

    foreach ($file_mapping as $field_name => $path_to_value) {
      $media->set($field_name, $data[$path_to_value]);
    }

    if (strpos($data['dimensions'], ' x ')) {
      list($width, $height) = explode(' x ', $data['dimensions']);
      $media->set('field_dimension', [
        'width' => $width,
        'height' => $height,
      ]);
    }
    $media->set('field_is_transparent', strtolower($data['isTransparent'] ?? NULL));

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
    $file_mapping = $this->getFileMapping($data);

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
