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
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
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
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  public function getMediaEntity(int $id): ?MediaInterface;

  /**
   * Get access tokens.
   *
   * @param bool $generate_new
   *   Force to create new tokens.
   *
   * @return array
   *   Access tokens.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  public function getToken(bool $generate_new = FALSE): array;

}
