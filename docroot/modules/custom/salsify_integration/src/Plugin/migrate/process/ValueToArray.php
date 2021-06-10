<?php

namespace Drupal\salsify_integration\Plugin\migrate\process;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Allow use id keys in migration lookup.
 *
 * @MigrateProcessPlugin(
 *   id = "value_to_array"
 * )
 */
class ValueToArray extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!empty($this->configuration['key'])) {
      return [$this->configuration['key'] => $value];
    }
    else {
      return [$value];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }

}
