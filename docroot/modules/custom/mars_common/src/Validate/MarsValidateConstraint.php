<?php

namespace Drupal\mars_common\Validate;

use Drupal\Core\Form\FormStateInterface;

/**
 * Form API callback. Validate element value.
 */
class MarsValidateConstraint {

  /**
   * Validates given element.
   *
   * @param array $element
   *   The form element to process.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param array $form
   *   The complete form structure.
   */
  public static function validate(array &$element, FormStateInterface $formState, array &$form) {
    $webformKey = $element['#webform_key'];
    $value = $element['#value'];

    // Skip empty unique fields or arrays.
    if ($element['#required']  && (empty($value) || $value === '' || is_array($value))) {
      if ($webformKey !== 'name') {
        $element['#suffix'] = '<span class="validation-error">required</span>';
        $formState->setError($element);
      }
      return;
    }

    // Validate email pattern.
    if ($webformKey === 'email' && $value !== '' && !\Drupal::service('email.validator')->isValid($value)) {
      $element['#suffix'] = '<span class="validation-error">Email is not valid</span>';
    }
  }

}
