<?php

namespace Drupal\mars_lighthouse;

/**
 * Interface LighthouseClientInterface.
 *
 * @package Drupal\mars_lighthouse
 */
interface LighthouseClientInterface {

  /**
   * Search request.
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
   *   Response with media data items.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   * @throws \Drupal\mars_lighthouse\TokenIsExpiredException
   * @throws \Drupal\mars_lighthouse\LighthouseAccessException
   */
  public function search(
    int &$total_found,
    string $text = '',
    array $filters = [],
    array $sort_by = [],
    int $offset = 0,
    int $limit = 10,
    string $media_type = 'image'
  ): array;

  /**
   * Get an asset data by its Id.
   *
   * @param string $id
   *   Asset Id.
   *
   * @return array
   *   An asset data.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   * @throws \Drupal\mars_lighthouse\TokenIsExpiredException
   * @throws \Drupal\mars_lighthouse\LighthouseAccessException
   */
  public function getAssetById(string $id): array;

  /**
   * Get an assets data by its Ids.
   *
   * @param array $ids
   *   Asset Id.
   * @param string $date
   *   The latest modified date.
   *
   * @return array
   *   An asset data.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   * @throws \Drupal\mars_lighthouse\TokenIsExpiredException
   * @throws \Drupal\mars_lighthouse\LighthouseAccessException
   */
  public function getAssetsByIds(array $ids, string $date): array;

  /**
   * Sent inventory report.
   *
   * @param array $asset_list
   *   List of asset information.
   *
   * @return array
   *   Response data.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   * @throws \Drupal\mars_lighthouse\TokenIsExpiredException
   * @throws \Drupal\mars_lighthouse\LighthouseAccessException
   */
  public function sentInventoryReport(array $asset_list): array;

  /**
   * Brands list.
   *
   * @return array
   *   List of brands.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   * @throws \Drupal\mars_lighthouse\TokenIsExpiredException
   * @throws \Drupal\mars_lighthouse\LighthouseAccessException
   */
  public function getBrands(): array;

  /**
   * Markets list.
   *
   * @return array
   *   List of markets.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   * @throws \Drupal\mars_lighthouse\TokenIsExpiredException
   * @throws \Drupal\mars_lighthouse\LighthouseAccessException
   */
  public function getMarkets(): array;

}
