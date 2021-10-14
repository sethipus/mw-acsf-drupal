<?php

namespace Drupal\salsify_integration;

/**
 * Static filter class.
 */
class Filter {

  protected const FIELD_VARIETY = 'field_variety';

  protected const FIELD_VARIETY_USED = 'field_variety_used';

  /**
   * Check if row is product multipack.
   */
  public static function isProductMultipack($item) {
    return isset($item[static::FIELD_VARIETY]) &&
      !empty($item[static::FIELD_VARIETY]);
  }

  /**
   * Check if row is product multipack.
   */
  public static function isProductMultipackSuffixed($item) {
    return (isset($item[static::FIELD_VARIETY_USED]) &&
      !empty($item[static::FIELD_VARIETY_USED]) &&
      (strtolower(reset($item[static::FIELD_VARIETY_USED])) == 'yes'));
  }

  /**
   * Check if row is product.
   */
  public static function isProduct($item) {
    return !static::isProductMultipack($item) &&
      !static::isProductMultipackSuffixed($item);
  }

}
