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
   * The Mapping.
   *
   * @var array
   */
  protected $mapping;

  /**
   * Constructs a \Drupal\salsify_integration\ProductHelper object.
   */
  public function __construct() {
    $this->mapping = [
      'by_group_id' => [],
      'primary' => [],
    ];
  }

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

    if (isset($product['CMS: content type']) &&
      $product['CMS: content type'] == static::PRODUCT_VARIANT_CONTENT_TYPE) {
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
    $is_product_multipack = FALSE;

    if (isset($product['CMS: content type']) &&
      $product['CMS: content type'] == static::PRODUCT_MULTIPACK_CONTENT_TYPE) {

      $is_product_multipack = TRUE;
    }

    return $is_product_multipack;
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

    if (isset($product['CMS: content type']) &&
      $product['CMS: content type'] == static::PRODUCT_CONTENT_TYPE) {

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
      $product_type = static::PRODUCT_CONTENT_TYPE;
    }
    elseif (static::isProductMultipack($product)) {
      $product_type = static::PRODUCT_MULTIPACK_CONTENT_TYPE;
    }
    elseif (static::isProductVariant($product)) {
      $product_type = static::PRODUCT_VARIANT_CONTENT_TYPE;
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
      ($field_mapping['salsify_data_type'] == static::SALSIFY_DATA_FORMAT_STRING &&
      isset($record[$field_mapping['salsify_id']]) &&
      (is_string($record[$field_mapping['salsify_id']]) || is_array($record[$field_mapping['salsify_id']]))) ||
      $field_mapping['salsify_data_type'] != static::SALSIFY_DATA_FORMAT_STRING ||
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

        $product = $this->addFamilyId($product);
        $products[] = $product;
      }
    }

    $response = Json::decode($response);
    $response['data'] = $products;

    return Json::encode($response);
  }

  /**
   * Filter product fields.
   *
   * @param string $response
   *   Response.
   *
   * @return string
   *   Response.
   */
  public function filterProductFields($response) {

    $products = [];

    foreach ($this->getProductsData($response) as $product_variant) {
      $product = [];
      $product_variant_fields = array_column(SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT_VARIANT, 'salsify:id');
      $product_fields = array_column(SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT, 'salsify:id');
      $product_fields = array_merge($product_variant_fields, $product_fields);

      foreach ($product_fields as $product_field_name) {
        if (isset($product_variant[$product_field_name])) {
          $product[$product_field_name] = $product_variant[$product_field_name];
        }

        // Add unit for the value.
        if (isset($product_variant[$product_field_name]) &&
          isset($product_variant[$product_field_name . ' UOM'])) {

          $product[$product_field_name] = $product_variant[$product_field_name] .
            ' ' . $product_variant[$product_field_name . ' UOM'];
        }

        // Filter nutrion fields and add to the record.
        $nutrition_fields = $this->getNuntritionFiledsByName($product_field_name, $product_variant);
        if (!empty($nutrition_fields)) {
          $this->addNutritionFieldsData($nutrition_fields, $product, $product_variant);
        }
      }

      $family_master = (isset($product_variant['CMS: Product Variant Family Master']) &&
        (strtolower($product_variant['CMS: Product Variant Family Master']) == 'yes' ||
        $product_variant['CMS: Product Variant Family Master'] == TRUE))
        ? TRUE
        : FALSE;

      $product['CMS: Product Variant Family Master'] = $family_master;
      $product['salsify:id'] = $product_variant['salsify:id'];
      $product['GTIN'] = $product_variant['salsify:id'];
      $product['salsify:version'] = $product_variant['salsify:version'];
      $product['salsify:system_id'] = $product_variant['salsify:system_id'];
      $product['salsify:created_at'] = $product_variant['salsify:created_at'];
      $product['salsify:updated_at'] = $product_variant['salsify:updated_at'];
      $product['CMS: content type'] = static::PRODUCT_VARIANT_CONTENT_TYPE;
      $product['CMS: Meta Description'] = $product_variant['CMS: Meta Description'] ?? NULL;
      $product['CMS: Keywords'] = $product_variant['CMS: Keywords'] ?? NULL;
      $product['CMS: Product Variant Family ID'] = $product_variant['CMS: Product Variant Family ID'] ?? NULL;
      $product['CMS: Product Family Groups ID'] = $product_variant['CMS: Product Family Groups ID'] ?? NULL;
      $product['CMS: Variety'] = $product_variant['CMS: Variety'] ?? NULL;
      $product['salsify:digital_assets'] = $product_variant['salsify:digital_assets'] ?? NULL;

      $products[] = $product;
    }

    $response = Json::decode($response);
    $response['data'] = $products;

    return Json::encode($response);
  }

  /**
   * Get nutrion fields by basic field name.
   *
   * @param string $field_name
   *   Field name.
   * @param array $product_variant
   *   Product variant record.
   *
   * @return array
   *   Nutrion fields.
   */
  public function getNuntritionFiledsByName(string $field_name, array $product_variant) {
    $fields = [];

    foreach (array_keys($product_variant) as $variant_field_name) {
      if (preg_match('/^' . $field_name . ' [0-9]+$/', $variant_field_name)) {
        $fields[] = $variant_field_name;
      }
    }

    return $fields;
  }

  /**
   * Add nutrion related fields to the result data.
   *
   * @param array $nutrition_fields
   *   Nutrion fields.
   * @param array $product
   *   Product data.
   * @param array $product_variant
   *   Product variant record.
   */
  public function addNutritionFieldsData(array $nutrition_fields, array &$product, array $product_variant) {
    foreach ($nutrition_fields as $field_name) {
      $product[$field_name] = $product_variant[$field_name];
    }
  }

  /**
   * Check whether product has family id field or not and add.
   *
   * @param array $product
   *   Product data.
   *
   * @return array
   *   Product.
   */
  private function addFamilyId(array $product) {
    if (((isset($product['CMS: Variety']) &&
      strtolower($product['CMS: Variety']) == 'no') ||
      !isset($product['CMS: Variety'])) &&
      !isset($product['CMS: Product Variant Family ID'])) {

      $product['CMS: Product Variant Family ID'] = $product['salsify:id'];
    }

    if (!isset($product['CMS: Product Family Groups ID'])) {
      $product['CMS: Product Family Groups ID'] = $product['salsify:id'];
    }

    return $product;
  }

  /**
   * Add product entities into response based on data.
   *
   * @param string $response
   *   Products data.
   *
   * @return string
   *   Response data.
   */
  public function addProducts($response) {

    $products = [];

    foreach ($this->getProductsData($response) as $product) {
      if ((isset($product['CMS: Variety']) &&
        strtolower($product['CMS: Variety']) == 'no') ||
        !isset($product['CMS: Variety'])) {

        if (isset($product['CMS: Product Variant Family ID']) &&
          !isset($products[$product['CMS: Product Variant Family ID']])
        ) {
          $new_product = $this->createProductFromProductVariant(
            static::PRODUCT_CONTENT_TYPE,
            SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT,
            $product
          );
          $products[$product['CMS: Product Variant Family ID']] = $new_product;
          $this->mapping['primary'][$new_product['salsify:id']][$product['salsify:id']] = static::PRODUCT_VARIANT_CONTENT_TYPE;
        }
        elseif (isset($products[$product['CMS: Product Variant Family ID']])) {
          $product_id = $products[$product['CMS: Product Variant Family ID']]['salsify:id'];
          $this->mapping['primary'][$product_id][$product['salsify:id']] = static::PRODUCT_VARIANT_CONTENT_TYPE;
        }

        // Add mapping by Product Family Groups ID in format
        // product_variant_id => product_id.
        $product_id = $products[$product['CMS: Product Variant Family ID']]['salsify:id'];
        $this->mapping['by_group_id'][$product['CMS: Product Family Groups ID']][$product['salsify:id']] = $product_id;
      }
      $products[] = $product;
    }

    $response = Json::decode($response);
    $response['data'] = array_values($products);

    return Json::encode($response);
  }

  /**
   * Create product entity based on product variant data.
   *
   * @param string $content_type
   *   Content type.
   * @param array $mapping
   *   Mapping.
   * @param array $product_variant
   *   Products data.
   *
   * @return array
   *   Product data.
   */
  public function createProductFromProductVariant(
    $content_type,
    array $mapping,
    array $product_variant
  ) {

    $product = [];
    $product_fields = array_column($mapping, 'salsify:id');
    foreach ($product_fields as $product_field_name) {
      if (isset($product_variant[$product_field_name])) {
        $product[$product_field_name] = $product_variant[$product_field_name];
      }
    }

    $salsify_id = base64_encode($product_variant['salsify:id']);
    $product['salsify:id'] = $salsify_id;
    $product['GTIN'] = $salsify_id;
    $product['salsify:created_at'] = $product_variant['salsify:created_at'];
    $product['salsify:updated_at'] = $product_variant['salsify:updated_at'];
    $product['CMS: content type'] = $content_type;

    return $product;
  }

  /**
   * Create nutrition product entity based on product variant data.
   *
   * @param array $product_variant
   *   Products data.
   *
   * @return array
   *   Product data.
   */
  public function createNutritionProductsFromProductVariant(
    array $product_variant
  ) {
    $mapping = SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT_VARIANT;
    $product_fields = array_column($mapping, 'salsify:id');
    $products = [];
    foreach ($product_fields as $product_field_name) {
      $nutrion_fields = $this->getNuntritionFiledsByName(
        $product_field_name,
        $product_variant
      );

      if (!empty($nutrion_fields)) {
        $this->fillNutrionRecordsByData(
          $nutrion_fields,
          $product_field_name,
          $product_variant,
          $products
        );
      }
    }

    $products_result = [];
    foreach ($products as $product_key => $product) {
      $salsify_id = $product_variant['salsify:id'] . '_' . $product_key . '_' . static::PRODUCT_VARIANT_CONTENT_TYPE;
      $product['Trade Item Description'] = $product_variant['Trade Item Description'] . '_' . $product_key;
      $product['salsify:id'] = $salsify_id;
      $product['GTIN'] = $salsify_id;
      $product['salsify:created_at'] = $product_variant['salsify:created_at'];
      $product['salsify:updated_at'] = $product_variant['salsify:updated_at'];
      $product['CMS: content type'] = static::PRODUCT_VARIANT_CONTENT_TYPE;

      $products_result[] = $product;

      $empty_product = [];
      $salsify_id = $product_variant['salsify:id'] . '_' . $product_key . '_' . static::PRODUCT_CONTENT_TYPE;
      $empty_product['Trade Item Description'] = $product_variant['Trade Item Description'] . '_' . $product_key;
      $empty_product['salsify:id'] = $salsify_id;
      $empty_product['GTIN'] = $salsify_id;
      $empty_product['salsify:created_at'] = $product_variant['salsify:created_at'];
      $empty_product['salsify:updated_at'] = $product_variant['salsify:updated_at'];
      $empty_product['CMS: content type'] = static::PRODUCT_CONTENT_TYPE;
      $empty_product['CMS: not publish'] = TRUE;
      $products_result[] = $empty_product;

      $this->mapping['primary'][$empty_product['salsify:id']][$product['salsify:id']] = static::PRODUCT_VARIANT_CONTENT_TYPE;
    }

    return $products_result;
  }

  /**
   * Fill nutrition records by data.
   *
   * @param array $nutrion_fields
   *   Nutrition fields.
   * @param string $product_field_name
   *   Product field name.
   * @param array $product_variant
   *   Product variant record.
   * @param array $products
   *   Result product array.
   */
  private function fillNutrionRecordsByData(
    array $nutrion_fields,
    string $product_field_name,
    array $product_variant,
    array &$products
  ) {
    foreach ($nutrion_fields as $nutrion_field) {
      $matches = [];
      preg_match('/^' . $product_field_name . ' ([0-9])+$/', $nutrion_field, $matches);
      $products[$matches[1]][$product_field_name] = $product_variant[$nutrion_field];
    }
  }

  /**
   * Add product entities into response based on data.
   *
   * @param string $response
   *   Products data.
   *
   * @return string
   *   Response data.
   */
  public function addProductMultipacks($response) {

    $products = [];

    foreach ($this->getProductsData($response) as $product) {
      if (isset($product['CMS: Variety']) &&
        strtolower($product['CMS: Variety']) == 'yes' &&
        static::isProductVariant($product)) {
        $product_multipack = $this->createProductFromProductVariant(
          static::PRODUCT_MULTIPACK_CONTENT_TYPE,
          SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT_MULTIPACK,
          $product
        );
        $generated_products = $this->createNutritionProductsFromProductVariant(
          $product
        );
        $products[$product['CMS: Product Family Groups ID']] = $product_multipack;
        $this->mapping['primary'][$product_multipack['salsify:id']][$product['salsify:id']] = static::PRODUCT_VARIANT_CONTENT_TYPE;

        $this->fillMappingByGeneratedProducts($generated_products, $product_multipack['salsify:id']);
        $products = array_merge($products, $generated_products);
      }
      $products[] = $product;
    }

    $response = Json::decode($response);
    $response['data'] = array_values($products);

    return Json::encode($response);
  }

  /**
   * Add generated product ids to the mapping list.
   *
   * @param array $generated_products
   *   Generated product records.
   * @param string $multipack_id
   *   Multipack salsify id.
   */
  public function fillMappingByGeneratedProducts(array $generated_products, string $multipack_id) {
    foreach ($generated_products as $product) {
      if ($product['CMS: content type'] == static::PRODUCT_CONTENT_TYPE) {
        $this->mapping['primary'][$multipack_id][$product['salsify:id']] = $product['CMS: content type'];
      }
    }
  }

  /**
   * Get primary mapping of products.
   *
   * @return array
   *   Mapping (Product->Product variants, Product Multipack->Products, Vars).
   */
  public function getPrimaryMapping() {
    return $this->mapping['primary'];
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

  /**
   * Sort products by type: product_variant, then product, then multipack.
   *
   * @param array $products
   *   Products.
   */
  public function sortProducts(array &$products) {
    usort($products, function ($product_one, $product_two) {
      if ($product_one['CMS: content type'] == $product_two['CMS: content type']) {
        return 0;
      }
      if ($product_one['CMS: content type'] == ProductHelper::PRODUCT_VARIANT_CONTENT_TYPE &&
        $product_two['CMS: content type'] == ProductHelper::PRODUCT_CONTENT_TYPE) {
        return -1;
      }
      if ($product_one['CMS: content type'] == ProductHelper::PRODUCT_CONTENT_TYPE &&
        $product_two['CMS: content type'] == ProductHelper::PRODUCT_MULTIPACK_CONTENT_TYPE) {
        return -1;
      }
      if ($product_one['CMS: content type'] == ProductHelper::PRODUCT_VARIANT_CONTENT_TYPE &&
        $product_two['CMS: content type'] == ProductHelper::PRODUCT_MULTIPACK_CONTENT_TYPE) {
        return -1;
      }
      return 1;
    });
  }

}
