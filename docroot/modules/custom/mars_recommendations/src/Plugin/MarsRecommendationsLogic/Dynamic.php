<?php

namespace Drupal\mars_recommendations\Plugin\MarsRecommendationsLogic;

use Drupal\mars_recommendations\RecommendationsLogicPluginBase;

/**
 * Dynamic Recommendations Population Logic plugin.
 *
 * @MarsRecommendationsLogic(
 *   id = "dynamic",
 *   label = @Translation("Dynamic"),
 *   description = @Translation("Loads recommendations dynamically from context."),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   }
 * )
 */
class Dynamic extends RecommendationsLogicPluginBase {

  const DEFAULT_RESULTS_LIMIT = 4;

  /**
   * {@inheritdoc}
   */
  public function getResultsLimit(): int {
    return self::DEFAULT_RESULTS_LIMIT;
  }

  /**
   * {@inheritdoc}
   */
  public function getRecommendations() {
    return [];
  }

}
