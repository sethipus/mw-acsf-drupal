<?php

namespace Drupal\mars_common\Controller;

use Drupal\Core\Config\Config;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Exposes the theme settings as an API.
 *
 * @package Drupal\mars_common\Controller
 */
class ThemeSettingsApiController extends ControllerBase {

  /**
   * The stream wrapper manager.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   */
  protected $streamWrapperManager;

  /**
   * The file storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * The 'emulsifymars.settings' config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs a new FileSystem.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $file_storage
   *   The file storage.
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $stream_wrapper_manager
   *   The stream wrapper manager.
   * @param \Drupal\Core\Config\Config $config
   *   The 'aggregator.settings' config.
   */
  public function __construct(EntityStorageInterface $file_storage, StreamWrapperManagerInterface $stream_wrapper_manager, Config $config) {
    $this->streamWrapperManager = $stream_wrapper_manager;
    $this->fileStorage = $file_storage;
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_type_manager = $container->get('entity_type.manager');
    return new static(
      $entity_type_manager->getStorage('file'),
      $container->get('stream_wrapper_manager'),
      $container->get('config.factory')->get('emulsifymars.settings')
    );
  }

  /**
   * Returns the theme settings as a JSON object.
   */
  public function main() {
    $data = $this->config->getRawData();

    if (!empty($data['logo']['path'])) {
      $data['logo']['path'] = $this->streamWrapperManager->getViaUri($data['logo']['path'])->getExternalUrl();
    }
    else {
      $data['logo']['path'] = NULL;
    }

    if (!empty($data['favicon']['path'])) {
      $data['favicon']['path'] = $this->streamWrapperManager->getViaUri($data['logo']['path'])->getExternalUrl();
    }
    else {
      $data['favicon']['path'] = NULL;
    }

    $file_fields = [
      'graphic_divider',
      'brand_shape',
      'brand_borders',
      'brand_borders_2',
      'png_asset',
    ];

    foreach ($file_fields as $field_name) {
      if (!empty($data[$field_name][0])) {
        $data[$field_name] = $this->loadExternalUrl($data[$field_name][0]);
      }
      else {
        $data[$field_name] = NULL;
      }
    }

    foreach ($data['social'] as $key => $item) {
      if (!empty($data['social'][$key]['icon'][0])) {
        $data['social'][$key]['icon'] = $this->loadExternalUrl($data['social'][$key]['icon'][0]);
      }
      else {
        $data['social'][$key]['icon'] = NULL;
      }
    }

    return new JsonResponse($data);
  }

  /**
   * Load the external URL for a file, given an fid.
   *
   * @param int $fid
   *   The file id.
   *
   * @return string|null
   *   The external URL for a file, or NULL.
   */
  protected function loadExternalUrl($fid) {
    $file = $this->fileStorage->load($fid);
    if ($file && $uri = $file->getFileUri()) {
      return $this->streamWrapperManager->getViaUri($uri)->getExternalUrl();
    }
    else {
      return NULL;
    }
  }

}
