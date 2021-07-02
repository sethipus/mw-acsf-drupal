<?php

namespace Drupal\salsify_integration\Plugin\migrate_plus\data_parser;

use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Json;

/**
 * Obtain JSON data for migration.
 *
 * @DataParser(
 *   id = "salsify_json",
 *   title = @Translation("JSON")
 * )
 */
class SalsifyJson extends Json {

  /**
   * {@inheritdoc}
   */
  public function currentUrl() {
    $keys = array_keys($this->urls);
    $index = $this->activeUrl ?: array_shift($keys);
    return isset($this->urls[$index]) ? $this->urls[$index] : NULL;
  }

}
