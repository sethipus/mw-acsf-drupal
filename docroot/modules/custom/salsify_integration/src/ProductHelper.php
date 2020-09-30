<?php

namespace Drupal\salsify_integration;

/**
 * Class ProductHelper.
 *
 * @package Drupal\salsify_integration
 */
class ProductHelper {

  public const PRODUCT_CONTENT_TYPE = 'product';

  public const PRODUCT_MULTIPACK_CONTENT_TYPE = 'product_multipack';

  public const PRODUCT_VARIANT_CONTENT_TYPE = 'product_variant';

  public const SALSIFY_DATA_FORMAT_STRING = 'string';

  /**
   * Whether product variant or not.
   *
   * @param array $product
   *   Product array.
   *
   * @return bool
   *   Result.
   */
  public static function isProductVariant(array $product) {
    $is_product_variant = FALSE;
    if (isset($product['Case Net Weight']) &&
      isset($product['CMS: Variety']) &&
      strtolower($product['CMS: Variety']) == 'no') {

      $is_product_variant = TRUE;
    }

    return $is_product_variant;
  }

  /**
   * Whether product multipack or not.
   *
   * @param array $product
   *   Product array.
   *
   * @return bool
   *   Result.
   */
  public static function isProductMultipack(array $product) {
    return (isset($product['CMS: Variety']) && strtolower($product['CMS: Variety']) == 'yes') ? TRUE : FALSE;
  }

  /**
   * Whether product or not.
   *
   * @param array $product
   *   Product array.
   *
   * @return bool
   *   Result.
   */
  public static function isProduct(array $product) {
    $is_product = FALSE;
    if (
      !isset($product['Case Net Weight']) &&
      (!isset($product['CMS: Variety']) || strtolower($product['CMS: Variety']) != 'yes')
    ) {
      $is_product = TRUE;
    }

    return $is_product;
  }

  /**
   * Whether product or not.
   *
   * @param array $product
   *   Product array.
   *
   * @return bool
   *   Result.
   */
  public static function getProductType(array $product) {
    $product_type = 'undefined';

    if (static::isProduct($product)) {
      $product_type = self::PRODUCT_CONTENT_TYPE;
    }
    elseif (static::isProductMultipack($product)) {
      $product_type = self::PRODUCT_MULTIPACK_CONTENT_TYPE;
    }
    elseif (static::isProductVariant($product)) {
      $product_type = self::PRODUCT_VARIANT_CONTENT_TYPE;
    }

    return $product_type;
  }

  /**
   * Validate data by type in mapping.
   *
   * @param array $record
   *   Product record.
   * @param array $field_mapping
   *   Field map.
   *
   * @return bool
   *   Valid data or not.
   */
  public function validateDataRecord(array $record, array $field_mapping) {
    $result = FALSE;

    // Validate only string fields. If record doesn't have field value it
    // returns true.
    if (
      ($field_mapping['salsify_data_type'] == self::SALSIFY_DATA_FORMAT_STRING &&
      isset($record[$field_mapping['salsify_id']]) &&
      is_string($record[$field_mapping['salsify_id']])) ||
      $field_mapping['salsify_data_type'] != self::SALSIFY_DATA_FORMAT_STRING ||
      !isset($record[$field_mapping['salsify_id']])
    ) {
      $result = TRUE;
    }
    return $result;
  }

}
