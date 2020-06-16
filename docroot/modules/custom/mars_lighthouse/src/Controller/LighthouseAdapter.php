<?php

namespace Drupal\mars_lighthouse\Controller;

use Drupal\mars_lighthouse\LighthouseInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\mars_lighthouse\LighthouseClientInterface;
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
  const MEDIA_BUNDLE = 'otmm_image';

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
  }

  /**
   * Returns fields mapping for media entity.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   Fields mapping.
   */
  protected function getFieldsMapping() {
    return \Drupal::config('mars_lighthouse.mapping');
  }

  /**
   * {@inheritdoc}
   */
  public function getMediaDataList($text = '', $filters = [], $sort_by = [], $offset = 0, $limit = 10): array {
    $params = $this->lighthouseClient->getToken();
    $params['access_token'] = $params['response']['lhisToken'];
    unset($params['response']);
    $response = $this->lighthouseClient->search($text, $filters, $sort_by, $offset, $limit, $params);
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
  public function getMediaEntity($id) {
    if ($media = $this->mediaStorage->loadByProperties(['field_external_id' => $id])) {
      return array_shift($media);
    }
    $params = $this->lighthouseClient->getToken();
    $params['access_token'] = $params['response']['lhisToken'];
    unset($params['response']);
    $data = $this->lighthouseClient->getAssetById($id, $params);
    if (!$data) {
      return NULL;
    }
    return $this->createMediaEntity($data);
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
  protected function createMediaEntity(array $data) {
    $mapping = $this->getFieldsMapping();
    $file_mapping = $mapping->get('media');
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
   * @return int
   *   ID of File entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createFileEntity(array $data) {
    $mapping = $this->getFieldsMapping();
    $file_mapping = $mapping->get('file');
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
