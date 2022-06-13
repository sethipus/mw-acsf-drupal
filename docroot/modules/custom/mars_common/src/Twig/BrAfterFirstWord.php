<?php

namespace Drupal\mars_common\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

/**
 * Class Twig extension for adding BR tag after first word in the given string.
 *
 * @package Drupal\mars_common\Twig
 */
class BrAfterFirstWord extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return [
      new TwigFilter('brAfterFirstWord', [$this, 'addBrTagAfterFirstWord']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'mars_common.add_br_tag_after_first_word';
  }

  /**
   * Filter add br tag after first word.
   *
   * @param string $string
   *   A string containing the English text to translate.
   *
   * @return array|null
   *   An object that, when cast to a string, returns the translated string.
   */
  public function addBrTagAfterFirstWord($string): ?array {
    $string = trim($string);
    if (!empty($string)) {

      if (strpos($string, ' ') !== FALSE) {
        // Replaces any number of spaces (including zero) with a space.
        $string = preg_replace('/ {2,}/', ' ', $string);
        $sentence_array = explode(' ', $string);
        $first_element = array_shift($sentence_array);
        $first_element .= ' <br />';
        array_unshift($sentence_array, $first_element);
        $string = implode(' ', $sentence_array);
      }
      else {
        $string .= ' <br />';
      }
    }
    return ['#markup' => $string];
  }

}
