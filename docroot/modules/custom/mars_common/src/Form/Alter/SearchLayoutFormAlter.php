<?php

namespace Drupal\mars_common\Form\Alter;

/**
 * Class SearchLayoutFormAlter contains list of required sections.
 *
 * @package Drupal\mars_common\Form\Alter
 */
class SearchLayoutFormAlter extends LayoutFormAlterBase {

  const FIXED_SECTIONS = [
    'search_page_header',
    'search_page_results',
  ];

}
