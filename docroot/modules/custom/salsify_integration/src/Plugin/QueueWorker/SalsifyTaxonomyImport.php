<?php

namespace Drupal\salsify_integration\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\salsify_integration\SalsifyImportTaxonomyTerm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides functionality for the SalsifyTaxonomyImport Queue.
 *
 * @QueueWorker(
 *   id = "salsify_integration_taxonomy_import",
 *   title = @Translation("Salsify: Taxonomy Import"),
 *   cron = {"time" = 10}
 * )
 */
class SalsifyTaxonomyImport extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The Salsify import taxonomy service.
   *
   * @var \Drupal\salsify_integration\SalsifyImportTaxonomyTerm
   */
  protected $salsifyImportTaxonomy;

  /**
   * Creates a new SalsifyTaxonomyImport object.
   *
   * @param \Drupal\salsify_integration\SalsifyImportTaxonomyTerm $salsify_import_taxonomy
   *   The Salsify import taxonomy service.
   */
  public function __construct(
    SalsifyImportTaxonomyTerm $salsify_import_taxonomy
  ) {
    $this->salsifyImportTaxonomy = $salsify_import_taxonomy;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('salsify_integration.salsify_import_taxonomy')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Create a new SalsifyImportTaxnomyTerm object and pass the Salsify data
    // through.
    $this->salsifyImportTaxonomy
      ->processSalsifyTaxonomyTermItems(
        $data['field_mapping'],
        $data['salisfy_ids'],
        $data['salsify_field_data']
      );
  }

}
