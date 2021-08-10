<?php

namespace Drupal\mars_common\Form\Alter;

/**
 * Class ProductLayoutFormAlter contains list of required sections.
 *
 * @package Drupal\mars_common\Form\Alter
 */
class ProductLayoutFormAlter extends LayoutFormAlterBase {

  const FIXED_SECTIONS = [
    'product_product_detail_hero',
    'product_product_recommendations',
  ];

}
