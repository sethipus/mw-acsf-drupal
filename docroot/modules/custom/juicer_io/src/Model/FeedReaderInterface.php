<?php

namespace Drupal\juicer_io\Model;

/**
 * Interface for feed reader implementations.
 */
interface FeedReaderInterface {

  /**
   * Reads the feed data.
   *
   * @param array $types
   *   Array of allowed types. No restriction if empty.
   * @param int|null $max
   *   Number of max items. If null then unlimited.
   *
   * @return array
   *   Array of feed data.
   *
   * @throws \Drupal\juicer_io\Model\FeedException
   */
  public function read(array $types, ?int $max): array;

}
