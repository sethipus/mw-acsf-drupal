<?php

namespace Drupal\mars_search\Processors;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Url;

/**
 * Class SearchTermFacetProcess - facet terms processing logic.
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
   * Search categories processor.
   *
   * @var \Drupal\mars_search\Processors\SearchCategoriesInterface
   */
  protected $searchCategories;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RequestStack $request, SearchCategoriesInterface $searchCategories, LanguageHelper $language_helper, ConfigFactoryInterface $configFactory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->request = $request->getMasterRequest();
    $this->searchCategories = $searchCategories;
    $this->languageHelper = $language_helper;
    $this->configFactory = $configFactory;
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
    $taxonomy_vocs = $this->getTaxonomiesVocabularies($vocabularies);
    $label_config = $this->configFactory->get('mars_common.site_labels');
    $category_label = $label_config->get('mars_category') ? strtolower($label_config->get('mars_category')) : '';
    $appliedFilters = [];

    foreach ($vocabularies as $vocabulary => $vocabulary_data) {
      if (array_key_exists($vocabulary, $facets) && count($facets[$vocabulary]) > 0) {
        $facetValues = [];
        $countSelected = 0;
        $queryFilter = $vocabulary;
        foreach ($facets[$vocabulary] as $facet) {
          if ($facet['filter'] == '!' || !array_key_exists($facet['filter'], $terms)) {
            continue;
          }
          $key_taxonomy_term = $terms[$facet['filter']]->label();
          $pretty_path_for_vocabulary = SearchPrettyFacetProcess::getPrettyFacetKeys($category_label)[$vocabulary];
          $facetValues[] = [
            'title' => $terms[$facet['filter']]->label(),
            'key' => $grid_id . $pretty_path_for_vocabulary . urlencode($key_taxonomy_term),
            'weight' => $terms[$facet['filter']]->get('weight')->value,
          ];

          if ($this->hasQueryKey($pretty_path_for_vocabulary)) {
            $derivative_id = LanguageInterface::TYPE_URL;
            $current_language_id = $this->languageHelper->getLanguageManager()->getCurrentLanguage($derivative_id)->getId();
            $ids = [];
            $queryValueItems = explode(',', $this->getQueryValue($pretty_path_for_vocabulary, $grid_id));
            foreach ($queryValueItems as $term_name) {
              $term_object = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
                'vid' => $vocabulary,
                'name' => $term_name,
                'langcode' => $current_language_id,
              ]);
              $term_object = reset($term_object);
              if ($term_object instanceof TermInterface) {
                $ids[] = $term_object->id();
              }
            }

            if (in_array($facet['filter'], $ids)) {
              $facetValues[count($facetValues) - 1]['checked'] = 'checked';
              $countSelected++;
              $appliedFilters[] = $facetValues[count($facetValues) - 1];
            }
          }
        }
        if (count($facetValues) == 0) {
          continue;
        }
        $this->sortFilters($facetValues);
        $filters[] = [
          'filter_title' => $vocabulary_data['label'],
          'filter_id' => $queryFilter,
          'active_filters_count' => $countSelected,
          'checkboxes' => $facetValues,
          'weight' => $taxonomy_vocs[$vocabulary]->get('weight'),
        ];
      }
    }
    $this->sortFilters($filters);

    return [$appliedFilters, $filters];
  }

  /**
   * Load taxonomies for all vocabularies.
   *
   * @param array $facets
   *   The facet result from search query.
   * @param array $vocabularies
   *   List of vocabularies to process.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Terms.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
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
   * Load taxonomies vocabularies for provided filters list.
   *
   * @param array $vocabularies
   *   List of vocabularies to process.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Terms.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getTaxonomiesVocabularies(array $vocabularies) {
    // Loading needed taxonomy vocabularies.
    return $this->entityTypeManager
      ->getStorage('taxonomy_vocabulary')
      ->loadMultiple(array_keys($vocabularies));
  }

  /**
   * Sort filters and options.
   *
   * @param array $facetValues
   *   Option's values.
   *
   * @return bool
   *   Result of sorting operation (true or false in case of error).
   */
  private function sortFilters(array &$facetValues) {
    return usort($facetValues, function ($option_one, $option_two) {
      if ((int) $option_one['weight'] == (int) $option_two['weight']) {
        return 0;
      }
      if ((int) $option_one['weight'] < (int) $option_two['weight']) {
        return -1;
      }
      return 1;
    });
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
        if ($facet_key == 'faq_filter_topic') {
          $weight = $this->getFaqFacetWeight($facet['filter']);
        }
        $facets_links[] = [
          'class' => $facet_link_class,
          'text' => $facet['filter'],
          'attr' => [
            'href' => $url->toString(),
            'data-filter-value' => $facet['filter'],
          ],
          'weight' => $weight ?? NULL,
        ];
      }
    }
    $this->sortFilters($facets_links);
    return $facets_links;
  }

  /**
   * Get faq facet weight based on term weight.
   *
   * @param string $name
   *   Term name.
   *
   * @return \Drupal\taxonomy\TermInterface|null
   *   Term or null.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getFaqFacetWeight(string $name) {
    $derivative_id = LanguageInterface::TYPE_URL;
    $current_language_id = $this->languageHelper->getLanguageManager()->getCurrentLanguage($derivative_id)->getId();
    $term = $this->entityTypeManager
      ->getStorage('taxonomy_term')
      ->loadByProperties([
        'name' => $name,
        'vid' => 'faq_filter_topic',
        'langcode' => $current_language_id,
      ]);
    $term = reset($term);

    return ($term instanceof TermInterface)
      ? $term->get('weight')->value
      : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareFacetsLinksWithCount(array $facets, string $facet_key, string $search_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID) {
    // Preparing content type facet filter.
    $type_facet_key = 'type';
    $search_filters = [];

    $search_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID;
    $content_types = $this->searchCategories->getContentTypes();
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
          'title' => [
            '#type' => 'link',
            '#title' => $content_types[$type_facet['filter']] ?? $type_facet['filter'],
            '#url' => $url,
            '#attributes' => [
              'data-type' => $type_facet['filter'],
            ],
          ],
          'count' => $type_facet['count'],
          'search_results_item_modifier' => $state,
        ];
      }
    }
    return $search_filters;
  }

}
