<?php

namespace Drupal\juicer_io\Model;

/**
 * Class that represents a juicer.io feed.
 */
class Feed {

  const TYPE_INSTAGRAM = 'Instagram';

  /**
   * Feed reader object.
   *
   * @var \Drupal\juicer_io\Model\FeedReaderInterface
   */
  private $feedReader;

  /**
   * Feed constructor.
   *
   * @param \Drupal\juicer_io\Model\FeedReaderInterface $feed_reader
   *   The feed reader object.
   */
  public function __construct(FeedReaderInterface $feed_reader) {
    $this->feedReader = $feed_reader;
  }

  /**
   * Get the latest feed items.
   *
   * @param int|null $max
   *   The number of max items. Default is NULL which is unlimited.
   * @param array $types
   *   The item types that should be in the list. Default is everything.
   *
   * @return \Drupal\juicer_io\Model\FeedItem[]
   *   The list of feed items.
   *
   * @throws \Drupal\juicer_io\Model\FeedException
   */
  public function getLatestItems(int $max = NULL, array $types = []): array {
    $feedData = $this->feedReader->read($types, $max);
    $itemsData = $feedData['posts']['items'] ?? [];
    $items = [];

    foreach ($itemsData as $item) {
      $items[] = FeedItem::createFromFeedArray($item);
    }

    return $items;
  }

}
