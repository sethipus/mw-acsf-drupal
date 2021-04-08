<?php

namespace Drupal\mars_search\Plugin\search_api\processor;

use Drupal\search_api\Plugin\search_api\processor\Highlight;

/**
 * Adds a highlighted excerpt to results and highlights returned fields.
 *
 * This processor won't run for queries with the "basic" processing level set.
 *
 * @SearchApiProcessor(
 *   id = "mars_highlight",
 *   label = @Translation("Mars Highlight"),
 *   description = @Translation("Adds a highlighted excerpt to results and highlights returned fields. Overrides default <b>Highlight</b> processor logic."),
 *   stages = {
 *     "pre_index_save" = 0,
 *     "postprocess_query" = 0,
 *   }
 * )
 */
class MarsHighlight extends Highlight {

  /**
   * Marks occurrences of the search keywords in a text field.
   *
   * @param string $text
   *   The text of the field.
   * @param array $keys
   *   The search keywords entered by the user.
   * @param bool $html
   *   (optional) Whether the text can contain HTML tags or not. In the former
   *   case, text inside tags (that is, tag names and attributes) won't be
   *   highlighted.
   *
   * @return string
   *   The given text with all occurrences of search keywords highlighted.
   */
  protected function highlightField($text, array $keys, $html = TRUE) {
    if ($html) {
      $texts = preg_split('#((?:</?[[:alpha:]](?:[^>"\']*|"[^"]*"|\'[^\']\')*>)+)#i', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
      if ($texts === FALSE) {
        $args = [
          '%error_num' => preg_last_error(),
        ];
        $this->getLogger()->warning('A PCRE error (#%error_num) occurred during results highlighting.', $args);
        return $text;
      }
      $textsCount = count($texts);
      for ($i = 0; $i < $textsCount; $i += 2) {
        $texts[$i] = $this->highlightField($texts[$i], $keys, FALSE);
      }
      return implode('', $texts);
    }
    $keys = implode('|', array_map('preg_quote', $keys, array_fill(0, count($keys), '/')));
    // If "Highlight partial matches" is disabled, we only want to highlight
    // matches that are complete words. Otherwise, we want all of them.
    $boundary = !$this->configuration['highlight_partial'] ? static::$boundary : '';
    $regex = '/' . $boundary . '(?:' . $keys . ')' . $boundary . '/iu';
    $replace = $this->configuration['prefix'] . '\0' . $this->configuration['suffix'];
    $text = preg_replace($regex, $replace, ' ' . $text . ' ');
    return rtrim($text);
  }

}
