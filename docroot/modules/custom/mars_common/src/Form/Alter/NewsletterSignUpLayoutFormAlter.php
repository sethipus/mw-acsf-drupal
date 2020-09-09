<?php

namespace Drupal\mars_common\Form\Alter;

/**
 * Class NewslatterSignUpLayoutFormAlter.
 *
 * @package Drupal\mars_common\Form\Alter
 */
class NewsletterSignUpLayoutFormAlter extends LayoutFormAlterBase {

  const FIXED_SECTIONS = [
    'newsletter_sign_up_parent_page_header',
    'newsletter_sign_up_main_section',
    'newsletter_sign_up_recommended_products',
  ];

}
