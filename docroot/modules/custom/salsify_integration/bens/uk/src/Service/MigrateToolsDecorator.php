<?php

namespace Drupal\brand_drupal_salsify_bens_uk\Service;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\migrate\Plugin\MigrationPluginManager;
use Drupal\migrate_tools\Commands\MigrateToolsCommands;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;

/**
 * Class MigrateToolsDecorator - decorator for migrate tools service.
 *
 * @package Drupal\brand_drupal_salsify_bens_uk\Service
 */
class MigrateToolsDecorator extends MigrateToolsCommands {

  use StringTranslationTrait;

  /**
   * Migration plugin manager service.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManager
   */
  protected $migrationPluginManager;

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Key-value store service.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValue;

  /**
   * Migrate message logger.
   *
   * @var \Drupal\migrate_tools\Drush9LogMigrateMessage
   */
  protected $migrateMessage;

  /**
   * MigrateToolsCommands constructor.
   *
   * @param \Drupal\migrate\Plugin\MigrationPluginManager $migrationPluginManager
   *   Migration Plugin Manager service.
   * @param \Drupal\Core\Datetime\DateFormatter $dateFormatter
   *   Date formatter service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $keyValue
   *   Key-value store service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger factory service.
   */
  public function __construct(
    MigrationPluginManager $migrationPluginManager,
    DateFormatter $dateFormatter,
    EntityTypeManagerInterface $entityTypeManager,
    KeyValueFactoryInterface $keyValue,
    LoggerChannelFactoryInterface $loggerChannelFactory
  ) {
    parent::__construct($migrationPluginManager, $dateFormatter, $entityTypeManager, $keyValue);
    $this->migrationPluginManager = $migrationPluginManager;
    $this->dateFormatter = $dateFormatter;
    $this->entityTypeManager = $entityTypeManager;
    $this->keyValue = $keyValue;
    $this->logger = $loggerChannelFactory->get('salsify_channel_approach_migrate');
  }

  /**
   * List all migrations with current status.
   *
   * @param string $migration_names
   *   Restrict to a comma-separated list of migrations (Optional).
   * @param array $options
   *   Additional options for the command.
   *
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   *   Migrations status formatted as table.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Exception
   */
  public function status($migration_names = '', array $options = [
    'group' => self::REQ,
    'tag' => self::REQ,
    'names-only' => FALSE,
    'continue-on-failure' => FALSE,
  ]) {
    $names_only = $options['names-only'];

    $migrations = $this->migrationsList($migration_names, $options);

    $table = [];
    $errors = [];
    // Take it one group at a time, listing the migrations within each group.
    foreach ($migrations as $group_id => $migration_list) {
      /** @var \Drupal\migrate_plus\Entity\MigrationGroup $group */
      $group = $this->entityTypeManager->getStorage('migration_group')->load($group_id);
      $group_name = !empty($group) ? "{$group->label()} ({$group->id()})" : $group_id;

      foreach ($migration_list as $migration_id => $migration) {
        if ($names_only) {
          $table[] = [
            'group' => $this->t('Group: @name', ['@name' => $group_name]),
            'id' => $migration_id,
          ];
        }
        else {
          try {
            $map = $migration->getIdMap();
            $imported = $map->importedCount();
            $source_plugin = $migration->getSourcePlugin();
          }
          catch (\Exception $e) {
            $error = $this->t(
              'Failure retrieving information on @migration: @message',
              ['@migration' => $migration_id, '@message' => $e->getMessage()]
            );
            $this->logger()->error($error);
            $errors[] = $error;
            continue;
          }

          try {
            $source_rows = $source_plugin->count();
            // -1 indicates uncountable sources.
            if ($source_rows == -1) {
              $source_rows = $this->t('N/A');
              $unprocessed = $this->t('N/A');
            }
            else {
              $unprocessed = $source_rows - $map->processedCount();
            }
          }
          catch (\Exception $e) {
            $this->logger()->error(
              $this->t(
                'Could not retrieve source count from @migration: @message',
                ['@migration' => $migration_id, '@message' => $e->getMessage()]
              )
            );
            $source_rows = $this->t('N/A');
            $unprocessed = $this->t('N/A');
          }

          $status = $migration->getStatusLabel();
          $migrate_last_imported_store = $this->keyValue->get(
            'migrate_last_imported'
          );
          $last_imported = $migrate_last_imported_store->get(
            $migration->id(),
            FALSE
          );
          if ($last_imported) {
            $last_imported = $this->dateFormatter->format(
              $last_imported / 1000,
              'custom',
              'Y-m-d H:i:s'
            );
          }
          else {
            $last_imported = '';
          }
          $table[] = [
            'group' => $group_name,
            'id' => $migration_id,
            'status' => $status,
            'total' => $source_rows,
            'imported' => $imported,
            'unprocessed' => $unprocessed,
            'last_imported' => $last_imported,
          ];
        }
      }

      // Add empty row to separate groups, for readability.
      end($migrations);
      if ($group_id !== key($migrations)) {
        $table[] = [];
      }
    }

    // If any errors occurred, throw an exception.
    if (!empty($errors)) {
      throw new \Exception(implode(PHP_EOL, $errors));
    }

    return new RowsOfFields($table);
  }

}
