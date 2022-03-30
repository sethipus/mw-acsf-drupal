<?php

namespace Drupal\mars_search\Processors;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_common\Traits\OverrideThemeTextColorTrait;
use Drupal\mars_search\SearchProcessFactoryInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class SearchBuilder - core logic of search processing.
 */
class SearchBuilder implements SearchBuilderInterface, SearchProcessManagerInterface {

  use StringTranslationTrait;
  use OverrideThemeTextColorTrait;

  /*
   * Quite a big value in case of query without limit.
   */
  const SEARCH_LIMIT_NO_LIMIT = 999999;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Menu link tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  /**
   * Search processing factory.
   *
   * @var \Drupal\mars_search\SearchProcessFactoryInterface
   */
  protected $searchProcessor;

  /**
   * Search helper.
   *
   * @var \Drupal\mars_search\Processors\SearchHelperInterface
   */
  protected $searchHelper;

  /**
   * Search query parser.
   *
   * @var \Drupal\mars_search\Processors\SearchQueryParserInterface
   */
  protected $searchQueryParser;

  /**
   * Taxonomy facet process service.
   *
   * @var \Drupal\mars_search\Processors\SearchTermFacetProcess
   */
  protected $searchTermFacetProcess;

  /**
   * Search categories processor.
   *
   * @var \Drupal\mars_search\Processors\SearchCategoriesInterface
   */
  protected $searchCategories;

  /**
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * The node view builder.
   *
   * @var \Drupal\node\NodeViewBuilder
   */
  protected $nodeViewBuilder;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * Minimum number of results.
   */
  const MINIMUM_NUMBER_OF_RESULTS = 1;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    MenuLinkTreeInterface $menuLinkTree,
    ThemeConfiguratorParser $themeConfiguratorParser,
    ConfigFactoryInterface $configFactory,
    SearchProcessFactoryInterface $searchProcessor,
    LanguageHelper $language_helper
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->menuLinkTree = $menuLinkTree;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->nodeViewBuilder = $this->entityTypeManager->getViewBuilder('node');
    $this->configFactory = $configFactory;
    $this->searchProcessor = $searchProcessor;
    $this->searchQueryParser = $this->searchProcessor->getProcessManager('search_query_parser');
    $this->searchHelper = $this->searchProcessor->getProcessManager('search_helper');
    $this->searchTermFacetProcess = $this->searchProcessor->getProcessManager('search_facet_process');
    $this->languageHelper = $language_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function getManagerId() {
    return 'search_builder';
  }

  /**
   * {@inheritdoc}
   */
  public function buildSearchResults(string $grid_type, array $config = [], string $grid_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID) {
    $build = [];
    $build['#items'] = [];

    // Getting default search options.
    $searchOptions = $this->searchQueryParser->parseQuery($grid_id);
    $searcher_key = static::SEARCH_PAGE_QUERY_ID;

    switch ($grid_type) {
      // Card Grid should include filter preset from configuration.
      case 'grid':
        $searchOptionsTop = $searchOptions;
        $searchOptions = $this->searchQueryParser->parseFilterPreset($searchOptions, $config);

        if (!empty($config['top_results_wrapper']['top_results'])) {
          $this->populateItemsByTopResults(
            $build,
            $searchOptions,
            $searchOptionsTop,
            $config
          );
        }
        $searcher_key = "grid_{$grid_id}";
        break;

      case 'faq':
        // Overriding some default options with FAQ specific values.
        // Overriding first condition from getDefaultOptions().
        $searchOptions['conditions'][0] = ['type', 'faq', '=', TRUE];
        $searchOptions['limit'] = $this->getFaqLimit($searchOptions);
        $searchOptions['sort'] = [
          'faq_item_queue_weight' => 'ASC',
          'created' => 'DESC',
        ];

        // That means filter topic filter is active.
        if ($this->searchHelper->request->get('faq_filter_topic')) {
          // Disabling entityqueue sorting when topic filter is active.
          unset($searchOptions['sort']['faq_item_queue_weight']);
        }

        break;

      default:
        break;
    }

    // Getting and building search results.
    $query_search_results = $this->searchHelper->getSearchResults($searchOptions, $searcher_key);
    $query_search_results['resultsCount'] += count($build['#items']);
    if ($query_search_results['resultsCount'] == 0) {
      $build['#no_results'] = $this->getSearchNoResult($searchOptions['keys'], $grid_type);
      $build['#grid_modifiers'] = 'no-results';
    }
    // FAQ items has different render.
    if ($grid_type == 'faq') {
      $build['#items'] = $this->prepareFaqRenderArray($query_search_results);
      $build['#search_result_text'] = (!empty($searchOptions['keys']) && $query_search_results['resultsCount'] > 0)
        ? $this->formatPlural($query_search_results['resultsCount'], 'Result for "@keys"', 'Results for "@keys"', ['@keys' => $searchOptions['keys']])
        : '';
      return [$searchOptions, $query_search_results, $build];
    }
    if(!empty($config['exclude_products'])){
      $exclude_products_arr = explode(',',$config['exclude_products']);
      $exclude_products_arr = array_map('trim',$exclude_products_arr);
    }
    foreach ($query_search_results['results'] as $node) {
      if((!empty($config['exclude_products']) && !in_array($node->id(),$exclude_products_arr)) || empty($config['exclude_products'])){
        if (!empty($config['override_text_color']['override_color'])) {
          $build['#items'][] = array_merge($this->nodeViewBuilder->view($node, 'card'), ['#text_color_override' => static::$overrideColor]);
        }
        else {
          $build['#items'][] = $this->nodeViewBuilder->view($node, 'card');
        }
      }
    }

    return [$searchOptions, $query_search_results, $build];
  }

  /**
   * Sort top results based on order in configuration.
   *
   * @param array $results
   *   Results of search.
   * @param array $weights
   *   Weights in configuration.
   *
   * @return array
   *   Sorted results.
   */
  private function sortTopResults(array $results, array $weights) {
    $results_weighted = [];
    foreach ($results as $result) {
      /** @var \Drupal\node\NodeInterface $result */
      $results_weighted[$weights[$result->id()]] = $result;
    }
    ksort($results_weighted);
    return $results_weighted;
  }

  /**
   * Populate build items by top results.
   *
   * @param array $build
   *   Build array.
   * @param array $searchOptions
   *   Common search options.
   * @param array $searchOptionsTop
   *   Search options for top results.
   * @param array $config
   *   Component's config.
   */
  private function populateItemsByTopResults(
    array &$build,
    array &$searchOptions,
    array $searchOptionsTop,
    array $config
  ) {
    $top_result_ids = array_map(function ($value) {
      return $value['target_id'];
    }, $config['top_results_wrapper']['top_results']);
    $searchOptionsTop['top_results_query'] = TRUE;
    $searchOptionsTop['offset'] = 0;
    $searchOptionsTop['limit'] = count($config['top_results_wrapper']['top_results']);

    $searchOptionsTop['top_results_ids'] = $top_result_ids;
    $searchOptionsTop = $this->searchQueryParser->parseFilterPreset($searchOptionsTop, $config);

    // Getting and building search results.
    $searcher_key_top = static::SEARCH_PAGE_QUERY_ID . '_top';
    $top_search_results = $this->searchHelper->getSearchResults($searchOptionsTop, $searcher_key_top);

    $top_results = $this->sortTopResults(
      $top_search_results['results'],
      array_flip($top_result_ids)
    );

    // Shift result by number of top results.
    $top_results_slice = array_slice(
      $top_results,
      $searchOptions['offset'],
      $searchOptions['limit']
    );
    foreach ($top_results_slice as $top_result_node) {
      $build['#items'][] = $this->nodeViewBuilder->view($top_result_node, 'card');
    }

    $searchOptions['offset'] = count($build['#items']) > 0 ? 0 : $searchOptions['offset'] - $top_search_results['resultsCount'];
    $searchOptions['limit'] = $searchOptions['limit'] - count($build['#items']);
  }

  /**
   * Get search limit for the faq list.
   *
   * @param array $searchOptions
   *   Search options.
   *
   * @return int|string
   *   Limit.
   */
  private function getFaqLimit(array $searchOptions) {
    return (isset($searchOptions['offset']) && $searchOptions['offset'] != 0)
      ? self::SEARCH_LIMIT_NO_LIMIT
      : 4;
  }

  /**
   * {@inheritdoc}
   */
  public function buildSearchFacets(string $grid_type, array $config = [], string $grid_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID) {
    $build = [];
    // Getting default search options.
    $facetOptions = $this->searchQueryParser->parseQuery($grid_id);
    $conditions = array_filter($facetOptions['conditions'], function ($value) {
      $condition_name = $value[0];
      return ($condition_name == 'type')
        ? TRUE
        : FALSE;
    });
    $facetOptions['conditions'] = $conditions;

    if ($grid_type == 'grid') {
      $facetOptions['facet_option'] = TRUE;
      $facetOptions = $this->searchQueryParser->parseFilterPreset($facetOptions, $config);
    }
    unset($facetOptions['limit']);

    if (!empty($config)) {
      // Populating search form.
      if (!empty($config['exposed_filters_wrapper']['toggle_search'])) {
        // Preparing search form.
        $label_config = $this->configFactory->get('mars_common.site_labels');
        $placeholder = $this->languageHelper->translate($label_config->get('faq_card_grid_search'));
        $build['#input_form'] = $this->getSearhForm($facetOptions['keys'], $placeholder, $grid_id);
        $build['#input_form']['#attributes']['class'][] = 'mars-autocomplete-field-card-grid';
      }
      // Prevent building facets if they are disabled in block configuration.
      if ($grid_type == 'grid' && empty($config['exposed_filters_wrapper']['toggle_filters'])) {
        $build['#filters'] = [];
        return $build;
      }
      if (!empty($config)) {
        $facet_id = "grid_{$grid_id}_facets";
      }
    }
    else {
      $facet_id = static::SEARCH_FACET_QUERY_ID;
    }
    if (!empty($facet_id)) {
      $facets_query = $this->searchHelper->getSearchResults($facetOptions, $facet_id);
      $this->searchCategories = $this->searchProcessor->getProcessManager('search_categories');
      $default_filters = $this->searchCategories->getCategories();
      if (isset($config['exclude_filters'])) {
        $this->hideExcludedFacetOptions($default_filters, $config['exclude_filters']);
      }
      $build['#applied_filters_list'] = [];
      $build['#filters'] = [];
      // Display facets when result count more
      // than minimum number of results.
      if ($facets_query['resultsCount'] > static::MINIMUM_NUMBER_OF_RESULTS) {
        [$build['#applied_filters_list'], $build['#filters']] = $this->searchTermFacetProcess->processFilter($facets_query['facets'], $default_filters, $grid_id);
      }
    }
    $this->searchProcessor->getProcessManager('search_pretty_facet_process')->rewriteFilterKeys($build);
    return $build;
  }

  /**
   * Removes excluded facet options from the available facets list.
   *
   * @param array $default_filters
   *   The default filters array.
   * @param array $excluded_options
   *   The excluded options configuration array.
   */
  private function hideExcludedFacetOptions(array &$default_filters, array $excluded_options) {
    if (!empty($excluded_options['filters'])) {
      foreach ($excluded_options['filters'] as $option) {
        if ($option !== 0) {
          unset($default_filters[$option]);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildSearchHeader(array $config = [], string $grid_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID) {
    $build = [];
    // Getting search results from SOLR.
    $facetOptions = $this->searchQueryParser->parseQuery();
    // Preparing search form.
    $placeholder = $config['search_header_placeholder'] ?? $this->t('Search products, recipes, articles...');
    $build['#input_form'] = $this->getSearhForm($facetOptions['keys'], $placeholder, $grid_id);

    // Remove filter by type.
    $facetOptions['conditions'] = array_filter($facetOptions['conditions'], function ($condition, $k) {
      return $condition[1] === 'faq';
    }, ARRAY_FILTER_USE_BOTH);
    $query_search_results = $this->searchHelper->getSearchResults($facetOptions, static::SEARCH_LINKS_QUERY_ID);

    $build['#filter_title_transform'] = $this->themeConfiguratorParser->getSettingValue('facets_text_transform', 'uppercase');
    $build['#search_filters'] = [];
    // Display facets when result count more
    // than minimum number of results.
    if ($query_search_results['resultsCount'] > static::MINIMUM_NUMBER_OF_RESULTS) {
      $build['#search_filters'] = $this->searchTermFacetProcess->prepareFacetsLinksWithCount($query_search_results['facets'], 'type', SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID);
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildFaqFilters() {
    $build = [];
    // Getting search results from SOLR.
    $searchOptions = $this->searchQueryParser->parseQuery();
    $label_config = $this->configFactory->get('mars_common.site_labels');
    $placeholder = $this->languageHelper->translate($label_config->get('faq_card_grid_search')) ?? '';
    $build['#input_form'] = $this->getSearhForm($searchOptions['keys'], $placeholder);
    $build['#input_form']['#attributes']['class'][] = 'mars-autocomplete-field-faq';
    $build['#input_form']['#attributes']['data-grid-query'] = 'faq=1';
    unset($searchOptions['conditions']);
    unset($searchOptions['keys']);
    // Facets query.
    $facets_search_results = $this->searchHelper->getSearchResults($searchOptions, 'faq_facets');
    $build['#facets'] = [];
    // Display facets when result count more
    // than minimum number of results.
    if ($facets_search_results['resultsCount'] > static::MINIMUM_NUMBER_OF_RESULTS) {
      $build['#facets'] = $this->searchTermFacetProcess->prepareFacetsLinks($facets_search_results['facets']['faq_filter_topic'], 'faq_filter_topic');
    }
    return $build;
  }

  /**
   * Search input form.
   *
   * @param string $keys
   *   Search query.
   * @param string $placeholder
   *   Search input placeholder.
   * @param string $grid_id
   *   Searcher identifier.
   *
   * @return array
   *   Array with search options.
   */
  protected function getSearhForm(string $keys, string $placeholder = '', string $grid_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID) {
    return [
      '#type' => 'textfield',
      '#attributes' => [
        'placeholder' => $placeholder,
        'class' => [
          'search-input__field',
          'mars-autocomplete-field',
          'data-layer-search-form-input',
        ],
        'title' => $this->t('Search'),
        'aria-label' => $this->t('Search'),
        'data-grid-id' => $grid_id,
        'autocomplete' => 'off',
      ],
      '#value' => $keys,
    ];
  }

  /**
   * Parse query filters.
   *
   * @param array $config
   *   Search config.
   * @param string $grid_id
   *   Searcher identifier.
   *
   * @return array
   *   Array with search options.
   */
  protected function parseQuery(array $config = [], string $grid_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID) {
    // Getting default search options.
    $searchOptions = $this->searchQueryParser->parseQuery($grid_id);
    if (!empty($config)) {
      $searchOptions = $this->searchQueryParser->parseFilterPreset($searchOptions, $config);
    }
    return $searchOptions;
  }

  /**
   * Prepare FAQ render array.
   *
   * @param array $search_results
   *   Search config.
   *
   * @return array
   *   Array with search options.
   */
  protected function prepareFaqRenderArray(array $search_results) {
    $build = [];
    $faq_ques_label = $this->themeConfiguratorParser->getSettingValue('faq_ques_label');
    $faq_ans_label = $this->themeConfiguratorParser->getSettingValue('faq_ans_label');
    /** @var \Drupal\node\NodeInterface $search_result */
    foreach ($search_results['results'] as $row_key => $search_result) {
      if (!$search_result->hasField('field_qa_item_question')) {
        continue;
      }
      $question_value = !empty($search_results['highlighted_fields'][$row_key]['field_qa_item_question'][0])
        ? $search_results['highlighted_fields'][$row_key]['field_qa_item_question'][0]
        : $search_result->get('field_qa_item_question')->value;
      $answer_value = !empty($search_results['highlighted_fields'][$row_key]['field_qa_item_answer'][0])
        ? $search_results['highlighted_fields'][$row_key]['field_qa_item_answer'][0]
        : $search_result->get('field_qa_item_answer')->value;
      $build[$row_key] = [
        'items' => [
          'question' => $question_value,
          'answer' => $answer_value,
        ],
        '#theme' => 'mars_search_faq_item',
        '#content' => [
          'question' => $question_value,
          'answer' => $answer_value,
        ],
        '#faq_ques_label' => $faq_ques_label,
        '#faq_ans_label' => $faq_ans_label,
      ];
    }
    return $build;
  }

  /**
   * Render search no result block.
   */
  public function getSearchNoResult($key, $grid_type) {
    $config = $this->configFactory->get('mars_search.search_no_results');
    $heading = (!empty($key))
      ? str_replace('@keys', $key, $this->languageHelper->translate($config->get('no_results_heading')))
      : $this->languageHelper->translate($config->get('no_results_heading_empty_str'));
    $build = [
      '#no_results_heading' => $heading,
      '#no_results_text' => $this->languageHelper->translate($config->get('no_results_text')),
      '#theme' => 'mars_search_no_results',
      '#graphic_divider' => $this->themeConfiguratorParser->getGraphicDivider(),
    ];

    switch ($grid_type) {
      case 'search_page':
        $linksMenu = $this->buildMenu('error-page-menu');
        $links = [];
        foreach ($linksMenu as $linkMenu) {
          $links[] = [
            'content' => $linkMenu['title'],
            'attributes' => [
              'target' => '_self',
              'href' => $linkMenu['url'],
            ],
          ];
        }
        $build['#no_results_links'] = $links;
        break;

      case 'grid':
        $build['#brand_border'] = $this->themeConfiguratorParser->getBrandBorder2();
        break;

      default:
        break;
    }

    return $build;
  }

  /**
   * Render menu by its name.
   *
   * @param string $menu_name
   *   Menu name.
   *
   * @return array
   *   Rendered menu.
   */
  protected function buildMenu($menu_name) {
    $menu_parameters = new MenuTreeParameters();
    $menu_parameters->setMaxDepth(1);

    // Get the tree.
    $tree = $this->menuLinkTree->load($menu_name, $menu_parameters);

    // Apply some manipulators (checking the access, sorting).
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkNodeAccess'],
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuLinkTree->transform($tree, $manipulators);

    // And the last step is to actually build the tree.
    $menu = $this->menuLinkTree->build($tree);
    $menu_links = [];
    if (!empty($menu['#items'])) {
      foreach ($menu['#items'] as $item) {
        array_push($menu_links, [
          'title' => $item['title'],
          'url' => $item['url']->setAbsolute()->toString(),
        ]);
      }
    }
    return $menu_links;
  }

}
