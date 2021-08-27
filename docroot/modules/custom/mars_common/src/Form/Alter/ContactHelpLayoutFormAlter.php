<?php

namespace Drupal\mars_common\Form\Alter;

/**
 * Class ContactHelpLayoutFormAlter contains list of required sections.
 *
 * @package Drupal\mars_common\Form\Alter
 */
class ContactHelpLayoutFormAlter extends LayoutFormAlterBase {

  const FIXED_SECTIONS = [
    'contact_help_parent_page_header',
    'contact_help_faq',
    'contact_help_contact_banner',
  ];

}
