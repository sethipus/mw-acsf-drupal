<?php

namespace Drupal\salsify_integration;

use Drupal\migrate_tools\MigrateExecutable as MigrateExecutableBase;

/**
 * Class MigrateExecutable.
 *
 * Overrides original class to allow to get migration source context
 * in migration process plugins.
 *
 * @package Drupal\salsify_integration
 */
class MigrateExecutable extends MigrateExecutableBase {

  /**
   * {@inheritdoc}
   *
   * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
   */
  public function getSource() {
    return parent::getSource();
  }

}
