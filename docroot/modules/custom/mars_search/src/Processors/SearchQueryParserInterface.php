<?php

namespace Drupal\mars_search\Processors;

/**
 * SearchQueryParserInterface.
 */
interface SearchQueryParserInterface {


  /**
   * Search key which is using in URL.
   */
  const MARS_SEARCH_SEARCH_KEY = 'search';

  /**
   * Search offset which is using in pager callback.
   */
  const MARS_SEARCH_SEARCH_OFFSET = 'offset';

  /**
   * Search limit which is using in pager callback.
   */
  const MARS_SEARCH_SEARCH_LIMIT = 'limit';

  /**
   * Search page path.
   */
  const MARS_SEARCH_DEFAULT_SEARCH_ID = '1';

  /**
   * Converts current GET parameters into SOLR friendly array.
   *
   * @param string $search_id
   *   Search identifier.
   *
   * @return array
   *   Array with SOLR filters.
   */
  public function parseQuery(string $search_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID);

  /**
   * Parse filter preset from block configuration.
   *
   * @param array $searchOptions
   *   Search options array.
   * @param array $config
   *   Block configuration.
   *
   * @return array
   *   Array with SOLR filters.
   */
  public function parseFilterPreset(array $searchOptions, array $config);

}
