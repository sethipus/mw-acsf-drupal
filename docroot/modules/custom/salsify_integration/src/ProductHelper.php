<?php

namespace Drupal\salsify_integration;

use Drupal\Component\Serialization\Json;

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

  /**
   * Yield product items.
   *
   * @param string $products
   *   Products string.
   *
   * @return \Generator
   *   Product item.
   */
  public function getProductsData(string $products) {
    $products = Json::decode($products);
    $count = count($products['data']);
    for ($i = 0; $i < $count; $i++) {
      yield $products['data'][$i];
    }
  }

  /**
   * Get mapping for entities (product variants to products to multipack).
   *
   * @param string $response
   *   Products data.
   *
   * @return array
   *   Response data.
   */
  public function getParentEntitiesMapping($response) {
    $mapping = [];

    $product_gtins = [];
    foreach ($this->getProductsData($response) as $product) {
      $product_gtins[$product['GTIN']] = $product['GTIN'];
    }

    foreach ($this->getProductsData($response) as $product) {
      if (isset($product['Parent GTIN'])) {
        $parent_gtin = is_array($product['Parent GTIN']) ? $product['Parent GTIN'] : [$product['Parent GTIN']];
        foreach ($parent_gtin as $gtin) {
          if (isset($product_gtins[$gtin])) {
            $mapping[$gtin][(string) $product['GTIN']] = ProductHelper::getProductType($product);
          }
        }
      }
    }

    return $mapping;
  }

  /**
   * Filter products in response by 'Send to Brand site' field.
   *
   * @param string $response
   *   Products data.
   *
   * @return string
   *   Response data.
   */
  public function filterProductsInResponse($response) {

    $products = [];

    foreach ($this->getProductsData($response) as $product) {
      if (isset($product['Send to Brand Site?']) &&
        $product['Send to Brand Site?']) {

        $products[] = $product;
      }
    }

    $response = Json::decode($response);
    $response['data'] = $products;

    return Json::encode($response);
  }

  /**
   * Get data attributes by products.
   *
   * @param string $products
   *   Products data.
   *
   * @return array
   *   Attributes.
   */
  public function getAttributesByProducts($products) {
    $attributes = [];
    $products_generator = $this->getProductsData($products);

    $product_fields_map = array_column(SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT, 'salsify:id');
    $product_variant_fields_map = array_column(
      SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT_VARIANT, 'salsify:id'
    );

    foreach ($products_generator as $product) {
      foreach ($product as $product_attr_key => $product_attr_value) {
        if (
          strpos($product_attr_key, 'salsify:') !== 0 &&
          (in_array($product_attr_key, $product_fields_map) ||
            in_array($product_attr_key, $product_variant_fields_map))
        ) {
          $attributes[$product_attr_key] = [
            'salsify:id' => $product_attr_key,
            'salsify:updated_at' => Salsify::ATTRIBUTE_UPDATED_AT,
            'salsify:entity_types' => ['products'],
          ];
        }
      }
    }
    return array_values($attributes);
  }

  /**
   * Get values of attirbutes by products.
   *
   * @param string $products
   *   Products data.
   *
   * @return array
   *   Attributes.
   */
  public function getAttributeValuesByProducts($products) {
    $attributes_values = [];
    $products_generator = $this->getProductsData($products);

    $product_fields_map = array_column(SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT, 'salsify:id');
    $product_variant_fields_map = array_column(
      SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT_VARIANT, 'salsify:id'
    );
    $enum_fields = array_column(array_filter(
      SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT_VARIANT + SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT,
      function ($salsify_attribute, $k) {
        return (
          isset($salsify_attribute['salsify:data_type']) &&
          $salsify_attribute['salsify:data_type'] == 'enumerated'
        );
      },
      ARRAY_FILTER_USE_BOTH
    ), 'salsify:id');

    foreach ($products_generator as $product) {
      foreach ($product as $product_attr_key => $product_attr_value) {
        if (
          strpos($product_attr_key, 'salsify:') !== 0 &&
          (in_array($product_attr_key, $product_fields_map) ||
            in_array($product_attr_key, $product_variant_fields_map)) &&
          in_array($product_attr_key, $enum_fields)
        ) {
          $attributes_values[$product_attr_key . $product_attr_value] = [
            "salsify:attribute_id" => $product_attr_key,
            'salsify:id' => $product_attr_value,
            'salsify:name' => $product_attr_value,
            'salsify:updated_at' => Salsify::ATTRIBUTE_UPDATED_AT,
          ];
        }
      }
    }
    return array_values($attributes_values);
  }

  /**
   * Get digital assets by products.
   *
   * @param string $products
   *   Products data.
   *
   * @return array
   *   Attributes.
   */
  public function getDigitalAssetsByProducts($products) {
    $assets = [];
    foreach ($this->getProductsData($products) as $product) {
      if (isset($product['salsify:digital_assets'])) {
        foreach ($product['salsify:digital_assets'] as $asset) {
          $assets[$asset['salsify:id']] = $asset;
        }
      }
    }
    return array_values($assets);
  }

}
