<?php

namespace Drupal\mars_common\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'list item default' widget.
 *
 * @FieldWidget(
 *   id = "list_item_default_widget",
 *   label = @Translation("List widget"),
 *   field_types = {
 *     "list_item"
 *   }
 * )
 */
class ListItemWidget extends FileTextWidgetBase {

  /**
   * Form API callback: Processes a list item field element.
   *
   * This method is assigned as a #process callback in formElement() method.
   */
  public static function process($element, FormStateInterface $form_state, $form) {
    $item = $element['#value'];
    $item['fids'] = $element['fids']['#value'];

    $element['#theme'] = 'file_text_item_widget';

    // Add the additional desc fields.
    $element['desc'] = [
      '#title' => t('Description of the item'),
      '#type' => 'textfield',
      '#default_value' => $item['desc'] ?? '',
      '#description' => t('Short description of the item.'),
      '#maxlength' => 65,
      '#weight' => -12,
      '#access' => (bool) $element['#desc_field'],
      '#required' => $element['#desc_field_required'],
      '#element_validate' => $element['#desc_field_required'] == 1 ? [
        [get_called_class(), 'validateRequiredFields'],
        [get_called_class(), 'validateDescriptionField'],
      ] : [],
    ];

    return parent::process($element, $form_state, $form);
  }

}
