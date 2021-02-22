<?php

namespace Drupal\mars_common\Twig;

use Drupal\mars_common\Form\MarsCardColorSettingsForm;

/**
 * Twig extension to generate BG Colors for Card grids and sticky PDP hero.
 */
class BgColorClassMapper extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('bg_color_class_map', [$this, 'getBgColorClasses']),
    ];
  }

  /**
   * Gets a unique identifier for this Twig extension.
   */
  public function getName() {
    return 'mars_common.bg_color_class_map.twig_extension';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('uuid', [$this, 'getUuid']),
    ];
  }

  /**
   * Returns a unique id.
   *
   * @return string
   *   A unique id.
   */
  public static function getUuid() {
    return uniqid();
  }

  /**
   * Provides an array of bg color classes based on the given color identifier.
   *
   * @param string $color_identifier
   *   The color id from the configuration.
   *
   * @return array
   *   Returns an array of bg color classes.
   */
  public static function getBgColorClasses(string $color_identifier) {
    if ($color_identifier != 'default' && !empty($color_identifier) &&
      array_key_exists($color_identifier, MarsCardColorSettingsForm::$colorVariables)
    ) {
      $color_letter = explode('_', $color_identifier)[1];
      $color_letter = strtoupper($color_letter);
      return ['bg-color-' . $color_letter, 'bg-gradient-' . $color_letter];
    }
    return [];
  }

}
