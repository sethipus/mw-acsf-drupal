<?php

namespace Drupal\mars_common\Form\Alter;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class CampaignLayoutFormAlter.
 *
 * @package Drupal\mars_common\Form\Alter
 */
class CampaignLayoutFormAlter extends LayoutFormAlterBase {

  const FIXED_SECTIONS = [
    'campaign_section',
  ];

  /**
   * {@inheritdoc}
   */
  public static function validate(array &$form, FormStateInterface $form_state) {
    return [];
  }

}
