<?php

namespace Drupal\mars_recommendations\Plugin\DynamicRecommendationsStrategy;

use Drupal\mars_recommendations\DynamicRecommendationsStrategyPluginBase;

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

    if ($node->getType() !== 'product') {
      return $this->getFallbackPlugin()->generate();
    }

    // TODO: Replace 4 with default config value.
    $limit = $this->configuration['limit'] ?? 4;
    $nodes = [];

    foreach (['field_product_flavor', 'field_product_format'] as $fieldname) {
      $entity_ids = array_map(function ($value) {
        return $value->id();
      }, $node->{$fieldname}->referencedEntities());

      if ($entity_ids) {
        $query = $this->nodeStorage->getQuery();
        $query->condition('type', 'product');
        $query->condition($fieldname . '.target_id', $entity_ids ?: [], 'IN');
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
    }

    return count($nodes) >= $limit ? $this->nodeStorage->loadMultiple($nodes) : $this->getFallbackPlugin()->generate();
  }

}
