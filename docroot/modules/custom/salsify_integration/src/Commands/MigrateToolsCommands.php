<?php

namespace Drupal\salsify_integration\Commands;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_tools\Commands\MigrateToolsCommands as MigrateToolsCommandsBase;
use Drupal\salsify_integration\MigrateExecutable;
use Drupal\migrate_tools\MigrateTools;
// @codingStandardsIgnoreLine
use Drupal\migrate_tools\Commands;

/**
 * Class MigrateToolsCommands.
 *
 * Overrides executeMigration() method from the original class to allow drush
 * commands to get migration source context from a new MigrateExecutable class.
 *
 * @package Drupal\salsify_integration\Commands
 */
class MigrateToolsCommands extends MigrateToolsCommandsBase {

  /**
   * {@inheritdoc}
   */
  protected function executeMigration(MigrationInterface $migration, $migration_id, array $options = []) {
    // Keep track of all migrations run during this command so the same
    // migration is not run multiple times.
    static $executed_migrations = [];

    if ($options['execute-dependencies']) {
      $definition = $migration->getPluginDefinition();
      $required_migrations = $definition['requirements'] ?? [];
      $required_migrations = array_filter($required_migrations, function ($value) use ($executed_migrations) {
        return !isset($executed_migrations[$value]);
      });

      if (!empty($required_migrations)) {
        $manager = $this->migrationPluginManager;
        $required_migrations = $manager->createInstances($required_migrations);
        $dependency_options = array_merge($options, ['is_dependency' => TRUE]);
        array_walk($required_migrations, [$this, __FUNCTION__], $dependency_options);
        $executed_migrations += $required_migrations;
      }
    }
    if ($options['sync']) {
      $migration->set('syncSource', TRUE);
    }
    if ($options['skip-progress-bar']) {
      $migration->set('skipProgressBar', TRUE);
    }
    if ($options['continue-on-failure']) {
      $migration->set('continueOnFailure', TRUE);
    }
    if ($options['force']) {
      $migration->set('requirements', []);
    }
    if ($options['update']) {
      if (!$options['idlist']) {
        $migration->getIdMap()->prepareUpdate();
      }
      else {
        $source_id_values_list = MigrateTools::buildIdList($options);
        $keys = array_keys($migration->getSourcePlugin()->getIds());
        foreach ($source_id_values_list as $source_id_values) {
          $migration->getIdMap()->setUpdate(array_combine($keys, $source_id_values));
        }
      }
    }

    // Initialize the Synmfony Console progress bar.
    // @codingStandardsIgnoreLine
    \Drupal::service('migrate_tools.migration_drush_command_progress')->initializeProgress(
      $this->output(),
      $migration
    );

    $executable = new MigrateExecutable($migration, $this->getMigrateMessage(), $options);
    // drush_op() provides --simulate support.
    $result = drush_op([$executable, 'import']);
    $executed_migrations += [$migration_id => $migration_id];
    if ($count = $executable->getFailedCount()) {
      $error_message = dt(
        '!name Migration - !count failed.',
        ['!name' => $migration_id, '!count' => $count]
      );
    }
    elseif ($result == MigrationInterface::RESULT_FAILED) {
      $error_message = dt('!name migration failed.', ['!name' => $migration_id]);
    }
    else {
      $error_message = '';
    }
    if ($error_message) {
      if ($options['continue-on-failure']) {
        $this->logger()->error($error_message);
      }
      else {
        // Nudge Drush to use a non-zero exit code.
        throw new \Exception($error_message);
      }
    }
  }

}
