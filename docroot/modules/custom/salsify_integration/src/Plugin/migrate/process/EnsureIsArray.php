<?php

namespace Drupal\salsify_integration\Plugin\migrate\process;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Allow use id keys in migration lookup.
 *
 * @MigrateProcessPlugin(
 *   id = "ensure_is_array"
 * )
 */
class EnsureIsArray extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!is_array($value)) {
      return [$value];
    }
    else {
      return $value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }

}
