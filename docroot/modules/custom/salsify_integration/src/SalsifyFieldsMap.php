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
    'field_product_size' => ['salsify:id' => 'Case Net Weight', 'salsify:data_type' => 'string'],
    'field_product_allergen_statement' => ['salsify:id' => 'Allergen Statement', 'salsify:data_type' => 'string'],
    // 'field_product_allergen_warnings' => 'Allergen Statement',
    'field_product_ingredients' => ['salsify:id' => 'Complete Ingredient Statement', 'salsify:data_type' => 'string'],
    'field_product_serving_size' => ['salsify:id' => 'Serving Size', 'salsify:data_type' => 'string'],
    'field_product_servings_per' => ['salsify:id' => 'Servings per Container', 'salsify:data_type' => 'string'],
    'field_product_protein' => ['salsify:id' => 'Protein', 'salsify:data_type' => 'string'],
    'field_product_protein_daily' => NULL,
    'field_product_calories' => ['salsify:id' => 'Calories', 'salsify:data_type' => 'string'],
    'field_product_calories_fat' => ['salsify:id' => 'Calories from Fat', 'salsify:data_type' => 'string'],
    'field_product_total_fat' => ['salsify:id' => 'Total Fat', 'salsify:data_type' => 'string'],
    'field_product_total_fat_daily' => ['salsify:id' => 'Total Fat Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_saturated_fat' => ['salsify:id' => 'Saturated Fat', 'salsify:data_type' => 'string'],
    'field_product_saturated_daily' => ['salsify:id' => 'Saturated Fat Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_trans_fat' => ['salsify:id' => 'Trans Fat', 'salsify:data_type' => 'string'],
    'field_product_trans_fat_daily' => NULL,
    'field_product_cholesterol' => ['salsify:id' => 'Cholesterol', 'salsify:data_type' => 'string'],
    'field_product_cholesterol_daily' => ['salsify:id' => 'Cholesterol Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_sodium' => ['salsify:id' => 'Sodium', 'salsify:data_type' => 'string'],
    'field_product_sodium_daily' => ['salsify:id' => 'Sodium Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_carb' => ['salsify:id' => 'Total Carbohydrate', 'salsify:data_type' => 'string'],
    'field_product_carb_daily' => ['salsify:id' => 'Total Carbohydrate Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_dietary_fiber' => ['salsify:id' => 'Dietary Fiber', 'salsify:data_type' => 'string'],
    'field_product_dietary_daily' => ['salsify:id' => 'Dietary Fiber Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_sugars' => ['salsify:id' => 'Sugars', 'salsify:data_type' => 'string'],
    'field_product_sugars_daily' => NULL,
    'field_product_calcium' => ['salsify:id' => 'Calcium', 'salsify:data_type' => 'string'],
    'field_product_calcium_daily' => ['salsify:id' => 'Calcium Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_vitamin_a' => ['salsify:id' => 'Vitamin A', 'salsify:data_type' => 'string'],
    'field_product_vitamin_a_daily' => ['salsify:id' => 'Vitamin A Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_vitamin_c' => NULL,
    'field_product_vitamin_c_daily' => ['salsify:id' => 'Vitamin C Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_vitamin_d' => NULL,
    'field_product_vitamin_d_daily' => ['salsify:id' => 'Vitamin D Pct', 'salsify:data_type' => 'string'],
    'field_product_potassium' => ['salsify:id' => 'Potassium', 'salsify:data_type' => 'string'],
    'field_product_potassium_daily' => ['salsify:id' => 'Potassium pct', 'salsify:data_type' => 'string'],
    'field_product_iron' => ['salsify:id' => 'Iron', 'salsify:data_type' => 'string'],
    'field_product_iron_daily' => ['salsify:id' => 'Iron Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_key_image' => ['salsify:id' => 'Hero Image', 'salsify:data_type' => 'digital_asset'],
    'field_product_image_1' => ['salsify:id' => 'ATF Image 2', 'salsify:data_type' => 'digital_asset'],
    'field_product_image_2' => ['salsify:id' => 'ATF Image 3', 'salsify:data_type' => 'digital_asset'],
    'field_product_image_3' => ['salsify:id' => 'ATF Image 4', 'salsify:data_type' => 'digital_asset'],
    'field_product_image_4' => ['salsify:id' => 'ATF Image 5', 'salsify:data_type' => 'digital_asset'],
  ];

  public const SALSIFY_FIELD_MAPPING_PRODUCT = [
    // Enumirated.
    'field_product_brand' => ['salsify:id' => 'Brand Name', 'salsify:data_type' => 'enumerated'],
    'field_product_sub_brand' => ['salsify:id' => 'Sub Brand', 'salsify:data_type' => 'string'],
    'field_product_segment' => ['salsify:id' => 'Amazon Segment', 'salsify:data_type' => 'string'],
    'field_product_name' => ['salsify:id' => 'Generic Product Name', 'salsify:data_type' => 'string'],
    'field_product_description' => ['salsify:id' => 'Generic Product Description', 'salsify:data_type' => 'string'],
    // Enumirated.
    'field_product_format' => ['salsify:id' => 'Pack Size', 'salsify:data_type' => 'enumerated'],
    'field_product_market' => NULL,
    // Enumirated.
    'field_product_trade_description' => NULL,
    // Enumirated.
    'field_product_flavor' => ['salsify:id' => 'CMS: Flavor', 'salsify:data_type' => 'enumerated'],

  ];

}
