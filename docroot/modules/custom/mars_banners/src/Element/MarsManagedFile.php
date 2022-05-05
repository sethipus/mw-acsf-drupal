<?php

namespace Drupal\mars_banners\Element;

use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\file\Element\ManagedFile;
use Drupal\Core\Template\Attribute;

/**
 * Provides override AJAX/progress widget for uploading and saving a file.
 *
 * @FormElement("managed_file")
 */
class MarsManagedFile extends ManagedFile {

  /**
   * Render API callback: Expands the managed_file element type.
   *
   * Expands the file type to include Upload and Remove buttons, as well as
   * support for a default value.
   */
  public static function processManagedFile(&$element, FormStateInterface $form_state, &$complete_form) {

    // This is used sometimes so let's implode it just once.
    $parents_prefix = implode('_', $element['#parents']);

    $fids = $element['#value']['fids'] ?? [];

    // Set some default element properties.
    $element['#progress_indicator'] = empty($element['#progress_indicator']) ? 'none' : $element['#progress_indicator'];
    $element['#files'] = !empty($fids) ? File::loadMultiple($fids) : [];
    $element['#tree'] = TRUE;

    // Generate a unique wrapper HTML ID.
    $ajax_wrapper_id = Html::getUniqueId('ajax-wrapper');

    $ajax_settings = [
      'callback' => [get_called_class(), 'uploadAjaxCallback'],
      'options' => [
        'query' => [
          'element_parents' => implode('/', $element['#array_parents']),
        ],
      ],
      'wrapper' => $ajax_wrapper_id,
      'effect' => 'fade',
      'progress' => [
        'type' => $element['#progress_indicator'],
        'message' => $element['#progress_message'],
      ],
    ];

    // Set up the buttons first since we need to check if they were clicked.
    $element['upload_button'] = [
      '#name' => $parents_prefix . '_upload_button',
      '#type' => 'submit',
      '#value' => t('Upload'),
      '#attributes' => ['class' => ['js-hide']],
      '#validate' => [],
      '#submit' => ['file_managed_file_submit'],
      '#limit_validation_errors' => [$element['#parents']],
      '#ajax' => $ajax_settings,
      '#weight' => -5,
    ];

    // Force the progress indicator for the remove button to be either 'none' or
    // 'throbber', even if the upload button is using something else.
    $ajax_settings['progress']['type'] = ($element['#progress_indicator'] == 'none') ? 'none' : 'throbber';
    $ajax_settings['progress']['message'] = NULL;
    $ajax_settings['effect'] = 'none';
    $element['remove_button'] = [
      '#name' => $parents_prefix . '_remove_button',
      '#type' => 'submit',
      '#value' => $element['#multiple'] ? t('Remove selected') : t('Remove'),
      '#validate' => [],
      '#submit' => ['file_managed_file_submit'],
      '#limit_validation_errors' => [$element['#parents']],
      '#ajax' => $ajax_settings,
      '#weight' => 1,
    ];

    $element['fids'] = [
      '#type' => 'hidden',
      '#value' => $fids,
    ];

    // Add progress bar support to the upload if possible.
    if ($element['#progress_indicator'] == 'bar' && $implementation = file_progress_implementation()) {
      $upload_progress_key = mt_rand();

      if ($implementation == 'uploadprogress') {
        $element['UPLOAD_IDENTIFIER'] = [
          '#type' => 'hidden',
          '#value' => $upload_progress_key,
          '#attributes' => ['class' => ['file-progress']],
          // Uploadprogress extension requires this field to be at the top of
          // the form.
          '#weight' => -20,
        ];
      }

      // Add the upload progress callback.
      $element['upload_button']['#ajax']['progress']['url'] = Url::fromRoute('file.ajax_progress', ['key' => $upload_progress_key]);

      // Set a custom submit event so we can modify the upload progress
      // identifier element before the form gets submitted.
      $element['upload_button']['#ajax']['event'] = 'fileUpload';
    }

    // Use a manually generated ID for the file upload field so the desired
    // field label can be associated with it below. Use the same method for
    // setting the ID that the form API autogenerator does.
    // @see \Drupal\Core\Form\FormBuilder::doBuildForm()
    $id = Html::getUniqueId('edit-' . implode('-', array_merge($element['#parents'], ['upload'])));

    // The file upload field itself.
    $element['upload'] = [
      '#name' => 'files[' . $parents_prefix . ']',
      '#type' => 'file',
      // This #title will not actually be used as the upload field's HTML label,
      // since the theme function for upload fields never passes the element
      // through theme('form_element'). Instead the parent element's #title is
      // used as the label (see below). That is usually a more meaningful label
      // anyway.
      '#title' => t('Choose a file'),
      '#title_display' => 'invisible',
      '#id' => $id,
      '#size' => $element['#size'],
      '#multiple' => $element['#multiple'],
      '#theme_wrappers' => [],
      '#weight' => -10,
      '#error_no_message' => TRUE,
    ];
    if (!empty($element['#accept'])) {
      $element['upload']['#attributes'] = ['accept' => $element['#accept']];
    }

    // Indicate that $element['#title'] should be used as the HTML label for the
    // file upload field.
    $element['#label_for'] = $element['upload']['#id'];

    if (!empty($fids) && $element['#files']) {
      foreach ($element['#files'] as $delta => $file) {
        $file_link = [
          '#theme' => 'file_link',
          '#file' => $file,
        ];
        if ($element['#multiple']) {
          $element['file_' . $delta]['selected'] = [
            '#type' => 'checkbox',
            '#title' => \Drupal::service('renderer')->renderPlain($file_link),
          ];
        }
        else {
          $element['file_' . $delta]['filename'] = $file_link + ['#weight' => -10];
        }
        // Anonymous users who have uploaded a temporary file need a
        // non-session-based token added so $this->valueCallback() can check
        // that they have permission to use this file on subsequent submissions
        // of the same form (for example, after an Ajax upload or form
        // validation error).
        if ($file->isTemporary() && \Drupal::currentUser()->isAnonymous()) {
          $element['file_' . $delta]['fid_token'] = [
            '#type' => 'hidden',
            '#value' => Crypt::hmacBase64('file-' . $delta, \Drupal::service('private_key')->get() . Settings::getHashSalt()),
          ];
        }
      }
    }

    // Add the extension list to the page as JavaScript settings.
    if (isset($element['#upload_validators']['file_validate_extensions'][0])) {
      $extension_list = implode(',', array_filter(explode(' ', $element['#upload_validators']['file_validate_extensions'][0])));
      $element['upload']['#attached']['drupalSettings']['file']['elements']['#' . $id] = $extension_list;
    }

    // Add states to ajax wrapper so states.js can potentially attach this
    // element as a Dependent.
    $attributes = new Attribute(['id' => $ajax_wrapper_id]);
    if (isset($element['#states']) && !empty($element['#states'])) {
      $attributes->offsetSet('data-drupal-states', json_encode($element['#states']));

      // If we already have a file, we don't show the upload controls.
      if ($fids) {
        // So let field label's 'for' correspond to element with file IDs.
        $element['#id'] = &$element['fids']['#id'];
        // And add states to this element.
        $element['fids']['#states'] = $element['#states'];
      }
      else {
        // Add states to the file form element itself.
        $element['upload']['#states'] = $element['#states'];
      }
    }

    // Prefix and suffix used for Ajax replacement.
    $element['#prefix'] = '<div' . $attributes . '>';
    $element['#suffix'] = '</div>';

    return $element;
  }

}
