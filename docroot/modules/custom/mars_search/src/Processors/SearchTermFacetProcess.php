<?php

namespace Drupal\mars_search\Processors;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Class SearchTermFacetProcess.
 */
class SearchTermFacetProcess implements SearchTermFacetProcessInterface, SearchProcessManagerInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Request stack that controls the lifecycle of requests.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RequestStack $request) {
    $this->entityTypeManager = $entity_type_manager;
    $this->request = $request->getMasterRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function getManagerId() {
    return 'search_facet_process';
  }

  /**
   * Prepare filter variables.
   *
   * @param array $facets
   *   The facet result from search query.
   * @param array $vocabularies
   *   List of vocabularies to process.
   * @param int $grid_id
   *   Id of grid for search.
   */
  public function processFilter(array $facets, array $vocabularies, $grid_id) {
    $filters = [];
    $terms = $this->getTaxonomies($facets, $vocabularies);
    $appliedFilters = [];

    foreach ($vocabularies as $vocabulary => $vocabulary_data) {
      if (array_key_exists($vocabulary, $facets) && count($facets[$vocabulary]) > 0) {
        $facetValues = [];
        $countSelected = 0;
        $queryFilter = urlencode($vocabulary . '[' . $grid_id . ']');
        foreach ($facets[$vocabulary] as $facet) {
          if ($facet['filter'] == '!' || !array_key_exists($facet['filter'], $terms)) {
            continue;
          }
          $facetValues[] = [
            'title' => $terms[$facet['filter']]->label(),
            'key' => $facet['filter'],
          ];
          if (
            $this->hasQueryKey($vocabulary) &&
            strpos($this->getQueryValue($vocabulary, $grid_id), $facet['filter']) !== FALSE
          ) {
            $facetValues[count($facetValues) - 1]['checked'] = 'checked';
            $countSelected++;
            $appliedFilters[] = $facetValues[count($facetValues) - 1];
          }
        }
        if (count($facetValues) == 0) {
          continue;
        }
        $filters[] = [
          'filter_title' => $vocabulary_data['label'],
          'filter_id' => $queryFilter,
          'active_filters_count' => $countSelected,
          'checkboxes' => $facetValues,
        ];
      }
    }

    return [$appliedFilters, $filters];
  }

  /**
   * Load taxonomies for all vocabularies.
   *
   * @param array $facets
   *   The facet result from search query.
   * @param array $vocabularies
   *   List of vocabularies to process.
   */
  private function getTaxonomies(array $facets, array $vocabularies) {
    $term_ids = [];

    // Getting term names.
    foreach ($facets as $facet_key => $facet) {
      // That means it's a taxonomy facet.
      if (in_array($facet_key, array_keys($vocabularies))) {
        foreach ($facet as $facet_data) {
          if (is_numeric($facet_data['filter'])) {
            $term_ids[] = $facet_data['filter'];
          }
        }
      }
    }
    // Loading needed taxonomy terms.
    return $this->entityTypeManager->getStorage('taxonomy_term')->loadMultiple($term_ids);
  }

  /**
   * Review if query has key.
   *
   * @param string $key
   *   Query key.
   */
  public function hasQueryKey($key) {
    return $this->request->query->has($key);
  }

  /**
   * Retrieve query key value.
   *
   * @param string $key
   *   Query key.
   * @param int $grid_id
   *   Id of grid for search.
   */
  public function getQueryValue($key, $grid_id) {
    $filterValue = $this->request->query->get($key);
    if (array_key_exists($grid_id, $filterValue)) {
      return $this->request->query->get($key)[$grid_id];
    }
    return '';
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
   * {@inheritdoc}
   */
  public function prepareFacetsLinks(array $facets, string $facet_key, string $search_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID) {
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
          'attr' => [
            'href' => $url->toString(),
            'data-filter-value' => $facet['filter'],
          ],
        ];
      }
    }
    return $facets_links;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareFacetsLinksWithCount(array $facets, string $facet_key, string $search_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID) {
    // Preparing content type facet filter.
    $type_facet_key = 'type';
    $search_filters = [];

    $search_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID;
    if (!empty($facets[$facet_key])) {
      foreach ($facets[$facet_key] as $type_facet) {
        $url = $this->getCurrentUrl();
        $url_options = $url->getOptions();
        // That means facet is active.
        $state = '';
        $facet_query_value = $this->request->query->get($type_facet_key);

        if (!empty($facet_query_value[$search_id]) &&  $facet_query_value[$search_id] == $type_facet['filter']) {
          // Removing facet query from active filter to allow deselect it.
          unset($url_options['query'][$type_facet_key]);
          $state = 'active';
        }
        else {
          // Adding facet filter to the query.
          $url_options['query'][$type_facet_key][$search_id] = $type_facet['filter'];
        }
        $url->setOptions($url_options);

        $search_filters[] = [
          'title' => Link::fromTextAndUrl($type_facet['filter'], $url),
          'count' => $type_facet['count'],
          'search_results_item_modifier' => $state,
        ];
      }
    }
    return $search_filters;
  }

}
