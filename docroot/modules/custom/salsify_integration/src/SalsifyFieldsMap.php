<?php

namespace Drupal\salsify_integration;

/**
 * Class FieldsMap.
 *
 * @package Drupal\salsify_integration
 */
class SalsifyFieldsMap {

  public const SALSIFY_FIELD_MAPPING_PRODUCT_VARIANT = [
    'field_product_sku' => ['salsify:id' => 'GTIN', 'salsify:data_type' => 'string'],
    'field_product_family_master' => ['salsify:id' => 'CMS: Product Variant Family Master', 'salsify:data_type' => 'boolean'],
    'field_product_size' => ['salsify:id' => 'Net Content', 'salsify:data_type' => 'string'],
    'field_product_allergen_warnings' => ['salsify:id' => 'Allergen Statement', 'salsify:data_type' => 'string'],
    'field_product_ingredients' => ['salsify:id' => 'Complete Ingredient Statement', 'salsify:data_type' => 'string'],
    'field_product_serving_size' => ['salsify:id' => 'Serving Size', 'salsify:data_type' => 'string'],
    'field_product_servings_per' => ['salsify:id' => 'Servings per Container', 'salsify:data_type' => 'string'],
    'field_product_protein' => ['salsify:id' => 'Protein', 'salsify:data_type' => 'string'],
    'field_product_protein_daily' => ['salsify:id' => 'Protein Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_calories' => ['salsify:id' => 'Calories', 'salsify:data_type' => 'string'],
    'field_product_calories_fat' => ['salsify:id' => 'Calories from Fat', 'salsify:data_type' => 'string'],
    'field_product_total_fat' => ['salsify:id' => 'Total Fat', 'salsify:data_type' => 'string'],
    'field_product_total_fat_daily' => ['salsify:id' => 'Total Fat Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_saturated_fat' => ['salsify:id' => 'Saturated Fat', 'salsify:data_type' => 'string'],
    'field_product_saturated_daily' => ['salsify:id' => 'Saturated Fat Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_trans_fat' => ['salsify:id' => 'Trans Fat', 'salsify:data_type' => 'string'],
    'field_product_trans_fat_daily' => ['salsify:id' => 'Trans Fat Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_cholesterol' => ['salsify:id' => 'Cholesterol', 'salsify:data_type' => 'string'],
    'field_product_cholesterol_daily' => ['salsify:id' => 'Cholesterol Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_sodium' => ['salsify:id' => 'Sodium', 'salsify:data_type' => 'string'],
    'field_product_sodium_daily' => ['salsify:id' => 'Sodium Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_carb' => ['salsify:id' => 'Total Carbohydrate', 'salsify:data_type' => 'string'],
    'field_product_carb_daily' => ['salsify:id' => 'Total Carbohydrate Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_dietary_fiber' => ['salsify:id' => 'Dietary Fiber', 'salsify:data_type' => 'string'],
    'field_product_dietary_daily' => ['salsify:id' => 'Dietary Fiber Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_sugars' => ['salsify:id' => 'Sugars', 'salsify:data_type' => 'string'],
    'field_product_sugars_daily' => ['salsify:id' => 'Added Sugars Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_calcium' => ['salsify:id' => 'Calcium', 'salsify:data_type' => 'string'],
    'field_product_calcium_daily' => ['salsify:id' => 'Calcium Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_vitamin_a' => ['salsify:id' => 'Vitamin A', 'salsify:data_type' => 'string'],
    'field_product_vitamin_a_daily' => ['salsify:id' => 'Vitamin A Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_vitamin_c' => ['salsify:id' => 'Vitamin C', 'salsify:data_type' => 'string'],
    'field_product_vitamin_c_daily' => ['salsify:id' => 'Vitamin C Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_vitamin_d' => ['salsify:id' => 'Vitamin D', 'salsify:data_type' => 'string'],
    'field_product_vitamin_d_daily' => ['salsify:id' => 'Vitamin D Pct', 'salsify:data_type' => 'string'],
    'field_product_potassium' => ['salsify:id' => 'Potassium', 'salsify:data_type' => 'string'],
    'field_product_potassium_daily' => ['salsify:id' => 'Potassium pct', 'salsify:data_type' => 'string'],
    'field_product_iron' => ['salsify:id' => 'Iron', 'salsify:data_type' => 'string'],
    'field_product_iron_daily' => ['salsify:id' => 'Iron Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_key_image' => ['salsify:id' => 'CMS: Image 1', 'salsify:data_type' => 'digital_asset'],
    'field_product_image_1' => ['salsify:id' => 'CMS: Image 2', 'salsify:data_type' => 'digital_asset'],
    'field_product_image_2' => ['salsify:id' => 'CMS: Image 3', 'salsify:data_type' => 'digital_asset'],
    'field_product_image_3' => ['salsify:id' => 'CMS: Image 4', 'salsify:data_type' => 'digital_asset'],
    'field_product_image_4' => ['salsify:id' => 'CMS: Image 5', 'salsify:data_type' => 'digital_asset'],
  ];

  public const SALSIFY_FIELD_MAPPING_PRODUCT = [
    'field_product_sub_brand' => ['salsify:id' => 'Sub Brand', 'salsify:data_type' => 'string'],
    'field_product_segment' => ['salsify:id' => 'Amazon Segment', 'salsify:data_type' => 'string'],
    'field_product_name' => ['salsify:id' => 'CMS: Product Name', 'salsify:data_type' => 'string'],
    'field_product_description' => ['salsify:id' => 'CMS: Description', 'salsify:data_type' => 'string'],
    // Enumerated.
    'field_product_format' => ['salsify:id' => 'CMS: Format', 'salsify:data_type' => 'enumerated'],
    'field_product_market' => ['salsify:id' => 'CMS: Market', 'salsify:data_type' => 'string'],
    // Enumerated.
    'field_product_trade_description' => ['salsify:id' => 'Trade Item Description', 'salsify:data_type' => 'enumerated'],
    // Enumerated.
    'field_product_flavor' => ['salsify:id' => 'CMS: Flavor', 'salsify:data_type' => 'enumerated'],
    'field_product_variants' => ['salsify:id' => 'CMS: Child variants', 'salsify:data_type' => 'entity_ref'],
    // There is a custom logic for fields with 'complex' data type in
    // SalsifyImportField class.
    'field_meta_tags' => ['salsify:id' => 'CMS: Meta tags', 'salsify:data_type' => 'complex'],
  ];

  public const SALSIFY_FIELD_MAPPING_PRODUCT_MULTIPACK = self::SALSIFY_FIELD_MAPPING_PRODUCT + [
    'field_product_pack_items' => ['salsify:id' => 'CMS: Child products', 'salsify:data_type' => 'entity_ref'],
  ];

}
