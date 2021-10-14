<?php

namespace Drupal\salsify_integration\Plugin\migrate\process;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Postprocessing logic for all multipack related products.
 *
 * @MigrateProcessPlugin(
 *   id = "multipack_product_postprocess"
 * )
 */
class MultipackProductPostprocess extends PluginBase implements ContainerFactoryPluginInterface {

  /**
   * The Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a MultipackProductPostprocess object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   *   The Migration the plugin is being used in.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity Type manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MigrationInterface $migration,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migration = $migration;

    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MigrationInterface $migration = NULL
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity_type.manager')
    );
  }

  /**
   * Postprocessing for the child product.
   *
   * @param mixed $value
   *   The input value.
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migration in which this process is being executed.
   * @param \Drupal\migrate\Row $row
   *   The row from the source to process.
   * @param string $destination_property
   *   The destination property currently worked on. This is only used together
   *   with the $row above.
   *
   * @return mixed
   *   The input value, $value, if it is not empty.
   *
   * @throws \Drupal\migrate\MigrateSkipProcessException
   *   Thrown if there is no child product entity.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    try {
      if (isset($this->configuration['hide_product']) &&
        $this->configuration['hide_product']
        && isset($value['target_id'])) {

        $product = $this->entityTypeManager
          ->getStorage('node')
          ->load($value['target_id']);

        /** @var \Drupal\node\NodeInterface $product */
        $product->set('rh_action', 'page_not_found');
        $product->set('field_product_generated', TRUE);

        $mod_state = $this->configuration['moderation_state'] ?? 'published';
        $product->set('moderation_state', $mod_state);
        $product->save();
      }
    }
    catch (\Exception $e) {
      throw new MigrateSkipProcessException();
    }

    return $value;
  }

}
