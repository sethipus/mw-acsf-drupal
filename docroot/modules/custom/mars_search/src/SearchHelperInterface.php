<?php

namespace Drupal\mars_search;

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
  public function getSearchResults(array $options, $searcher_key);

  /**
   * Returns list with available facets keys.
   *
   * @return array
   *   Array with facets keys.
   */
  public function getFacetKeys();

  /**
   * Returns Url based on current request with GET parameters.
   *
   * @return \Drupal\Core\Url
   *   Current Url object.
   */
  public function getCurrentUrl();

  /**
   * Converts SOLR facets to mars-friendly links.
   *
   * @param array $facets
   *   SOLR facets.
   * @param string $facet_key
   *   Facet key.
   * @param int $search_id
   *   Search id.
   *
   * @return array
   *   Array with links according to mars frontend logic.
   */
  public function prepareFacetsLinks(array $facets, $facet_key, $search_id);

  /**
   * Returns default options for search query.
   *
   * @return array
   *   Array with options.
   */
  public function getSearchQueryDefaultOptions();

  /**
   * Returns current request.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   Current request stack object.
   */
  public function getRequest();

}
