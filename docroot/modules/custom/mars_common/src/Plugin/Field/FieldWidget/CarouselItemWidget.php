<?php

namespace Drupal\mars_common\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Plugin\Field\FieldWidget\FileWidget;

/**
 * Plugin implementation of the 'carousel item default' widget.
 *
 * @FieldWidget(
 *   id = "carousel_item_default_widget",
 *   label = @Translation("Carousel widget"),
 *   field_types = {
 *     "carousel_item"
 *   }
 * )
 */
class CarouselItemWidget extends FileWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'progress_indicator' => 'throbber',
    ] + parent::defaultSettings();
  }

  /**
   * Overrides FileWidget::formMultipleElements().
   *
   * Special handling for draggable multiple widgets and 'add more' button.
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);

    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    $file_upload_help = [
      '#theme' => 'file_upload_help',
      '#description' => '',
      '#upload_validators' => $elements[0]['#upload_validators'],
      '#cardinality' => $cardinality,
    ];
    if ($cardinality == 1) {
      // If there's only one field, return it as delta 0.
      if (empty($elements[0]['#default_value']['fids'])) {
        $file_upload_help['#description'] = $this->getFilteredDescription();
        $elements[0]['#description'] = \Drupal::service('renderer')->renderPlain($file_upload_help);
      }
    }
    else {
      $elements['#file_upload_description'] = $file_upload_help;
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $field_settings = $this->getFieldSettings();

    // Add extension validators.
    $element['#upload_validators']['file_validate_extensions'][0] = $field_settings['file_extensions'];

    $element['#desc_field'] = $field_settings['desc_field'];
    $element['#desc_field_required'] = $field_settings['desc_field_required'];

    return $element;
  }

  /**
   * Form API callback: Processes a image_image field element.
   *
   * This method is assigned as a #process callback in formElement() method.
   */
  public static function process($element, FormStateInterface $form_state, $form) {
    $item = $element['#value'];
    $item['fids'] = $element['fids']['#value'];

    $element['#theme'] = 'carousel_item_widget';

    // Add the additional desc fields.
    $element['desc'] = [
      '#title' => t('Description of the item'),
      '#type' => 'textfield',
      '#default_value' => isset($item['desc']) ? $item['desc'] : '',
      '#description' => t('Short description of the item.'),
      '#maxlength' => 65,
      '#weight' => -12,
      '#access' => (bool) $item['fids'] && $element['#desc_field'],
      '#required' => $element['#desc_field_required'],
      '#element_validate' => $element['#desc_field_required'] == 1 ? [[get_called_class(), 'validateRequiredFields']] : [],
    ];

    return parent::process($element, $form_state, $form);
  }

  /**
   * Validate callback for desc field, if the user wants them required.
   *
   * This is separated in a validate function instead of a #required flag to
   * avoid being validated on the process callback.
   */
  public static function validateRequiredFields($element, FormStateInterface $form_state) {
    // Only do validation if the function is triggered from other places than
    // the image process form.
    $triggering_element = $form_state->getTriggeringElement();
    if (!empty($triggering_element['#submit']) && in_array('file_managed_file_submit', $triggering_element['#submit'], TRUE)) {
      $form_state->setLimitValidationErrors([]);
    }
  }

}
