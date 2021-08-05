<?php

namespace Drupal\mars_common\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\layout_builder\Form\DefaultsEntityForm;
use Drupal\layout_builder\Form\OverridesEntityForm;

/**
 * Class LayoutFormAlterBase is responsible for layout validation logic.
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
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public static function validate(array &$form, FormStateInterface $form_state) {
    $form_object = $form_state->getFormObject();
    $sections = [];

    if ($form_object instanceof OverridesEntityForm) {
      $layout_value = $form_object->getSectionStorage()
        ->getContextValue('entity')
        ->get('layout_builder__layout')
        ->getValue();
      $sections = array_column($layout_value, 'section');
    }
    elseif ($form_object instanceof DefaultsEntityForm) {
      $section_storage = $form_object->getSectionStorage();
      $section_settings = $section_storage
        ->getContextValue('display')
        ->getThirdPartySettings('layout_builder');
      $sections = $section_settings['sections'];
    }

    foreach ($sections as $section) {
      /** @var \Drupal\layout_builder\Section $section */
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
