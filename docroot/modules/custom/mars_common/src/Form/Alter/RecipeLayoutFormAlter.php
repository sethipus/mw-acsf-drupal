<?php

namespace Drupal\mars_common\Form\Alter;

/**
 * Class RecipeLayoutFormAlter.
 *
 * @package Drupal\mars_common\Form\Alter
 */
class RecipeLayoutFormAlter extends LayoutFormAlterBase {

  const FIXED_SECTIONS = [
    'recipe_recipe_hero',
    'recipe_recipe_body',
    // 'recipe_recipe_recommendations',
  ];

}
