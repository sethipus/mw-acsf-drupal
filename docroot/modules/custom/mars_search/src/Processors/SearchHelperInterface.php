<?php

namespace Drupal\mars_search\Processors;

/**
 * SearchHelperInterface.
 */
interface SearchHelperInterface {

  /**
   * Search key which is using in URL.
   */
  const MARS_SEARCH_SEARCH_KEY = 'search';

  /**
   * Search page path.
   */
  const MARS_SEARCH_SEARCH_PAGE_PATH = 'search';

  /**
   * Performs search API query and returns results and facets info.
   *
   * @param array $options
   *   Search options.
   * @param string $searcher_key
   *   Searcher identifier.
   *
   * @return array
   *   Array with facets and results.
   */
  public function getSearchResults(array $options = [], string $searcher_key = 'searcher_1');

  /**
   * Return current Url object.
   */
  public function getCurrentUrl();

  /**
   * Get alias for search url.
   */
  public function getAliasForSearchUrl();

}
