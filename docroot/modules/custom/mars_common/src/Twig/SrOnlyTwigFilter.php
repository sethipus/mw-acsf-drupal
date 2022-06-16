<?php

namespace Drupal\mars_common\Twig;

use Drupal\Component\Utility\UrlHelper;
use Drupal\mars_common\LanguageHelper;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

/**
 * Class SrOnlyTwigFilter - sr twig filter logic.
 *
 * @package Drupal\mars_common\Twig
 */
class SrOnlyTwigFilter extends AbstractExtension {

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
      new TwigFilter(
        'srOnlyTwigForTextarea',
        [$this, 'srOnlyTwigPreprocessForTextarea']
      ),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'mars_common.screen_reader_twig_filters';
  }

  /**
   * Filter add span for screen reader.
   *
   * @param string $string
   *   A string containing the English text to translate.
   *
   * @return string|null
   *   An object that, when cast to a string, returns the translated string.
   */
  public function srOnlyTwigPreprocessForTextarea($string): ?string {
    $regexp = "<a(.*)href=\"(.*)\"(.*)>(.*)<\/a>";
    preg_match_all("/$regexp/siU", $string, $links, PREG_SET_ORDER);
    if (!empty($links)) {
      foreach ($links as $link) {
        // Full link tag.
        $full_link = $link[0];
        // Link url.
        $url = $link[2];
        // Link text.
        $link_text = $link[4];
        $link_has_target_blank = FALSE;
        if (strpos($link[1], '_blank') !== FALSE ||
          strpos($link[3], '_blank') !== FALSE
        ) {
          $link_has_target_blank = TRUE;
        }
        if (!empty($link_text) &&
          (UrlHelper::isExternal($url) || $link_has_target_blank)
        ) {
          $sr_text_link = $link_text . ' <span class="sronly">' . $this->languageHelper->translate('(opens in new window)') . '</span>';
          $result = str_replace($link_text, $sr_text_link, $full_link);
          $string = str_replace($full_link, $result, $string);
        }
      }
    }
    return $string;
  }

}
