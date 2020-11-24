<?php

namespace Drupal\mars_search\Processors;

/**
 * SearchBuilderInterface.
 */
interface SearchBuilderInterface {

  /**
   * Performs search API query and returns results and facets info.
   *
   * @param string $grid_type
   *   Type of grid.
   * @param array $config
   *   Search config.
   * @param string $grid_id
   *   Searcher identifier.
   *
   * @return array
   *   Array with facets and results.
   */
  public function buildSearchResults(string $grid_type, array $config = [], string $grid_id = 'searcher_1');

}
