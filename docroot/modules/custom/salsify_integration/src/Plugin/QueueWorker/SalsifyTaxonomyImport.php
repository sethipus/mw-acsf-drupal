<?php

namespace Drupal\salsify_integration\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\salsify_integration\SalsifyImportTaxonomyTerm;

/**
 * Provides functionality for the SalsifyTaxonomyImport Queue.
 *
 * @QueueWorker(
 *   id = "salsify_integration_taxonomy_import",
 *   title = @Translation("Salsify: Taxonomy Import"),
 *   cron = {"time" = 10}
 * )
 */
class SalsifyTaxonomyImport extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Create a new SalsifyImportTaxnomyTerm object and pass the Salsify data
    // through.
    $salsify_import = SalsifyImportTaxonomyTerm::create(\Drupal::getContainer());
    $salsify_import->processSalsifyTaxonomyTermItems($data['field_mapping'], $data['salisfy_ids'], $data['salsify_field_data']);
  }

}
