<?php

namespace Drupal\mars_search\Processors;

/**
 * SearchCategoriesInterface contains description for search category methods.
 */
interface SearchCategoriesInterface {

  /**
   * List of vocabularies which are included in indexing.
   *
   * @var array
   */
  const TAXONOMY_VOCABULARIES = [
    'mars_category' => [
      'label' => 'CATEGORY',
      'content_types' => ['product'],
    ],
    'mars_brand_initiatives' => [
      'label' => 'BRAND INITIATIVES',
      'content_types' => [
        'article',
        'recipe',
        'landing_page',
        'campaign',
        'product',
      ],
    ],
    'mars_occasions' => [
      'label' => 'OCCASIONS',
      'content_types' => [
        'article', 'recipe', 'product', 'landing_page', 'campaign',
      ],
    ],
    'mars_flavor' => [
      'label' => 'FLAVOR',
      'content_types' => ['product'],
    ],
    'mars_format' => [
      'label' => 'FORMAT',
      'content_types' => ['product'],
    ],
    'mars_diet_allergens' => [
      'label' => 'DIET & ALLERGENS',
      'content_types' => ['product', 'recipe'],
    ],
    'mars_culture' => [
      'label' => 'CULTURE',
      'content_types' => ['recipe'],
    ],
    'mars_food_type' => [
      'label' => 'FOOD TYPE',
      'content_types' => ['recipe'],
    ],
    'mars_main_ingredient' => [
      'label' => 'MAIN INGREDIENT',
      'content_types' => ['recipe'],
    ],
    'mars_meal_type' => [
      'label' => 'MEAL TYPE',
      'content_types' => ['recipe'],
    ],
    'mars_method' => [
      'label' => 'METHOD',
      'content_types' => ['recipe'],
    ],
    'mars_prep_time' => [
      'label' => 'PREP TIME',
      'content_types' => ['recipe'],
    ],
    'mars_product_used' => [
      'label' => 'PRODUCT USED',
      'content_types' => ['recipe'],
    ],
    'mars_recipe_collection' => [
      'label' => 'RECIPE COLLECTION',
      'content_types' => ['recipe'],
    ],
    'mars_trade_item_description' => [
      'label' => 'TRADE ITEM DESCRIPTION',
      'content_types' => ['product'],
    ],
  ];

  /**
   * List of content types which are included in indexing.
   *
   * @var array
   */
  const CONTENT_TYPES = [
    'product' => 'Product',
    'article' => 'Article',
    'recipe' => 'Recipe',
    'campaign' => 'Campaign',
    'landing_page' => 'Landing page',
  ];

  /**
   * Return processed list of categories.
   */
  public function getCategories();

  /**
   * Return list of content types which are included in indexing.
   */
  public function getContentTypes();

}
