<?php

namespace Drupal\mars_recommendations;

/**
 * Service class for Mars Recommendations events.
 */
final class RecommendationsEvents {

  /**
   * Emitted during the Recommendations Population Logic options loading.
   */
  const ALTER_POPULATION_LOGIC_OPTIONS = 'mars_recommendations.alter_population_logic_options';

  /**
   * Emitted during the Manual Recommendations Logic config form rendering.
   */
  const ALTER_MANUAL_LOGIC_BUNDLES = 'mars_recommendations.alter_manual_logic_bundles';

}
