<?php

namespace Drupal\mars_recommendations\Plugin\MarsRecommendationsLogic;

use Drupal\mars_recommendations\RecommendationsLogicPluginBase;

/**
 * Dynamic Recommendations Population Logic plugin.
 *
 * @MarsRecommendationsLogic(
 *   id = "manual",
 *   label = @Translation("Manual"),
 *   description = @Translation("Allows to set a list of recommended nodes manually.")
 * )
 */
class Manual extends RecommendationsLogicPluginBase {

  const DEFAULT_RESULTS_LIMIT = 16;

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
