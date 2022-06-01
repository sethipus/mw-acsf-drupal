<?php

namespace Drupal\salsify_integration;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationPluginManager;

/**
 * Migration runner.
 */
class MigrationRunner {

  /**
   * Migration plugin manager.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManager
   */
  protected $migrationPluginManager;

  /**
   * Configuration.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor.
   */
  public function __construct(
    MigrationPluginManager $migrationPluginManager,
    ConfigFactoryInterface $configFactory
  ) {
    $this->migrationPluginManager = $migrationPluginManager;
    $this->configFactory = $configFactory;
  }

  /**
   * Run the migrations.
   */
  public function runProductMigration() {
    // Reset migration status.
    $this->truncateMigrateTables();

    $migrationIds = $this->configFactory->get('salsify_integration.migrate_settings')->get('migration_ids');
    if (is_array($migrationIds)) {
      $this->runMigrations($migrationIds);
    }
  }

  /**
   * Run list of migrations.
   */
  protected function runMigrations(array $migrationIds) {
    asort($migrationIds);
    foreach ($migrationIds as $migrationId) {
      if ($this->migrationPluginManager->hasDefinition($migrationId)) {
        /** @var \Drupal\migrate\Plugin\Migration $migration */
        $migration = $this->migrationPluginManager->createInstance($migrationId);

        foreach ($migration->getMigrationDependencies() as $dependencies) {
          $this->runMigrations($dependencies);
        }
        // Set migration 'sync' flag to remove outdated entities on import.
        $migration->set('syncSource', TRUE);
        // Run the migration.
        $executable = new MigrateExecutable($migration, new MigrateMessage());
        $executable->import();
      }
    }
  }

  /**
   * Truncate Migrate Tables before running migration import.
   */
  protected function truncateMigrateTables() {
    $migrationIds = $this->migrationPluginManager->getDefinitions();
    $connection = \Drupal::database();
    foreach ($migrationIds as $migrationId) {
      $table = 'migrate_map_' . $migrationId['id'];
      $tableExists = $connection->schema()->tableExists($table);
      if ($tableExists) {
        $connection->truncate('migrate_map_' . $migrationId['id'])->execute();
        $connection->truncate('migrate_message_' . $migrationId['id'])->execute();
      }
    }
  }

}
