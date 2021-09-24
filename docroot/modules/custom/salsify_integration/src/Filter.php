<?php

namespace Drupal\salsify_integration;

/**
 * Static filter class.
 */
class Filter {

  protected const FIELD_VARIETY = 'field_variety';

  /**
   * Check if row is product multipack.
   */
  public static function isProductMultipack($item) {
    return isset($item[static::FIELD_VARIETY]) &&
      !empty($item[static::FIELD_VARIETY]);
  }

  /**
   * Check if row is product.
   */
  public static function isProduct($item) {
    return !isset($item[static::FIELD_VARIETY])
      || empty($item[static::FIELD_VARIETY]);
  }

}
