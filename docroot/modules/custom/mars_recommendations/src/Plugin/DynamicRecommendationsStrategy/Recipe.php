<?php

namespace Drupal\mars_recommendations\Plugin\DynamicRecommendationsStrategy;

use Drupal\mars_recommendations\DynamicRecommendationsStrategyPluginBase;

/**
 * Default Dynamic recommendations strategy plugin implementation.
 *
 * @DynamicRecommendationsStrategy(
 *   id = "recipe",
 *   label = @Translation("Recipe"),
 *   description = @Translation("Recipe Dynamic recommendations strategy that returns nodes with the same Initiatives, Occasions or ones from Related Product."),
 *   fallback_plugin = "default",
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   }
 * )
 */
class Recipe extends DynamicRecommendationsStrategyPluginBase {

  /**
   * {@inheritdoc}
   */
  public function generate() {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getContextValue('node');

    if ($node->getType() !== 'recipe') {
      return $this->getFallbackPlugin()->generate();
    }

    // TODO: Replace 4 with default config value.
    $limit = $this->configuration['limit'] ?? 4;
    $nodes = [];

    $fields = [
      'field_brand_initiatives',
      'field_product_occasions',
      'field_product_reference',
    ];
    foreach ($fields as $fieldname) {
      $entity_ids = array_map(function ($value) {
        return $value->id();
      }, $node->{$fieldname}->referencedEntities());

      if ($entity_ids) {
        $query = $this->nodeStorage->getQuery();
        $query->condition('type', 'recipe');
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
