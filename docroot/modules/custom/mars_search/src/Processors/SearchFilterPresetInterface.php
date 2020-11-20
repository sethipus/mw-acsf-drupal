<?php

namespace Drupal\mars_search\Processors;

/**
 * SearchFilterPresetInterface.
 */
interface SearchFilterPresetInterface {

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
   * @param array $query_parameters
   *   Search identifier.
   * @param array $config
   *   Search identifier.
   *
   * @return array
   *   Array with SOLR filters.
   */
  public function presetFilter(array $query_parameters, array $config);

}
