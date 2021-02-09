<?php

namespace Drupal\mars_search\Plugin\search_api\processor;

use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\IndexInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Remove Product referenced from Product Multipack.
 *
 * @SearchApiProcessor(
 *   id = "product_multipack_reference",
 *   label = @Translation("Product Multipack references"),
 *   description = @Translation("Remove Product referenced from Product Multipack."),
 *   stages = {
 *     "alter_items" = 0,
 *   }
 * )
 */
class ProductMultipack extends ProcessorPluginBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|null
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $processor */
    $processor = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $processor->setEntityTypeManager($container->get('entity_type.manager'));
    return $processor;
  }

  /**
   * Retrieves the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  public function getEntityTypeManager() {
    return $this->entityTypeManager ?: \Drupal::entityTypeManager();
  }

  /**
   * Sets the entity type manager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The new entity type manager.
   *
   * @return $this
   */
  public function setEntityTypeManager(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function supportsIndex(IndexInterface $index) {
    /** @var \Drupal\search_api\Plugin\search_api\datasource\ContentEntity $datasource */
    foreach ($index->getDatasources() as $datasource) {
      $entityTypeId = $datasource->getEntityTypeId();
      $entityBundle = $datasource->getEntityTypeBundleInfo()->getBundleInfo($entityTypeId);
      if (!$entityTypeId && $entityTypeId !== 'node') {
        continue;
      }
      if (array_key_exists('product', $entityBundle)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function alterIndexedItems(array &$items) {
    /** @var \Drupal\search_api\Item\ItemInterface $item */
    foreach ($items as $item_id => $item) {
      $node = $item->getOriginalObject()->getEntity();
      /* @var \Drupal\Core\Entity\EntityInterface $node */
      if ($node->bundle() == 'product' &&
        $node->get('field_product_generated')->value) {
        unset($items[$item_id]);
      }
    }
  }

}
