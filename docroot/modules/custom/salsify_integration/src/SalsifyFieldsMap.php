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
    'field_product_serving_size' => ['salsify:id' => 'Serving Size', 'salsify:data_type' => 'string'],
    'field_product_servings_per' => ['salsify:id' => 'Servings per Container', 'salsify:data_type' => 'string'],
    'field_product_allergen_warnings' => ['salsify:id' => 'Allergen Statement', 'salsify:data_type' => 'string'],
    'field_product_ingredients' => ['salsify:id' => 'Complete Ingredient Statement', 'salsify:data_type' => 'string'],
    'field_product_description' => ['salsify:id' => 'CMS: Description', 'salsify:data_type' => 'string'],
    'field_product_cooking_instruct' => ['salsify:id' => 'Brandbank Cooking Guidelines', 'salsify:data_type' => 'string'],
    'field_product_protein' => [
      'salsify:id' => 'Protein',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Protein Precision',
    ],
    'field_product_protein_daily' => ['salsify:id' => 'Protein Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_calories' => [
      'salsify:id' => 'Calories',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Calories Precision',
    ],
    'field_product_ltd_calories' => [
      'salsify:id' => 'LTD Energy Serving/kcal',
      'salsify:data_type' => 'string',
      'delimiter' => '/',
      'prefix_field' => 'LTD Energy Serving/kJ',
    ],
    'field_product_calories_daily' => [
      'salsify:id' => 'LTD Energy Serving/kcal/Percentage',
      'salsify:data_type' => 'string',
      'delimiter' => '/',
      'prefix_field' => 'LTD Energy Serving/kJ/Percentage',
    ],
    'field_product_calories_fat' => ['salsify:id' => 'Calories from Fat', 'salsify:data_type' => 'string'],
    'field_product_total_fat' => [
      'salsify:id' => 'Total Fat',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Total Fat Precision',
    ],
    'field_product_total_fat_daily' => ['salsify:id' => 'Total Fat Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_saturated_fat' => [
      'salsify:id' => 'Saturated Fat',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Saturated Fat Precision',
    ],
    'field_product_saturated_daily' => ['salsify:id' => 'Saturated Fat Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_trans_fat' => [
      'salsify:id' => 'Trans Fat',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Trans Fat Precision',
    ],
    'field_product_trans_fat_daily' => ['salsify:id' => 'Trans Fat Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_cholesterol' => [
      'salsify:id' => 'Cholesterol',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Cholesterol Precision',
    ],
    'field_product_cholesterol_daily' => ['salsify:id' => 'Cholesterol Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_sodium' => [
      'salsify:id' => 'Sodium',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Sodium Precision',
    ],
    'field_product_sodium_daily' => ['salsify:id' => 'Sodium Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_carb' => ['salsify:id' => 'Total Carbohydrate', 'salsify:data_type' => 'string'],
    'field_product_carb_daily' => ['salsify:id' => 'Total Carbohydrate Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_dietary_fiber' => [
      'salsify:id' => 'Dietary Fiber',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dietary Fiber Precision',
    ],
    'field_product_dietary_daily' => ['salsify:id' => 'Dietary Fiber Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_sugars' => [
      'salsify:id' => 'Sugars',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Sugars Precision',
    ],
    'field_product_total_sugars' => [
      'salsify:id' => 'Total Sugars',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Total Sugars Precision',
    ],
    'field_product_added_sugars' => [
      'salsify:id' => 'Added Sugars',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Added Sugars Precision',
    ],
    'field_product_added_sugars_daily' => ['salsify:id' => 'Added Sugars Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_sugar_alcohol' => [
      'salsify:id' => 'Sugar Alcohol',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Sugar Alcohol Precision',
    ],
    'field_product_sugars_daily' => ['salsify:id' => 'Sugars Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_product_calcium' => [
      'salsify:id' => 'Calcium',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Calcium Precision',
    ],
    'field_product_calcium_daily' => [
      'salsify:id' => 'Calcium Pct',
      'salsify:data_type' => 'string',
      'or' => 'Calcium Pct Daily Value',
    ],
    'field_product_vitamin_a' => [
      'salsify:id' => 'Vitamin A',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Vitamin A Precision',
    ],
    'field_product_reference_intake' => [
      'salsify:id' => 'LDT - Reference intake',
      'salsify:data_type' => 'string',
    ],
    'field_product_fibre' => [
      'salsify:id' => 'LTD Fibre/100g',
      'salsify:data_type' => 'string',
      'prefix_field' => 'LTD Fibre/100g Precision',
    ],
    'field_dual_fibre' => [
      'salsify:id' => 'LTD/fibre/kcal',
      'salsify:data_type' => 'string',
      'prefix_field' => 'LTD/fibre/kcal Precision',
    ],
    'field_product_vitamin_a_daily' => [
      'salsify:id' => 'Vitamin A Pct',
      'salsify:data_type' => 'string',
      'or' => 'Vitamin A Pct Daily Value',
    ],
    'field_product_vitamin_c' => [
      'salsify:id' => 'Vitamin C',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Vitamin C Precision',
    ],
    'field_product_vitamin_c_daily' => [
      'salsify:id' => 'Vitamin C Pct',
      'salsify:data_type' => 'string',
      'or' => 'Vitamin C Pct Daily Value',
    ],
    'field_product_vitamin_d' => [
      'salsify:id' => 'Vitamin D',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Vitamin D Precision',
    ],
    'field_product_vitamin_d_daily' => [
      'salsify:id' => 'Vitamin D Pct',
      'salsify:data_type' => 'string',
      'or' => 'Vitamin D Pct Daily Value',
    ],
    'field_product_thiamin' => ['salsify:id' => 'Thiamin', 'salsify:data_type' => 'string'],
    'field_product_thiamin_daily' => [
      'salsify:id' => 'Thiamin Pct',
      'salsify:data_type' => 'string',
      'or' => 'Thiamin Pct Daily Value',
    ],
    'field_product_niacin' => ['salsify:id' => 'Niacin', 'salsify:data_type' => 'string'],
    'field_product_niacin_daily' => [
      'salsify:id' => 'Niacin Pct',
      'salsify:data_type' => 'string',
      'or' => 'Niacin Pct Daily Value',
    ],
    'field_product_potassium' => [
      'salsify:id' => 'Potassium',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Potassium Precision',
    ],
    'field_product_potassium_daily' => [
      'salsify:id' => 'Potassium Pct',
      'salsify:data_type' => 'string',
      'or' => 'Potassium Pct Daily Value',
    ],
    'field_product_iron' => [
      'salsify:id' => 'Iron',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Iron Precision',
    ],
    'field_product_iron_daily' => [
      'salsify:id' => 'Iron Pct',
      'salsify:data_type' => 'string',
      'or' => 'Iron Pct Daily Value',
    ],
    'field_product_riboflavin' => ['salsify:id' => 'Riboflavin', 'salsify:data_type' => 'string'],
    'field_product_riboflavin_daily' => [
      'salsify:id' => 'Riboflavin Pct',
      'salsify:data_type' => 'string',
      'or' => 'Riboflavin Pct Daily Value',
    ],
    'field_dual_allergen_warnings' => ['salsify:id' => 'Dual Allergen Statement', 'salsify:data_type' => 'string'],
    'field_dual_ingredients' => ['salsify:id' => 'Dual Complete Ingredient Statement', 'salsify:data_type' => 'string'],
    'field_dual_protein' => [
      'salsify:id' => 'Dual Protein',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Protein Precision',
    ],
    'field_dual_protein_daily' => ['salsify:id' => 'Dual Protein Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_dual_calories' => [
      'salsify:id' => 'Dual Calories',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Calories Precision',
    ],
    'field_dual_ltd_calories' => [
      'salsify:id' => 'Dual LTD Energy Serving/kcal',
      'salsify:data_type' => 'string',
      'delimiter' => '/',
      'prefix_field' => 'Dual LTD Energy Serving/kJ',
    ],
    'field_dual_calories_daily' => [
      'salsify:id' => 'Dual LTD Energy Serving/kcal/Percentage',
      'salsify:data_type' => 'string',
      'delimiter' => '/',
      'prefix_field' => 'Dual LTD Energy Serving/kJ/Percentage',
    ],
    'field_dual_calories_fat' => ['salsify:id' => 'Dual Calories from Fat', 'salsify:data_type' => 'string'],
    'field_dual_total_fat' => [
      'salsify:id' => 'Dual Total Fat',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Total Fat Precision',
    ],
    'field_dual_total_fat_daily' => ['salsify:id' => 'Dual Total Fat Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_dual_saturated_fat' => [
      'salsify:id' => 'Dual Saturated Fat',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Saturated Fat Precision',
    ],
    'field_dual_saturated_daily' => ['salsify:id' => 'Dual Saturated Fat Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_dual_trans_fat' => [
      'salsify:id' => 'Dual Trans Fat',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Trans Fat Precision',
    ],
    'field_dual_trans_fat_daily' => ['salsify:id' => 'Dual Trans Fat Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_dual_cholesterol' => [
      'salsify:id' => 'Dual Cholesterol',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Cholesterol Precision',
    ],
    'field_dual_cholesterol_daily' => ['salsify:id' => 'Dual Cholesterol Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_dual_sodium' => [
      'salsify:id' => 'Dual Sodium',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Sodium Precision',
    ],
    'field_dual_sodium_daily' => ['salsify:id' => 'Dual Sodium Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_dual_carb' => ['salsify:id' => 'Dual Total Carbohydrate', 'salsify:data_type' => 'string'],
    'field_dual_carb_daily' => ['salsify:id' => 'Dual Total Carbohydrate Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_dual_dietary_fiber' => [
      'salsify:id' => 'Dual Dietary Fiber',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Dietary Fiber Precision',
    ],
    'field_dual_dietary_daily' => ['salsify:id' => 'Dual Dietary Fiber Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_dual_sugars' => [
      'salsify:id' => 'Dual Sugars',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Sugars Precision',
    ],
    'field_dual_total_sugars' => [
      'salsify:id' => 'Dual Total Sugars',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Total Sugars Precision',
    ],
    'field_dual_added_sugars' => [
      'salsify:id' => 'Dual Added Sugars',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Added Sugars Precision',
    ],
    'field_dual_added_sugars_daily' => ['salsify:id' => 'Dual Added Sugars Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_dual_sugar_alcohol' => [
      'salsify:id' => 'Dual Sugar Alcohol',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Sugar Alcohol Precision',
    ],
    'field_dual_sugars_daily' => ['salsify:id' => 'Dual Sugars Pct Daily Value', 'salsify:data_type' => 'string'],
    'field_dual_calcium' => [
      'salsify:id' => 'Dual Calcium',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Calcium Precision',
    ],
    'field_dual_calcium_daily' => [
      'salsify:id' => 'Dual Calcium Pct',
      'salsify:data_type' => 'string',
      'or' => 'Dual Calcium Pct Daily Value',
    ],
    'field_dual_vitamin_a' => [
      'salsify:id' => 'Dual Vitamin A',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Vitamin A Precision',
    ],
    'field_dual_vitamin_a_daily' => [
      'salsify:id' => 'Dual Vitamin A Pct',
      'salsify:data_type' => 'string',
      'or' => 'Dual Vitamin A Pct Daily Value',
    ],
    'field_dual_vitamin_c' => [
      'salsify:id' => 'Dual Vitamin C',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Vitamin C Precision',
    ],
    'field_dual_vitamin_c_daily' => [
      'salsify:id' => 'Dual Vitamin C Pct',
      'salsify:data_type' => 'string',
      'or' => 'Dual Vitamin C Pct Daily Value',
    ],
    'field_dual_vitamin_d' => [
      'salsify:id' => 'Dual Vitamin D',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Vitamin D Precision',
    ],
    'field_dual_vitamin_d_daily' => [
      'salsify:id' => 'Dual Vitamin D Pct',
      'salsify:data_type' => 'string',
      'or' => 'Dual Vitamin D Pct Daily Value',
    ],
    'field_dual_thiamin' => ['salsify:id' => 'Dual Thiamin', 'salsify:data_type' => 'string'],
    'field_dual_thiamin_daily' => [
      'salsify:id' => 'Dual Thiamin Pct',
      'salsify:data_type' => 'string',
      'or' => 'Dual Thiamin Pct Daily Value',
    ],
    'field_dual_niacin' => ['salsify:id' => 'Dual Niacin', 'salsify:data_type' => 'string'],
    'field_dual_niacin_daily' => [
      'salsify:id' => 'Dual Niacin Pct',
      'salsify:data_type' => 'string',
      'or' => 'Dual Niacin Pct Daily Value',
    ],
    'field_dual_potassium' => [
      'salsify:id' => 'Dual Potassium',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Potassium Precision',
    ],
    'field_dual_potassium_daily' => [
      'salsify:id' => 'Dual Potassium Pct',
      'salsify:data_type' => 'string',
      'or' => 'Dual Potassium Pct Daily Value',
    ],
    'field_dual_iron' => [
      'salsify:id' => 'Dual Iron',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Iron Precision',
    ],
    'field_dual_iron_daily' => [
      'salsify:id' => 'Dual Iron Pct',
      'salsify:data_type' => 'string',
      'or' => 'Dual Iron Pct Daily Value',
    ],
    'field_dual_riboflavin' => ['salsify:id' => 'Dual Riboflavin', 'salsify:data_type' => 'string'],
    'field_dual_riboflavin_daily' => [
      'salsify:id' => 'Dual Riboflavin Pct',
      'salsify:data_type' => 'string',
      'or' => 'Dual Riboflavin Pct Daily Value',
    ],
    'field_product_consumption_1' => ['salsify:id' => 'Consumption Context', 'salsify:data_type' => 'string'],
    'field_product_consumption_2' => ['salsify:id' => 'Dual Consumption Context', 'salsify:data_type' => 'string'],
    'field_product_key_image' => ['salsify:id' => 'CMS: Image 1', 'salsify:data_type' => 'digital_asset'],
    'field_product_image_1' => ['salsify:id' => 'CMS: Image 2', 'salsify:data_type' => 'digital_asset'],
    'field_product_image_2' => ['salsify:id' => 'CMS: Image 3', 'salsify:data_type' => 'digital_asset'],
    'field_product_image_3' => ['salsify:id' => 'CMS: Image 4', 'salsify:data_type' => 'digital_asset'],
    'field_product_image_4' => ['salsify:id' => 'CMS: Image 5', 'salsify:data_type' => 'digital_asset'],
    'field_product_polyunsaturated_fa' => [
      'salsify:id' => 'Polyunsaturated Fat',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Polyunsaturated Fat Precision',
    ],
    'field_product_monounsaturated_fa' => [
      'salsify:id' => 'Monounsaturated Fat',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Monounsaturated Fat Precision',
    ],
    'field_product_folate' => [
      'salsify:id' => 'Folate',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Folate Precision',
    ],
    'field_product_folate_daily' => [
      'salsify:id' => 'Folate Pct Daily Value',
      'salsify:data_type' => 'string',
    ],
    'field_dual_polyunsaturated_fa' => [
      'salsify:id' => 'Dual Polyunsaturated Fat',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Polyunsaturated Fat Precision',
    ],
    'field_dual_monounsaturated_fa' => [
      'salsify:id' => 'Dual Monounsaturated Fat',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Monounsaturated Fat Precision',
    ],
    'field_dual_folate' => [
      'salsify:id' => 'Dual Folate',
      'salsify:data_type' => 'string',
      'prefix_field' => 'Dual Folate Precision',
    ],
    'field_dual_folate_daily' => [
      'salsify:id' => 'Dual Folate Pct Daily Value',
      'salsify:data_type' => 'string',
    ],
  ];

  public const SALSIFY_FIELD_MAPPING_PRODUCT = [
    'field_product_category' => ['salsify:id' => 'CMS: Category Computed', 'salsify:data_type' => 'enumerated'],
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
    'field_product_generated' => ['salsify:id' => 'CMS: multipack generated', 'salsify:data_type' => 'boolean'],
  ];

  public const SALSIFY_FIELD_MAPPING_PRODUCT_MULTIPACK = self::SALSIFY_FIELD_MAPPING_PRODUCT + [
    'field_product_pack_items' => ['salsify:id' => 'CMS: Child products', 'salsify:data_type' => 'entity_ref'],
  ];

}
