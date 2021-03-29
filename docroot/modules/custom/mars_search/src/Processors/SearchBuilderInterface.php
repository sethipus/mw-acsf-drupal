<?php

namespace Drupal\mars_search\Processors;

/**
 * SearchBuilderInterface.
 */
interface SearchBuilderInterface {

  /**
   * Facet search query id.
   *
   * @var string
   */
  const SEARCH_FACET_QUERY_ID = 'main_search_facets';

  /**
   * Search page query id.
   *
   * @var string
   */
  const SEARCH_PAGE_QUERY_ID = 'main_search_results';

  /**
   * Links search query id.
   *
   * @var string
   */
  const SEARCH_LINKS_QUERY_ID = 'main_search_links';

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

  /**
   * Prepare facet for search results.
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
  public function buildSearchFacets(string $grid_type, array $config = [], string $grid_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID);

  /**
   * Prepare links for search page header.
   *
   * @param array $config
   *   Search config.
   * @param string $grid_id
   *   Searcher identifier.
   *
   * @return array
   *   Array with facets and results.
   */
  public function buildSearchHeader(array $config = [], string $grid_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID);

}
