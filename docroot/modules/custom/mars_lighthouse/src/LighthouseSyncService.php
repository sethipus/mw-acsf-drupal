<?php

namespace Drupal\mars_lighthouse;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Queue\QueueFactory;

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
   * The queue service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

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
    QueueFactory $queue_factory,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->queueFactory = $queue_factory;
    $this->mediaStorage = $entity_type_manager->getStorage('media');
  }

  /**
   * Run lighthouse sync queue.
   */
  public function runLighthouseSyncQueue() {
    $media_images = $this->mediaStorage->loadByProperties([
      'bundle' => self::LIGHTHOUSE_IMAGE_BUNDLE,
    ]);
    $media_videos = $this->mediaStorage->loadByProperties([
      'bundle' => self::LIGHTHOUSE_VIDEO_BUNDLE,
    ]);
    $all_media = array_merge($media_images, $media_videos);

    /** @var \Drupal\entityqueue\EntityQueueInterface $queue */
    $queue = $this->queueFactory->get('lighthouse_sync_queue');
    $queue->createItem($all_media);
  }

}
