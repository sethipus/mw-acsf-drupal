<?php

namespace Drupal\mars_common\Traits;

use Drupal\Core\Block\BlockPluginInterface;

/**
 * Provide form build helper SelectBackgroundColorTrait.
 */
trait SelectBackgroundColorTrait {

  /**
   * Color variables.
   *
   * @var array
   */
  protected $colorVariables = [
    'color_c' => '--c-subcolor-1',
    'color_d' => '--c-subcolor-2',
    'color_e' => '--c-subcolor-3',
  ];

  /**
   * Get theme configurator parser.
   *
   * @return \Drupal\mars_common\ThemeConfiguratorParser
   *   Theme configurator parser.
   */
  protected function getThemeConfiguratorParser() {
    if (!isset($this->themeConfiguratorParser)) {
      $this->themeConfiguratorParser = \Drupal::service('mars_common.theme_configurator_parser');
    }
    return $this->themeConfiguratorParser;
  }

  /**
   * Build select background.
   */
  protected function buildSelectBackground(&$form) {
    if ($this instanceof BlockPluginInterface) {
      $color_c = $this->getThemeConfiguratorParser()->getSettingValue('color_c');
      $color_d = $this->getThemeConfiguratorParser()->getSettingValue('color_d');
      $color_e = $this->getThemeConfiguratorParser()->getSettingValue('color_e');

      $form['select_background_color'] = [
        '#type' => 'select',
        '#title' => $this->t('Select background color'),
        '#options' => [
          'default' => $this->t('Default'),
          'color_c' => 'Color C - #' . $color_c,
          'color_d' => 'Color D - #' . $color_d,
          'color_e' => 'Color E - #' . $color_e,
        ],
        '#default_value' => $this->configuration['select_background_color'] ?? '',
      ];
    }
  }

}
