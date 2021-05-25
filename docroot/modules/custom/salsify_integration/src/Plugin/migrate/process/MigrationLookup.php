<?php

namespace Drupal\salsify_integration\Plugin\migrate\process;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\migrate\process\MigrationLookup as MigrationLookupBase;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Row;

/**
 * Allow use id keys in migration lookup.
 *
 * @MigrateProcessPlugin(
 *   id = "migration_lookup_ids"
 * )
 */
class MigrationLookup extends MigrationLookupBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $lookup_migration_ids = (array) $this->configuration['migration'];
    $self = FALSE;
    $destination_ids = NULL;
    $source_id_values = [];
    foreach ($lookup_migration_ids as $lookup_migration_id) {
      if ($lookup_migration_id == $this->migration->id()) {
        $self = TRUE;
      }
      if (isset($this->configuration['source_ids'][$lookup_migration_id])) {
        $value = [];
        $source_map = $this->configuration['source_ids'][$lookup_migration_id];
        foreach ($source_map as $key => $source) {
          $array_lookup_migration_ids = array_values($row->getMultiple($this->configuration['source_ids'][$lookup_migration_id]));
          $value[$key] = reset($array_lookup_migration_ids);
        }
      }
      if (!is_array($value)) {
        $value = [$value];
      }
      $this->skipInvalid($value);
      $source_id_values[$lookup_migration_id] = $value;

      // Re-throw any PluginException as a MigrateException so the executable
      // can shut down the migration.
      try {
        $destination_id_array = $this->migrateLookup->lookup($lookup_migration_id, $value);
      }
      catch (PluginNotFoundException $e) {
        $destination_id_array = [];
      }
      catch (MigrateException $e) {
        throw $e;
      }
      catch (\Exception $e) {
        throw new MigrateException(sprintf('A %s was thrown while processing this migration lookup', gettype($e)), $e->getCode(), $e);
      }

      if ($destination_id_array) {
        $destination_ids = [];
        foreach ($destination_id_array as $item) {
          $array_item = array_values($item);
          $destination_ids[] = reset($array_item);
        }
        break;
      }
    }

    if (!$destination_ids && !empty($this->configuration['no_stub'])) {
      return NULL;
    }

    if (!$destination_ids && ($self || isset($this->configuration['stub_id']) || count($lookup_migration_ids) == 1)) {
      // If the lookup didn't succeed, figure out which migration will do the
      // stubbing.
      if ($self) {
        $stub_migration = $this->migration->id();
      }
      elseif (isset($this->configuration['stub_id'])) {
        $stub_migration = $this->configuration['stub_id'];
      }
      else {
        $stub_migration = reset($lookup_migration_ids);
      }
      // Rethrow any exception as a MigrateException so the executable can shut
      // down the migration.
      try {
        $destination_ids = $this->migrateStub->createStub($stub_migration, $source_id_values[$stub_migration], [], FALSE);
      }
      catch (\LogicException $e) {
        // For BC reasons, we must allow attempting to stub a derived migration.
      }
      catch (PluginNotFoundException $e) {
        // For BC reasons, we must allow attempting to stub a non-existent
        // migration.
      }
      catch (MigrateException $e) {
        throw $e;
      }
      catch (MigrateSkipRowException $e) {
        throw $e;
      }
      catch (\Exception $e) {
        throw new MigrateException(sprintf('A(n) %s was thrown while attempting to stub.', gettype($e)), $e->getCode(), $e);
      }
    }
    if ($destination_ids) {
      if (count($destination_ids) == 1) {
        return reset($destination_ids);
      }
      else {
        return $destination_ids;
      }
    }
  }

}
