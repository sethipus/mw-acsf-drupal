<?php

namespace Drupal\salsify_integration;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;

/**
 * Class SalsifyProductRepository is responsible for manipulation with entities.
 *
 * @package Drupal\salsify_integration
 */
class SalsifyProductRepository {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * SalsifyProductRepository constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Update parent entities, if it was created earlier.
   *
   * @param array $product_data
   *   Product data.
   * @param \Drupal\Core\Entity\EntityInterface $child_entity
   *   Child entity (product or product variant).
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateParentEntities(array $product_data, EntityInterface $child_entity) {
    if (isset($product_data['Parent GTIN']) &&
      (ProductHelper::isProduct($product_data) || ProductHelper::isProductVariant($product_data))) {

      $parent_gtin = (array) $product_data['Parent GTIN'];
      $parent_field = (ProductHelper::isProduct($product_data)) ? 'field_product_pack_items' : 'field_product_variants';

      $entity_query = $this->entityTypeManager->getStorage('node')
        ->getQuery();
      $parent_entity_ids = $entity_query->condition(
        'salsify_id',
        $parent_gtin,
        'IN'
        )
        ->execute();

      $parent_entities = $this->entityTypeManager
        ->getStorage('node')
        ->loadMultiple($parent_entity_ids);

      foreach ($parent_entities as $parent_entity) {
        /** @var \Drupal\node\Entity\Node $parent_entity */
        if ($parent_entity->hasField($parent_field) &&
          !$this->haveChildValue($parent_entity, $parent_field, $child_entity->id())) {

          $entiry_ref_field = $parent_entity->get($parent_field)->getValue();
          $entiry_ref_field[] = ['target_id' => $child_entity->id()];
          $parent_entity->set($parent_field, $entiry_ref_field);
          $parent_entity->save();
        }

      }

    }
  }

  /**
   * Unpublish deleted at salsify side products.
   *
   * @param array $products
   *   Products array.
   *
   * @return array|int
   *   Ids deleted entities.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function unpublishProducts(array $products) {
    $products_for_delete = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->condition(
        'type',
        ['product', 'product_variant', 'product_multipack'],
        'IN'
      )
      ->condition('salsify_id', $products, 'NOT IN')
      ->execute();

    $product_entities_delete = $this->entityTypeManager
      ->getStorage('node')
      ->loadMultiple($products_for_delete);

    $deleted_gtins = [];
    foreach ($product_entities_delete as $product) {
      /** @var \Drupal\node\NodeInterface $product */
      $deleted_gtins[] = $product->get('salsify_id')->value;
    }

    $this->entityTypeManager
      ->getStorage('node')
      ->delete($product_entities_delete);

    return $deleted_gtins;
  }

  /**
   * Does entity field has value or not (entity ref type).
   *
   * @param \Drupal\node\Entity\Node $parent_entity
   *   Entity.
   * @param string $field_name
   *   Field name.
   * @param mixed $value
   *   Value.
   *
   * @return bool
   *   Result of check.
   */
  private function haveChildValue(Node $parent_entity, $field_name, $value) {
    $has_value = FALSE;

    /** @var \Drupal\node\Entity\Node $parent_entity */
    $entiry_ref_field = $parent_entity->get($field_name)->getValue();
    if (is_array($entiry_ref_field)) {
      foreach ($entiry_ref_field as $entiry_ref_field_value) {
        if ($entiry_ref_field_value['target_id'] == $value) {
          $has_value = TRUE;
        }
      }
    }

    return $has_value;
  }

}
