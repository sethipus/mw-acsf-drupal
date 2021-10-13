<?php

namespace Drupal\mars_search\Processors;

use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\search_api\Query\QueryInterface;

/**
 * Class SearchQueryParser - query parser logic.
 */
class SearchQueryParser implements SearchQueryParserInterface, SearchProcessManagerInterface {

  /**
   * Request stack that controls the lifecycle of requests.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  public $request;

  /**
   * Search categories processor.
   *
   * @var \Drupal\mars_search\Processors\SearchCategoriesInterface
   */
  protected $searchCategories;

  /**
   * Search pretty facets.
   *
   * @var \Drupal\mars_search\Processors\SearchPrettyFacetProcessInterface
   */
  protected $searchPrettyFacet;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    RequestStack $request,
    SearchCategoriesInterface $searchCategories,
    SearchPrettyFacetProcessInterface $search_pretty_facet
  ) {
    $this->request = $request->getMasterRequest();
    $this->searchCategories = $searchCategories;
    $this->searchPrettyFacet = $search_pretty_facet;
  }

  /**
   * {@inheritdoc}
   */
  public function getManagerId() {
    return 'search_query_parser';
  }

  /**
   * {@inheritdoc}
   */
  public function parseQuery(string $search_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID) {
    // Getting all GET parameters in array.
    $query_parameters = $this->request->query->all();

    if (!array_key_exists('faq_filter_topic', $query_parameters)) {
      $this->searchPrettyFacet->checkPrettyFacets($query_parameters);
    }

    // In autocomplete case we have to get search id from the GET query.
    if (isset($query_parameters['search_id'])) {
      $search_id = $query_parameters['search_id'];
    }
    // Removing GA 's' query param to don't include it into SOLR query.
    if (isset($query_parameters['s'])) {
      unset($query_parameters['s']);
    }

    $options = $query_parameters;
    // Set default filter options.
    $filter = $this->getDefaultOptions($query_parameters);
    // Filter options array for current grid.
    array_walk($options, [$this, 'filterByGridId'], $search_id);
    $options = array_filter($options);
    // Getting search keywords.
    if (array_key_exists(SearchQueryParserInterface::MARS_SEARCH_SEARCH_KEY, $options)) {
      $filter['keys'] = $options[SearchQueryParserInterface::MARS_SEARCH_SEARCH_KEY];
      unset($options[SearchQueryParserInterface::MARS_SEARCH_SEARCH_KEY]);
    }
    // Prepare filter conditions.
    $filter['conditions'] = array_merge(
      $filter['conditions'],
      array_map([$this, 'mapGridConditions'], array_keys($options), $options)
    );
    // Getting search filters query logic.
    $filter['options_logic'] = !empty($query_parameters['options_logic']) ? $query_parameters['options_logic'] : $filter['options_logic'];
    // Autocomplete specific option for header search overlay.
    // If it is set we display nodes cards, otherwise – just links.
    if (isset($query_parameters['cards_view'])) {
      $filter['cards_view'] = $query_parameters['cards_view'];
    }
    return $filter;
  }

  /**
   * Process search parameters by grid id.
   *
   * @param mixed $query_parameters
   *   Processed array.
   * @param string $key
   *   Processed array key.
   * @param string $grid_id
   *   Grid ID.
   */
  protected function filterByGridId(&$query_parameters, string $key, string $grid_id) {
    if (is_array($query_parameters) && !empty($query_parameters[$grid_id])) {
      $query_parameters = $query_parameters[$grid_id];
    }
    else {
      $query_parameters = NULL;
    }
  }

  /**
   * Process search conditions.
   *
   * @param mixed $filter
   *   Filter values.
   * @param mixed $parameter_value
   *   Parameters values.
   *
   * @return array
   *   Result mapped value.
   */
  protected function mapGridConditions($filter, $parameter_value) {
    if (in_array($filter, array_keys($this->searchCategories->getCategories()))) {
      return [
        $filter,
        explode(',', $parameter_value),
        'IN',
      ];
    }
    else {
      return [
        $filter,
        $parameter_value,
        '=',
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOptions(array $query_parameters = []) {
    $faq_operator = empty($query_parameters['faq']) ? '<>' : '=';
    $offset = 0;
    // Default limit.
    $limit = 8;
    // Getting search offset.
    if (array_key_exists(SearchQueryParserInterface::MARS_SEARCH_SEARCH_OFFSET, $query_parameters)) {
      $offset = $query_parameters[SearchQueryParserInterface::MARS_SEARCH_SEARCH_OFFSET];
    }
    // Getting search limit.
    if (array_key_exists(SearchQueryParserInterface::MARS_SEARCH_SEARCH_LIMIT, $query_parameters)) {
      $limit = $query_parameters[SearchQueryParserInterface::MARS_SEARCH_SEARCH_LIMIT];
    }
    return [
      'conditions' => [
        ['type', 'faq', $faq_operator, TRUE],
      ],
      'offset' => intval($offset),
      'limit' => intval($limit),
      // Just to not have this empty.
      'options_logic' => 'AND',
      'keys' => '',
      'sort' => [
        'field_product_ranking' => QueryInterface::SORT_ASC,
        'bundle_weight' => QueryInterface::SORT_ASC,
        'search_api_relevance' => QueryInterface::SORT_DESC,
        'title' => QueryInterface::SORT_ASC,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function parseFilterPreset(array $searchOptions, array $config) {
    // Adjusting them with grid specific configuration.
    // Content type filter.
    if (!empty($config['content_type'])) {
      // Remove filter by type.
      $searchOptions['conditions'] = array_filter($searchOptions['conditions'], function ($condition, $k) {
        return $condition[0] !== 'type';
      }, ARRAY_FILTER_USE_BOTH);
      $searchOptions['conditions'][] = ['type', $config['content_type'], '='];
    }

    // Populate top results items before other results.
    if (isset($searchOptions['top_results_query']) &&
      $searchOptions['top_results_query'] &&
      !empty($searchOptions['top_results_ids'])) {

      $searchOptions['conditions'][] = [
        'nid',
        $searchOptions['top_results_ids'],
        'IN',
      ];
    }
    elseif (!empty($config['top_results_wrapper']['top_results']) && empty($searchOptions['facet_option'])) {
      $top_result_ids = array_map(function ($value) {
        return $value['target_id'];
      }, $config['top_results_wrapper']['top_results']);
      // Adjusting query options to consider top results.
      // Adjusting limit.
      // Excluding top results ids from query.
      $searchOptions['conditions'][] = ['nid', $top_result_ids, 'NOT IN'];
    }

    // Taxonomy preset filter(s).
    $config['general_filters'] = !empty($config['general_filters']) ? $config['general_filters'] : [];
    foreach ($config['general_filters'] as $filter_key => $filter_value) {
      if (!empty($filter_value['select'])) {
        $searchOptions['conditions'][] = [
          $filter_key,
          $filter_value['select'],
          'IN',
        ];
      }
    }
    $searchOptions['options_logic'] = !empty($config['general_filters']['options_logic']) ? $config['general_filters']['options_logic'] : 'and';

    return $searchOptions;
  }

}
