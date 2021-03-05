<?php

namespace Drupal\mars_recommendations\Plugin\DynamicRecommendationsStrategy;

use Drupal\mars_recommendations\DynamicRecommendationsStrategyPluginBase;
use Drupal\node\NodeInterface;

/**
 * Default Dynamic Recommendations strategy plugin implementation.
 *
 * @DynamicRecommendationsStrategy(
 *   id = "default",
 *   label = @Translation("Default"),
 *   description = @Translation("Default Dynamic recommendations strategy that returns products sorted by recency.")
 * )
 */
class DefaultStrategy extends DynamicRecommendationsStrategyPluginBase {

  /**
   * {@inheritdoc}
   */
  public function generate() {
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', ['product', 'product_multipack'], 'IN');
    $query->condition('status', NodeInterface::PUBLISHED);
    $product_generated = $query->orConditionGroup()
      ->notExists('field_product_generated')
      ->condition('field_product_generated.value', 1, '!=');

    $query->condition($product_generated);
    $query->sort('created', 'DESC');
    $query->range(0, 4);
    $result = $query->execute();

    return $result ? $this->nodeStorage->loadMultiple($result) : [];
  }

}
