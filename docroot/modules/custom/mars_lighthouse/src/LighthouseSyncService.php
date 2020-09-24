<?php

namespace Drupal\mars_lighthouse;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\mars_lighthouse\Client\LighthouseDefaultsProvider;

/**
 * Class LighthouseSyncService.
 */
class LighthouseSyncService {

  /**
   * Lighthouse image bundle name.
   */
  const LIGHTHOUSE_IMAGE_BUNDLE = 'lighthouse_image';

  /**
   * Lighthouse video bundle name.
   */
  const LIGHTHOUSE_VIDEO_BUNDLE = 'lighthouse_video';

  /**
   * Size of chunk.
   */
  const SIZE_CHUNK = 10;

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The queue service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * A config object for the system performance configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Media storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    QueueFactory $queue_factory,
    LighthouseDefaultsProvider $defaults_provider,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->configFactory = $config_factory;
    $this->config = $config_factory->get('mars_lighthouse.settings');
    $this->queueFactory = $queue_factory;
    $this->mediaStorage = $entity_type_manager->getStorage('media');
  }

  /**
   * Run lighthouse sync queue.
   */
  public function runLighthouseSyncQueue() {
    $sync_mode = $this->config->get('sync_mode');

    $media_images = $this->mediaStorage->loadByProperties([
      'bundle' => self::LIGHTHOUSE_IMAGE_BUNDLE,
    ]);
    $media_videos = $this->mediaStorage->loadByProperties([
      'bundle' => self::LIGHTHOUSE_VIDEO_BUNDLE,
    ]);
    $all_media = array_merge($media_images, $media_videos);

    /** @var \Drupal\entityqueue\EntityQueueInterface $queue */
    $queue = $this->queueFactory->get('lighthouse_sync_queue');
    if ($sync_mode) {
      // Bulk sync.
      $media_chunks = array_chunk($all_media, self::SIZE_CHUNK);
      foreach ($media_chunks as $media_chunk) {
        $queue->createItem($media_chunk);
      }
    }
    else {
      foreach ($all_media as $media) {
        $queue->createItem($media);
      }
    }
  }

}
