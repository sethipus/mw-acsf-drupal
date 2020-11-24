<?php

namespace Drupal\mars_search\Processors;

/**
 * Class SearchTermFacetProcessInterface.
 */
interface SearchTermFacetProcessInterface {

  /**
   * Prepare filter variables.
   *
   * @param array $facets
   *   The facet result from search query.
   * @param array $vocabularies
   *   List of vocabularies to process.
   * @param int $grid_id
   *   Id of grid for search.
   */
  public function processFilter(array $facets, array $vocabularies, $grid_id);

  /**
   * Review if query has key.
   *
   * @param string $key
   *   Query key.
   */
  public function hasQueryKey(string $key);

  /**
   * Retrieve query key value.
   *
   * @param string $key
   *   Query key.
   * @param int $grid_id
   *   Id of grid for search.
   */
  public function getQueryValue(string $key, int $grid_id);

  /**
   * Prepare facet links.
   *
   * @param array $facets
   *   The facet result from search query.
   * @param string $facet_key
   *   Query key.
   * @param string $search_id
   *   Id of grid for search.
   */
  public function prepareFacetsLinks(array $facets, string $facet_key, string $search_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID);

}
