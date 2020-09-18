<?php

namespace Drupal\mars_search;

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
   * @param array $options
   *   Search options.
   *
   * @param string $searcher_key
   *   Searcher identifier.
   *
   * @return array
   *   Array with facets and results.
   */
  public function getSearchResults($options, $searcher_key);
}
