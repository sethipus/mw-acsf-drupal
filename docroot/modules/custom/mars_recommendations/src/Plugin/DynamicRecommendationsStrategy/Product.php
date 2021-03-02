<?php

namespace Drupal\mars_recommendations\Plugin\DynamicRecommendationsStrategy;

use Drupal\mars_recommendations\DynamicRecommendationsStrategyPluginBase;
use Drupal\node\NodeInterface;

/**
 * Product recommendations strategy plugin implementation.
 *
 * @DynamicRecommendationsStrategy(
 *   id = "product",
 *   label = @Translation("Product"),
 *   description = @Translation("Product Dynamic recommendations strategy that returns nodes with the same Flavor or Format."),
 *   fallback_plugin = "default",
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   }
 * )
 */
class Product extends DynamicRecommendationsStrategyPluginBase {

  /**
   * {@inheritdoc}
   */
  public function generate() {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getContextValue('node');

    if (!in_array($node->getType(), ['product', 'product_multipack'])) {
      return $this->getFallbackPlugin()->generate();
    }

    // TODO: Replace 4 with default config value.
    $limit = $this->configuration['limit'] ?? 4;
    $nodes = [];

    $fields = [
      'field_product_flavor',
      'field_product_format',
    ];
    foreach ($fields as $fieldname) {
      $entity_ids = array_map(function ($value) {
        return $value->id();
      }, $node->{$fieldname}->referencedEntities());

      $query = $this->nodeStorage->getQuery();
      $query->condition('type', ['product', 'product_multipack'], 'IN');

      $product_generated = $query->orConditionGroup()
        ->notExists('field_product_generated')
        ->condition('field_product_generated.value', 1, '!=');

      $query->condition($product_generated);

      $queryFieldName = $fieldname . '.target_id';
      if (!empty($entity_ids)) {
        $query->condition($queryFieldName, $entity_ids, 'IN');
      }
      else {
        $query->notExists($queryFieldName);
      }

      $query->condition('status', NodeInterface::PUBLISHED);
      $query->condition('nid', $node->id(), '<>');
      $query->range(0, $limit - count($nodes));
      $results = $query->execute();

      if ($results) {
        $nodes = array_unique(array_merge($nodes, array_values($results)));
        if (count($nodes) >= $limit) {
          return $this->nodeStorage->loadMultiple($nodes);
        }
      }
    }

    return count($nodes) >= $limit ? $this->nodeStorage->loadMultiple($nodes) : $this->getFallbackPlugin()->generate();
  }

}
