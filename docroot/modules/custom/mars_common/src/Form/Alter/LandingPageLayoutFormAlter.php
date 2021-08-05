<?php

namespace Drupal\mars_common\Form\Alter;

/**
 * Class LandingPageLayoutFormAlter contains list of required sections.
 *
 * @package Drupal\mars_common\Form\Alter
 */
class LandingPageLayoutFormAlter extends LayoutFormAlterBase {

  const FIXED_SECTIONS = [
    'landing_page_introduction',
    'landing_page_storytelling',
  ];

}
