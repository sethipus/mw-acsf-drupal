<?php

namespace Drupal\mars_common\Twig;

use Drupal\mars_common\LanguageHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class LanguageTwigFilter - new filter for twig (mt).
 *
 * @package Drupal\mars_common\Twig
 */
class LanguageTwigFilter extends AbstractExtension {

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * MediaHelperFilters constructor.
   *
   * @param \Drupal\mars_common\LanguageHelper $language_helper
   *   The language helper service.
   */
  public function __construct(LanguageHelper $language_helper) {
    $this->languageHelper = $language_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return [
      new TwigFilter('mt', [$this, 'translateMarsContext']),
    ];
  }

  /**
   * Filter to translate string with mars context.
   *
   * @param string $string
   *   A string containing the English text to translate.
   * @param array $args
   *   Optional array of arguments.
   * @param array $options
   *   Optional array of options.
   *
   * @return string|null
   *   An object that, when cast to a string, returns the translated string.
   */
  public function translateMarsContext($string, array $args = [], array $options = []): ?string {
    return is_string($string) ? $this->languageHelper->translate($string, $args, $options) : $string;
  }

}
