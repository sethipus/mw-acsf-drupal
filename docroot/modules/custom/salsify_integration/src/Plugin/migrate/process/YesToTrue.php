<?php

namespace Drupal\salsify_integration\Plugin\migrate\process;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Convert "Yes" value to boolean TRUE.
 *
 * @MigrateProcessPlugin(
 *   id = "yes_to_true"
 * )
 */
class YesToTrue extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!empty($value) && strtolower($value) == 'yes') {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }

}
