<?php

namespace Drupal\mars_recommendations\Plugin\DynamicRecommendationsStrategy;

use Drupal\mars_recommendations\DynamicRecommendationsStrategyPluginBase;

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
    $query->sort('created', 'DESC');
    $query->range(0, 4);
    $result = $query->execute();

    return $result ? $this->nodeStorage->loadMultiple($result) : [];
  }

}
