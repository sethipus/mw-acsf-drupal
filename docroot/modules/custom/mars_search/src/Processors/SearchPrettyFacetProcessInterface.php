<?php

namespace Drupal\mars_search\Processors;

/**
 * SearchPrettyFacetProcessInterface contains description for methods.
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
   * @param string $category_label
   *
   *   Category label.
   *
   * @return array
   *   Return pretty assets key.
   */
  public static function getPrettyFacetKeys($category_label);

  /**
   * Rewrite filters keys.
   *
   * @param array $build
   *   The build of facets.
   */
  public function rewriteFilterKeys(array &$build);

  /**
   * Get Category term label.
   */
  public function getCategoryTermLabel();

}
