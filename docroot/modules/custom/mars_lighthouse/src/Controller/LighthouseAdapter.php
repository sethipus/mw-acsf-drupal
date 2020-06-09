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
  public function getMediaDataList($text, $filters = [], $sort_by = [], $offset = 0, $limit = 10): array {
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
      $data_list[$item['assetId']] = [
        'uri' => $item['urls']['001default'],
        'name' => $item['assetName'],
      ];
    }
    return $data_list;
  }

  /**
   * {@inheritdoc}
   */
  public function getMediaEntity($id) {
    $media = $this->mediaStorage->loadByProperties(['external_id' => $id]);
    if (!$media) {
      // TODO: get media by id from API.
      return NULL;
    }
    return array_shift($media);
  }

}
