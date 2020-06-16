<?php

namespace Drupal\mars_lighthouse;

/**
 * Interface LighthouseInterface.
 *
 * @package Drupal\mars_lighthouse
 */
interface LighthouseInterface {

  /**
   * Process search request.
   *
   * @param string $text
   *   Text filter.
   * @param array $filters
   *   Associative array to filter with field => values.
   * @param array $sort_by
   *   Associative array to sort.
   * @param int $offset
   *   Offset index.
   * @param int $limit
   *   Limit number.
   *
   * @return array
   *   Media data array ready for a rendering.
   */
  public function getMediaDataList(string $text = '', array $filters = [], array $sort_by = [], int $offset = 0, int $limit = 10): array;

  /**
   * Returns lighthouse media entity, creates if it's needed.
   *
   * @param int $id
   *   External assert Id.
   *
   * @return \Drupal\media\MediaInterface
   *   Media entity.
   */
  public function getMediaEntity(int $id);

}
