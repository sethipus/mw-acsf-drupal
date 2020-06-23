<?php

namespace Drupal\mars_lighthouse\Controller;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\mars_lighthouse\LighthouseException;
use Drupal\mars_lighthouse\LighthouseInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\mars_lighthouse\LighthouseClientInterface;
use Drupal\media\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
      $container->get('lighthouse.client')
    );
  }

  /**
   * LighthouseAdapter constructor.
   *
   * @param \Drupal\mars_lighthouse\LighthouseClientInterface $lighthouse_client
   *   Lighthouse API client.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(LighthouseClientInterface $lighthouse_client) {
    $this->lighthouseClient = $lighthouse_client;
    $this->mediaStorage = $this->entityTypeManager()->getStorage('media');
    $this->fileStorage = $this->entityTypeManager()->getStorage('file');
    $this->mapping = $this->config(self::CONFIG_NAME);
  }

  /**
   * {@inheritdoc}
   */
  public function getToken(bool $generate_new = FALSE): array {
    $keys = [
      'mars_lighthouse.access_token',
      'mars_lighthouse.headers',
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
      unset($tokens['response']);
      $this->state()->setMultiple($tokens);
    }

    return $tokens;
  }

  /**
   * {@inheritdoc}
   */
  public function getMediaDataList($text = '', $filters = [], $sort_by = [], $offset = 0, $limit = 10): array {
    $params = $this->getToken();
    try {
      $response = $this->lighthouseClient->search($text, $filters, $sort_by, $offset, $limit, $params);
    }
    catch (LighthouseException $e) {
      // Try to refresh token.
      $params = $this->getToken(TRUE);
      $response = $this->lighthouseClient->search($text, $filters, $sort_by, $offset, $limit, $params);
    }
    return $this->prepareMediaDataList($response);
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
        'uri' => $item['urls']['001tnmd2'] ?? NULL,
        'name' => $item['assetName'] ?? '',
        'assetId' => $item['assetId'] ?? '',
      ];
    }
    return $data_list;
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
    catch (LighthouseException $e) {
      // Try to refresh token.
      $params = $this->getToken(TRUE);
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
