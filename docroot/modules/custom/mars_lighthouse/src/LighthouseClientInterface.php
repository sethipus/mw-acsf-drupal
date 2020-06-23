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
   * @return mixed
   *   Array with access tokens and headers.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  public function getToken();

  /**
   * Search request.
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
   * @param array $params
   *   Headers and access token.
   *
   * @return array
   *   Response with media data items.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  public function search(string $text = '', array $filters = [], array $sort_by = [], int $offset = 0, int $limit = 10, array $params = []);

  /**
   * Returns configuration for Lighthouse client.
   *
   * @return array
   *   Configuration for Lighthouse client.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  public function getConfiguration();

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
   */
  public function getAssetById(string $id, array $params = []): array;

}
