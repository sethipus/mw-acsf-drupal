<?php

namespace Drupal\mars_lighthouse\Controller;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\mars_lighthouse\LighthouseAccessException;
use Drupal\mars_lighthouse\LighthouseInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\mars_lighthouse\LighthouseClientInterface;
use Drupal\mars_lighthouse\TokenIsExpiredException;
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
   * Media bundle name for Lighthouse entities.
   */
  const MEDIA_BUNDLE = 'lighthouse_image';

  /**
   * Fields mapping name.
   */
  const CONFIG_NAME = 'mars_lighthouse.mapping';

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
      $container->get('cache.default')
    );
  }

  /**
   * LighthouseAdapter constructor.
   *
   * @param \Drupal\mars_lighthouse\LighthouseClientInterface $lighthouse_client
   *   Lighthouse API client.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache container.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(LighthouseClientInterface $lighthouse_client, CacheBackendInterface $cache) {
    $this->lighthouseClient = $lighthouse_client;
    $this->mediaStorage = $this->entityTypeManager()->getStorage('media');
    $this->fileStorage = $this->entityTypeManager()->getStorage('file');
    $this->mapping = $this->config(self::CONFIG_NAME);
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public function getToken(bool $generate_new = FALSE): array {
    $keys = [
      'mars_lighthouse.access_token',
      'mars_lighthouse.headers',
      'mars_lighthouse.refresh_token',
    ];
    $tokens = $this->state()->getMultiple($keys);

    if (!$generate_new && !$tokens) {
      $generate_new = TRUE;
    }

    // Check that tokens were saved.
    foreach ($tokens as $value) {
      if (!$value) {
        $generate_new = TRUE;
        break;
      }
    }

    // Get and save new tokens.
    if ($generate_new) {
      $tokens = $this->lighthouseClient->getToken();
      $tokens['mars_lighthouse.access_token'] = $tokens['response']['lhisToken'];
      $tokens['mars_lighthouse.refresh_token'] = $tokens['response']['refreshToken'];
      unset($tokens['response']);
      $this->state()->setMultiple($tokens);
    }

    return $tokens;
  }

  /**
   * {@inheritdoc}
   */
  public function refreshToken(): array {
    $tokens = $this->lighthouseClient->refreshToken($this->getToken());

    // Save refreshed tokens.
    $tokens['mars_lighthouse.access_token'] = $tokens['response']['lhisToken'];
    $tokens['mars_lighthouse.refresh_token'] = $tokens['response']['refreshToken'];
    unset($tokens['response']);
    $this->state()->setMultiple($tokens);

    return $tokens;
  }

  /**
   * {@inheritdoc}
   */
  public function getMediaDataList(&$total_found, $text = '', $filters = [], $sort_by = [], $offset = 0, $limit = 12): array {
    $params = $this->getToken();
    try {
      $response = $this->lighthouseClient->search($total_found, $text, $filters, $sort_by, $offset, $limit, $params);
    }
    catch (TokenIsExpiredException $e) {
      // Try to refresh token.
      $params = $this->refreshToken();
      $response = $this->lighthouseClient->search($total_found, $text, $filters, $sort_by, $offset, $limit, $params);
    }
    catch (LighthouseAccessException $e) {
      // Try to force request new token.
      $params = $this->getToken(TRUE);
      $response = $this->lighthouseClient->search($total_found, $text, $filters, $sort_by, $offset, $limit, $params);
    }
    return $this->prepareMediaDataList($response);
  }

  /**
   * {@inheritdoc}
   */
  public function getMediaEntity($id): ?MediaInterface {
    if ($media = $this->mediaStorage->loadByProperties(['field_external_id' => $id])) {
      return array_shift($media);
    }
    $params = $this->getToken();
    try {
      $data = $this->lighthouseClient->getAssetById($id, $params);
    }
    catch (TokenIsExpiredException $e) {
      // Try to refresh token.
      $params = $this->refreshToken();
      $data = $this->lighthouseClient->getAssetById($id, $params);
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

    $params = $this->getToken();
    try {
      $data = $this->lighthouseClient->getBrands($params);
    }
    catch (TokenIsExpiredException $e) {
      // Try to refresh token.
      $params = $this->refreshToken();
      $data = $this->lighthouseClient->getBrands($params);
    }
    catch (LighthouseAccessException $e) {
      // Try to force request new token.
      $params = $this->getToken(TRUE);
      $data = $this->lighthouseClient->getBrands($params);
    }

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

    $params = $this->getToken();
    try {
      $data = $this->lighthouseClient->getMarkets($params);
    }
    catch (TokenIsExpiredException $e) {
      // Try to refresh token.
      $params = $this->refreshToken();
      $data = $this->lighthouseClient->getMarkets($params);
    }
    catch (LighthouseAccessException $e) {
      // Try to force request new token.
      $params = $this->getToken(TRUE);
      $data = $this->lighthouseClient->getMarkets($params);
    }

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
   * @return \Drupal\Core\Entity\EntityInterface
   *   Media entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createMediaEntity(array $data): ?MediaInterface {
    if (!$data) {
      return NULL;
    }

    $file_mapping = $this->mapping->get('media');
    $file_id = $this->createFileEntity($data);
    $fields_values = [
      'bundle' => $this::MEDIA_BUNDLE,
      'field_media_image' => ['target_id' => $file_id],
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

    $file = $this->fileStorage->create($fields_values);
    $file->save();
    return $file->id();
  }

}
