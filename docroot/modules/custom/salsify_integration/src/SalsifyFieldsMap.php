<?php

namespace Drupal\salsify_integration;

/**
 * Class FieldsMap.
 *
 * @package Drupal\salsify_integration
 */
class SalsifyFieldsMap {

  public const SALSIFY_FIELD_MAPPING_PRODUCT_VARIANT = [
    'field_product_sku' => 'GTIN',
    'field_product_size' => 'Case Net Weight',
    'field_product_allergen_statement' => 'Allergen Statement',
    // 'field_product_allergen_warnings' => 'Allergen Statement',
    'field_product_ingredients' => 'Complete Ingredient Statement',
    'field_product_serving_size' => 'Serving Size',
    'field_product_servings_per' => 'Servings per Container',
    'field_product_protein' => 'Protein',
    'field_product_protein_daily' => NULL,
    'field_product_calories' => 'Calories',
    'field_product_calories_fat' => 'Calories from Fat',
    'field_product_total_fat' => 'Total Fat',
    'field_product_total_fat_daily' => 'Total Fat Pct Daily Value',
    'field_product_saturated_fat' => 'Saturated Fat',
    'field_product_saturated_daily' => 'Saturated Fat Pct Daily Value',
    'field_product_trans_fat' => 'Trans Fat',
    'field_product_trans_fat_daily' => NULL,
    'field_product_cholesterol' => 'Cholesterol',
    'field_product_cholesterol_daily' => 'Cholesterol Pct Daily Value',
    'field_product_sodium' => 'Sodium',
    'field_product_sodium_daily' => 'Sodium Pct Daily Value',
    'field_product_carb' => 'Total Carbohydrate',
    'field_product_carb_daily' => 'Total Carbohydrate Pct Daily Value',
    'field_product_dietary_fiber' => 'Dietary Fiber',
    'field_product_dietary_daily' => 'Dietary Fiber Pct Daily Value',
    'field_product_sugars' => 'Sugars',
    'field_product_sugars_daily' => NULL,
    'field_product_calcium' => 'Calcium',
    'field_product_calcium_daily' => 'Calcium Pct Daily Value',
    'field_product_vitamin_a' => 'Vitamin A',
    'field_product_vitamin_a_daily' => 'Vitamin A Pct Daily Value',
    'field_product_vitamin_c' => NULL,
    'field_product_vitamin_c_daily' => 'Vitamin C Pct Daily Value',
    'field_product_vitamin_d' => NULL,
    'field_product_vitamin_d_daily' => 'Vitamin D Pct',
    'field_product_potassium' => 'Potassium',
    'field_product_potassium_daily' => 'Potassium pct',
    'field_product_iron' => 'Iron',
    'field_product_iron_daily' => 'Iron Pct Daily Value',
    'field_product_key_image' => 'Hero Image',
    'field_product_image_1' => 'ATF Image 2',
    'field_product_image_2' => 'ATF Image 3',
    'field_product_image_3' => 'ATF Image 4',
    'field_product_image_4' => 'ATF Image 5',
  ];

  public const SALSIFY_FIELD_MAPPING_PRODUCT = [
    // Enumirated.
    'field_product_brand' => 'Brand Name',
    'field_product_sub_brand' => 'Sub Brand',
    'field_product_segment' => 'Amazon Segment',
    'field_product_name' => 'Generic Product Name',
    'field_product_description' => 'Generic Product Description',
    // Enumirated.
    'field_product_format' => 'Pack Size',
    'field_product_market' => NULL,
    // Enumirated.
    'field_product_trade_description' => NULL,
    // Enumirated.
    'field_product_flavor' => "CMS: Flavor",

  ];

}
