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

}
