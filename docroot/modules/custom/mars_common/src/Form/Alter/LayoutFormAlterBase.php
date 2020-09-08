<?php

namespace Drupal\mars_common\Form\Alter;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class LayoutFormAlterBase.
 *
 * @package Drupal\mars_common\Form\Alter
 */
abstract class LayoutFormAlterBase {

  /**
   * Alter form.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public static function validate(array &$form, FormStateInterface $form_state) {
    $section_storage = $form_state->getFormObject()->getSectionStorage();
    $section_settings = $section_storage
      ->getContextValue('display')
      ->getThirdPartySettings('layout_builder');

    if (!empty($section_settings)) {
      foreach ($section_settings['sections'] as $section) {

        /* @var \Drupal\layout_builder\Section $section */
        if (
          in_array($section->getLayoutId(), static::FIXED_SECTIONS) &&
          empty($section->getComponents())
        ) {
          $form_state->setErrorByName(
            'layout_builder_' . $section->getLayoutId(),
            t(
              'Please add component in @section section.',
              ['@section' => $section->getLayoutSettings()['label']]
            )
          );
        }
      }
    }
  }

}
