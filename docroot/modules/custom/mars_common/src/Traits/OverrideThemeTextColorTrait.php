<?php

namespace Drupal\mars_common\Traits;

/**
 * Provide form build helper OverrideThemeTextColorTrait.
 */
trait OverrideThemeTextColorTrait {

  /**
   * The color to override.
   *
   * @var string
   */
  public static $overrideColor = '#FFFFFF';

  /**
   * Builds form element for all block forms.
   *
   * @param array $form
   *   The form elements array.
   * @param array $block_config
   *   The block configuration array.
   * @param bool $filter_titles_override
   *   Indicates if we need to add form element to filter title color override.
   */
  public function buildOverrideColorElement(array &$form, array $block_config, bool $filter_titles_override = FALSE) {
    $form['override_text_color'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Override theme text color'),
    ];

    $form['override_text_color']['override_color'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Override default theme text color configuration with white for the selected component'),
      '#default_value' => $block_config['override_text_color']['override_color'] ?? NULL,
    ];

    if (!empty($filter_titles_override)) {
      $form['override_text_color']['override_filter_title_color'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Override default theme text color configuration with white for filter titles'),
        '#default_value' => $block_config['override_text_color']['override_filter_title_color'] ?? NULL,
      ];
    }

  }

}
