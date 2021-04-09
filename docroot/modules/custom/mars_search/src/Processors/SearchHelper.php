<?php

namespace Drupal\mars_search\Processors;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\search_api\SearchApiException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SearchHelper.
 */
class SearchHelper implements SearchHelperInterface, SearchProcessManagerInterface {

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
   * Request stack that controls the lifecycle of requests.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  public $request;

  /**
   * Search categories processor.
   *
   * @var \Drupal\mars_search\Processors\SearchCategoriesInterface
   */
  protected $searchCategories;

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
    SearchCategoriesInterface $searchCategories
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('mars_search');
    $this->request = $request->getMasterRequest();
    $this->searchCategories = $searchCategories;
  }

  /**
   * {@inheritdoc}
   */
  public function getManagerId() {
    return 'search_helper';
  }

  /**
   * {@inheritdoc}
   */
  public function getSearchResults(array $options = [], string $searcher_key = 'searcher_default') {
    if (isset($this->searches[$searcher_key])) {
      return $this->searches[$searcher_key];
    }

    /** @var \Drupal\search_api\IndexInterface $index */
    $index = $this->entityTypeManager->getStorage('search_api_index')->load('acquia_search_index');

    $query = $index->query([]);
    if (isset($options['offset']) && isset($options['limit'])) {
      $query->range($options['offset'], $options['limit']);
    }
    elseif (isset($options['limit'])) {
      $query->range(0, isset($options['limit']));
    }
    else {
      $query->range(0, 4);
    }

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
        // Taxonomy filters go as a separate condition group with OR/AND logic.
        if (in_array($condition[0], array_keys($this->searchCategories->getCategories()))) {
          $conditionsGroup->addCondition($condition[0], $condition[1], $condition[2]);
        }
        else {
          $query->addCondition($condition[0], $condition[1], $condition[2]);
        }
      }
      $query->addConditionGroup($conditionsGroup);
    }

    // Remove unnecessary hypens from the search query.
    $options['keys'] = preg_replace('/^-/', '', $options['keys']);

    // Applying search keys.
    if (!empty($options['keys'])) {
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
      'itemsCount' => count($query_results->getResultItems()),
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
      'mars_category',
      'mars_diet_allergens',
      'mars_culture',
      'mars_food_type',
      'mars_main_ingredient',
      'mars_meal_type',
      'mars_method',
      'mars_prep_time',
      'mars_product_used',
      'mars_recipe_collection',
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

}
