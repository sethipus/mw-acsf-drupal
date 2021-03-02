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
   * Inventory queue id.
   */
  const INVENTORY_QUEUE_ID = 'lighthouse_inventory_report_queue';

  /**
   * Sync queue id.
   */
  const SYNC_QUEUE_ID = 'lighthouse_sync_queue';

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
    $media_chunks = $this->getAllMediaChunks();
    /** @var \Drupal\entityqueue\EntityQueueInterface $queue */
    $queue = $this->queueFactory->get(self::SYNC_QUEUE_ID);
    foreach ($media_chunks as $media_chunk) {
      $queue->createItem($media_chunk);
    }
  }

  /**
   * Run lighthouse inventory report queue.
   */
  public function runLighthouseInventoryReport() {
    $media_chunks = $this->getAllMediaChunks();
    /** @var \Drupal\entityqueue\EntityQueueInterface $queue */
    $queue = $this->queueFactory->get(self::INVENTORY_QUEUE_ID);
    foreach ($media_chunks as $media_chunk) {
      $queue->createItem($media_chunk);
    }
  }

  /**
   * Get all media "video" and "images" split by chunks.
   */
  private function getAllMediaChunks() {
    $media_images = $this->mediaStorage->loadByProperties([
      'bundle' => self::LIGHTHOUSE_IMAGE_BUNDLE,
    ]);
    $media_videos = $this->mediaStorage->loadByProperties([
      'bundle' => self::LIGHTHOUSE_VIDEO_BUNDLE,
    ]);
    $all_media = array_merge($media_images, $media_videos);
    $media_chunks = array_chunk($all_media, self::SIZE_CHUNK);
    return $media_chunks;
  }

}
