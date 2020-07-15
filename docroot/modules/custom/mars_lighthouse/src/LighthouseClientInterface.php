<?php

namespace Drupal\mars_lighthouse;

/**
 * Interface LighthouseClientInterface.
 *
 * @package Drupal\mars_lighthouse
 */
interface LighthouseClientInterface {

  /**
   * Returns access tokens.
   *
   * @return array
   *   Array with access tokens and headers.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  public function getToken(): array;

  /**
   * Refresh access tokens.
   *
   * @param array $params
   *   Expired headers and access token.
   *
   * @return array
   *   Array with access tokens and headers.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  public function refreshToken(array $params): array;

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
   * @param array $params
   *   Headers and access token.
   *
   * @return array
   *   Response with media data items.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   * @throws \Drupal\mars_lighthouse\TokenIsExpiredException
   * @throws \Drupal\mars_lighthouse\LighthouseAccessException
   */
  public function search(int &$total_found, string $text = '', array $filters = [], array $sort_by = [], int $offset = 0, int $limit = 10, array $params = []): array;

  /**
   * Returns configuration for Lighthouse client.
   *
   * @return array
   *   Configuration for Lighthouse client.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  public function getConfiguration(): array;

  /**
   * Get an asset data by its Id.
   *
   * @param string $id
   *   Asset Id.
   * @param array $params
   *   Headers and access token.
   *
   * @return array
   *   An asset data.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   * @throws \Drupal\mars_lighthouse\TokenIsExpiredException
   * @throws \Drupal\mars_lighthouse\LighthouseAccessException
   */
  public function getAssetById(string $id, array $params = []): array;

  /**
   * Brands list.
   *
   * @param array $params
   *   Headers and access token.
   *
   * @return array
   *   List of brands.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   * @throws \Drupal\mars_lighthouse\TokenIsExpiredException
   * @throws \Drupal\mars_lighthouse\LighthouseAccessException
   */
  public function getBrands(array $params = []): array;

}
