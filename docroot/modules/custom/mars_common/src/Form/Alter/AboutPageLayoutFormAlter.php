<?php

namespace Drupal\mars_common\Form\Alter;

/**
 * Class AboutPageLayoutFormAlter contains list of required sections.
 *
 * @package Drupal\mars_common\Form\Alter
 */
class AboutPageLayoutFormAlter extends LayoutFormAlterBase {

  const FIXED_SECTIONS = [
    'about_page_introduction',
    'about_page_storytelling',
    'about_page_contact',
  ];

}
