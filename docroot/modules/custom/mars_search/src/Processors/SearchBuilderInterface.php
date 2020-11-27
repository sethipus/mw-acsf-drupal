<?php

namespace Drupal\mars_search\Processors;

/**
 * SearchBuilderInterface.
 */
interface SearchBuilderInterface {

  /**
   * List of vocabularies which are included in indexing.
   *
   * @var array
   */
  const TAXONOMY_VOCABULARIES = [
    'mars_brand_initiatives' => [
      'label' => 'Brand initiatives',
      'content_types' => ['article', 'recipe', 'landing_page', 'campaign'],
    ],
    'mars_occasions' => [
      'label' => 'Occasions',
      'content_types' => [
        'article', 'recipe', 'product', 'landing_page', 'campaign',
      ],
    ],
    'mars_flavor' => [
      'label' => 'Flavor',
      'content_types' => ['product'],
    ],
    'mars_format' => [
      'label' => 'Format',
      'content_types' => ['product'],
    ],
    'mars_diet_allergens' => [
      'label' => 'Diet & Allergens',
      'content_types' => ['product'],
    ],
    'mars_trade_item_description' => [
      'label' => 'Trade item description',
      'content_types' => ['product'],
    ],
  ];

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
   * @param array $config
   *   Search config.
   * @param string $grid_id
   *   Searcher identifier.
   *
   * @return array
   *   Array with facets and results.
   */
  public function buildSearchFacets(array $config = [], string $grid_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID);

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
