<?php

namespace Drupal\mars_common\Form\Alter;

/**
 * Class HomepageLayoutFormAlter contains list of required sections.
 *
 * @package Drupal\mars_common\Form\Alter
 */
class HomepageLayoutFormAlter extends LayoutFormAlterBase {

  const FIXED_SECTIONS = [
    'homepage_homepage_hero',
    'homepage_drive_to_product',
  ];

}
