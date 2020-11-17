<?php

namespace Drupal\mars_common\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\File;

/**
 * Provides a form element for uploading a file.
 *
 * If you add this element to a form the enctype="multipart/form-data" attribute
 * will automatically be added to the form element.
 *
 * Properties:
 * - #multiple: A Boolean indicating whether multiple files may be uploaded.
 * - #size: The size of the file input element in characters.
 *
 * @FormElement("file")
 */
class OverrideFile extends File {

  /**
   * Processes a file upload element, make use of #multiple if present.
   */
  public static function processFile(&$element, FormStateInterface $form_state, &$complete_form) {
    if ($element['#multiple']) {
      $element['#attributes']['multiple'] = 'multiple';
      $element['#name'] .= '[]';
    }
    if ($element['#name'] === 'files[settings]') {
      $element['#name'] = 'files' . '[' . implode('_', $element['#parents']) . ']';
    }
    return $element;
  }

}
