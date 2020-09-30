<?php

namespace Drupal\mars_recommendations;

/**
 * Interface for dynamic_recommendations_strategy plugins.
 */
interface DynamicRecommendationsStrategyInterface {

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label();

  /**
   * Fallback plugin ID getter.
   *
   * @return string
   *   Fallback Plugin ID.
   */
  public function getFallbackPluginId();

  /**
   * Generates node list based on plugin conditions.
   *
   * @return array
   *   Nodes list.
   */
  public function generate();

}
