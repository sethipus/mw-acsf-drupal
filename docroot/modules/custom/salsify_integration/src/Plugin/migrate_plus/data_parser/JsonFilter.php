<?php

namespace Drupal\salsify_integration\Plugin\migrate_plus\data_parser;

/**
 * Obtain JSON column for migration.
 *
 * @code
 * source:
 *   data_parser_plugin: json_filter
 *   filter: callable
 * @endcode
 *
 * @DataParser(
 *   id = "json_filter",
 *   title = @Translation("JSON filter")
 * )
 */
class JsonFilter extends SalsifyJson {

  /**
   * {@inheritdoc}
   */
  protected function getSourceData($url) {
    $array = parent::getSourceData($url);

    if (is_callable($this->configuration['filter'])) {
      $sourceData = array_filter($array, $this->configuration['filter']);
    }
    else {
      $sourceData = $array;
    }

    return $sourceData;
  }

}
