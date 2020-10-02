<?php

namespace Drupal\mars_search;

/**
 * SearchQueryParserInterface.
 */
interface SearchQueryParserInterface {


  /**
   * Search key which is using in URL.
   */
  const MARS_SEARCH_SEARCH_KEY = 'search';

  /**
   * Search page path.
   */
  const MARS_SEARCH_DEFAULT_SEARCH_ID = 1;

  /**
   * Converts current GET parameters into SOLR friendly array.
   *
   * @param $search_id int
   *   Search identifier.
   *
   * @return array
   *   Array with SOLR filters.
   */
  public function parseQuery($search_id);

  /**
   * Returns default options for search query.
   *
   * @param $query_parameters array
   *   GET query parameters.
   *
   * @return array
   *   Array with options.
   */
  public function getDefaultOptions(array $query_parameters);

}
