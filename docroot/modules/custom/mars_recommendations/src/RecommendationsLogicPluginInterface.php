<?php

namespace Drupal\mars_recommendations;

/**
 * An interface for Recommendations Population Logic plugins.
 */
interface RecommendationsLogicPluginInterface {

  /**
   * Do not limit recommendations result quantity.
   *
   * @var int
   */
  const UNLIMITED = -1;

  /**
   * Returns results limit for Recommendations Population Logic plugin.
   *
   * @return int
   *   Results limit.
   */
  public function getResultsLimit(): int;

  /**
   * Loads list of recommended nodes from the context.
   *
   * @return \Drupal\node\Entity\Node[]
   *   List of recommended nodes.
   */
  public function getRecommendations();

}
