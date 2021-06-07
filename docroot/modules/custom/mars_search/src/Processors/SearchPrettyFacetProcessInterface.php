<?php

namespace Drupal\mars_search\Processors;

/**
 * SearchPrettyFacetProcessInterface.
 */
interface SearchPrettyFacetProcessInterface {

  /**
   * Check pretty facets.
   *
   * @param array $query_parameters
   *   The search query parameters.
   */
  public function checkPrettyFacets(array &$query_parameters);

  /**
   * Get pretty facets keys by taxonomies vocabularies ids.
   *
   * @return array
   *   Return pretty assets key.
   */
  public static function getPrettyFacetKeys();

  /**
   * Rewrite filters keys.
   *
   * @param array $build
   *   The build of facets.
   */
  public function rewriteFilterKeys(array &$build);

}
