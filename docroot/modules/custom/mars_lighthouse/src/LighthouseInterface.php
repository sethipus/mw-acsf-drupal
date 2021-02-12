<?php

namespace Drupal\mars_lighthouse;

use Drupal\media\MediaInterface;

/**
 * Interface LighthouseInterface.
 *
 * @package Drupal\mars_lighthouse
 */
interface LighthouseInterface {

  /**
   * Process search request.
   *
   * @param int $total_found
   *   Returns the amount of results.
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
   * @param string $media_type
   *   Media type to get.
   *
   * @return array
   *   Media data array ready for a rendering.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  public function getMediaDataList(
    int &$total_found,
    string $text = '',
    array $filters = [],
    array $sort_by = [],
    int $offset = 0,
    int $limit = 10,
    string $media_type = 'image'
  ): array;

  /**
   * Returns lighthouse media entity, creates if it's needed.
   *
   * @param int $id
   *   External assert Id.
   *
   * @return \Drupal\media\MediaInterface
   *   Media entity.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  public function getMediaEntity(int $id): ?MediaInterface;

  /**
   * Get list of brand options.
   *
   * @return array
   *   List of brands.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  public function getBrands(): array;

  /**
   * Get list of market options.
   *
   * @return array
   *   List of markets.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  public function getMarkets(): array;

}
