<?php

namespace Drupal\mars_search;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\mars_search\Plugin\Block\SearchGridBlock;
use Drupal\search_api\SearchApiException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SearchHelper.
 */
class SearchHelper implements SearchHelperInterface {
  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Mars Search logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Current master request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Taxonomy facet process service.
   *
   * @var \Drupal\mars_search\SearchTermFacetProcess
   */
  protected $searchTermFacetProcess;

  /**
   * Arrays with searches metadata.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $searches = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory,
    RequestStack $request,
    SearchTermFacetProcess $searchTermFacetProcess
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('mars_search');
    $this->request = $request->getMasterRequest();
    $this->searchTermFacetProcess = $searchTermFacetProcess;
  }

  /**
   * {@inheritdoc}
   */
  public function getSearchResults($options = [], $searcher_key = 'searcher_1') {
    if (isset($this->searches[$searcher_key])) {
      return $this->searches[$searcher_key];
    }

    // Populating default query options if they are not explicitly specified.
    if (!$options) {
      $options = $this->getSearchQueryDefaultOptions();
    }

    $index = $this->entityTypeManager->getStorage('search_api_index')->load('acquia_search_index');

    $query_options = [];
    if (!empty($options['limit'])) {
      $query_options = ['limit' => $options['limit']];
    }

    $query = $index->query($query_options);

    $facet_options = [];
    // Setting facets query options.
    $facet_fields = $this->getFacetKeys();
    foreach ($facet_fields as $facet_field) {
      $facet_options[$facet_field] = [
        'field' => $facet_field,
        'limit' => 20,
        'operator' => 'AND',
        'min_count' => 1,
        'missing' => TRUE,
      ];
    }
    $query->setOption('search_api_facets', $facet_options);

    // Applying predefined conditions.
    // $condition[0] is a filter key.
    // $condition[1] is a filter value.
    // $condition[2] is a filter comparison operator: equals, not equals etc.
    if (!empty($options['conditions'])) {
      $conditionsGroup = $query->createConditionGroup($options['options_logic']);
      foreach ($options['conditions'] as $condition) {
        // Disable all filters in case corresponding flag is set.
        if (!empty($options['disable_filters']) && empty($condition[3])) {
          continue;
        }
        // Taxonomy filters go as a separate condition group with OR/AND logic.
        if (in_array($condition[0], array_keys(SearchGridBlock::TAXONOMY_VOCABULARIES))) {
          $conditionsGroup->addCondition($condition[0], $condition[1], $condition[2]);
        }
        else {
          $query->addCondition($condition[0], $condition[1], $condition[2]);
        }
      }
      $query->addConditionGroup($conditionsGroup);
    }

    // Applying search keys.
    if ($options['keys'] && empty($options['disable_filters'])) {
      $query->keys($options['keys']);
    }

    // Adding sorting.
    if (!empty($options['sort'])) {
      foreach ($options['sort'] as $sort_key => $sort_direction) {
        $query->sort($sort_key, $sort_direction);
      }
    }

    $query_results = $query->execute();

    $results = $highlighted_fields = [];
    foreach ($query_results->getResultItems() as $resultItem) {
      // Do not fail page load if search index is not in sync with database.
      try {
        $results[] = $resultItem->getOriginalObject()->getValue();
        $highlighted_fields[] = $resultItem->getExtraData('highlighted_fields');
      }
      catch (SearchApiException $e) {
        $this->logger->warning($e->getMessage());
      }
    }

    // It's better to trim facet values in a single place.
    $facets_data = $query_results->getExtraData('search_api_facets', []);
    foreach ($facets_data as $facet_key => $facet) {
      foreach ($facet as $facet_delta => $facet_value) {
        $facets_data[$facet_key][$facet_delta]['filter'] = trim($facets_data[$facet_key][$facet_delta]['filter'], '"');
      }
    }

    $this->searches[$searcher_key] = [
      'results' => $results,
      'facets' => $facets_data,
      'resultsCount' => $query_results->getResultCount(),
      'highlighted_fields' => $highlighted_fields,
    ];

    return $this->searches[$searcher_key];
  }

  /**
   * {@inheritdoc}
   */
  public function getFacetKeys() {
    return [
      'type',
      'faq_filter_topic',
      'mars_flavor',
      'mars_format',
      'mars_diet_allergens',
      'mars_occasions',
      'mars_brand_initiatives',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentUrl() {
    // Getting Url object from current request.
    $url = Url::createFromRequest($this->request);
    // Adding GET parameters.
    $url->setOption('query', $this->request->query->all());
    return $url;
  }

  /**
   * Process taxonomy facet links.
   *
   * @param array $facets
   *   The facet result from search query.
   * @param array $vocabularies
   *   List of vocabularies to process.
   * @param int $grid_id
   *   Id of grid for search.
   */
  public function processTermFacets(array $facets, array $vocabularies, int $grid_id) {
    return $this->searchTermFacetProcess->processFilter($facets, $vocabularies, $grid_id);
  }

  /**
   * {@inheritdoc}
   */
  public function prepareFacetsLinks($facets, $facet_key, $search_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID) {
    $facets_links = [];
    if (!$facets) {
      return $facets_links;
    }
    $url = $this->getCurrentUrl();
    $options = $url->getOptions();
    foreach ($facets as $facet) {
      // "!" means all items without facets so ignore this "facet".
      if ($facet['filter'] != '!') {
        // HTML class for facet link.
        $facet_link_class = '';

        // That means facet is active.
        $facet_query_value = $this->request->query->get($facet_key);
        if (isset($facet_query_value[$search_id]) && $facet_query_value[$search_id] == $facet['filter']) {
          $facet_link_class = 'active';
          // Removing facet query from active filter to allow deselect it.
          unset($options['query'][$facet_key]);
        }
        else {
          // Adding facet filter to the query.
          $options['query'][$facet_key][$search_id] = $facet['filter'];
        }

        $url->setOptions($options);
        $facets_links[] = [
          'class' => $facet_link_class,
          'text' => $facet['filter'],
          'attr' => ['href' => $url->toString()],
        ];
      }
    }
    return $facets_links;
  }

  /**
   * {@inheritdoc}
   */
  public function getSearchQueryDefaultOptions() {
    return [
      'conditions' => [
        // We don't need FAQ nodes in most cases.
        ['type', 'faq', '<>'],
        ['type', 'product_multipack', '<>'],
      ],
      'limit' => 12,
      'sort' => [
        'created' => 'DESC',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getRequest() {
    return $this->request;
  }

}
