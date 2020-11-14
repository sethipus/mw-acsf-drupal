<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_search\Form\SearchForm;
use Drupal\mars_search\SearchHelperInterface;
use Drupal\mars_search\SearchQueryParserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class SearchGridBlock.
 *
 * @Block(
 *   id = "search_grid_block",
 *   admin_label = @Translation("MARS: Search Grid block"),
 *   category = @Translation("Mars Search")
 * )
 *
 * @package Drupal\mars_search\Plugin\Block
 */
class SearchGridBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * List of content types which are included in indexing.
   *
   * @var array
   */
  const CONTENT_TYPES = [
    'product' => 'Product',
    'article' => 'Article',
    'recipe' => 'Recipe',
    'campaign' => 'Campaign',
    'landing_page' => 'Landing page',
  ];

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Search helper.
   *
   * @var \Drupal\mars_search\SearchHelperInterface
   */
  protected $searchHelper;

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
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Search query parser.
   *
   * @var \Drupal\mars_search\SearchQueryParserInterface
   */
  protected $searchQueryParser;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Current grid ID.
   *
   * @var int
   */
  private $gridId;

  /**
   * Default (common) dataLayer attributes.
   *
   * @var array
   */
  protected $dataLayerDefaults = [];

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('mars_search.search_helper'),
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('entity_type.manager')->getViewBuilder('node'),
      $container->get('form_builder'),
      $container->get('mars_search.search_query_parser'),
      $container->get('config.factory'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    SearchHelperInterface $search_helper,
    ThemeConfiguratorParser $themeConfiguratorParser,
    EntityViewBuilderInterface $node_view_builder,
    FormBuilderInterface $form_builder,
    SearchQueryParserInterface $search_query_parser,
    ConfigFactoryInterface $configFactory,
    RouteMatchInterface $routeMatch
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->searchHelper = $search_helper;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->nodeViewBuilder = $node_view_builder;
    $this->formBuilder = $form_builder;
    $this->searchQueryParser = $search_query_parser;
    $this->configFactory = $configFactory;
    $this->routeMatch = $routeMatch;
    // Getting unique grid id for the page.
    // This will be used later when several grids on a single page will be
    // approved. In that case URL will be like
    // /grid?search[1]=key&mars_format[1]=bla&search[2]=key2&mars_format[2]=bla
    // where [1] and [2] are grid ids generated by the following static logic.
    $this->gridId = &drupal_static('mars_search_grid_id');
    $this->gridId = $this->gridId ? ++$this->gridId : 1;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Getting all GET parameters in array.
    $query_parameters = $this->searchHelper->request->query->all();

    // Initializing grid options array.
    // It is needed to pass preset filters to autocomplete.
    $grid_options = [
      'grid_id' => $this->gridId,
      'filters' => [],
    ];

    $config = $this->getConfiguration();
    $build['#items'] = [];

    // Getting default search options.
    $searchOptions = $this->searchQueryParser->parseQuery($this->gridId);

    if (empty($query_parameters['see-all'])) {
      // We need only 8 items to show initially.
      // Parse query will trim limit in case of see all.
      // But initial results count needs to be 8 instead of configured default.
      $searchOptions['limit'] = 4;
    }

    // Adjusting them with grid specific configuration.
    // Content type filter.
    if (!empty($config['content_type'])) {
      $searchOptions['conditions'][] = ['type', $config['content_type'], '='];
      $grid_options['filters']['type'][$this->gridId] = $config['content_type'];
      $grid_options['filters']['options_logic'] = !empty($config['general_filters']['options_logic']) ? $config['general_filters']['options_logic'] : 'and';
    }

    // Populate top results items before other results.
    if (!empty($config['top_results_wrapper']['top_results'])) {
      $top_result_ids = [];
      foreach ($config['top_results_wrapper']['top_results'] as $top_result) {
        $top_result_ids[] = $top_result['target_id'];
      }
      $build['#attached']['drupalSettings']['cards'][$this->gridId]['topResults'] = $top_result_ids;
      foreach ($this->entityTypeManager->getStorage('node')->loadMultiple($top_result_ids) as $top_result_node) {
        $build['#items'][] = $this->nodeViewBuilder->view($top_result_node, 'card');
      }
      // Adjusting query options to consider top results.
      // Adjusting limit.
      $searchOptions['limit'] = $searchOptions['limit'] - count($top_result_ids);
      // Excluding top results ids from query.
      $searchOptions['conditions'][] = ['nid', $top_result_ids, 'NOT IN'];
    }

    // After this line $facetOptions and $searchOptions become different.
    $facetOptions = $searchOptions;
    unset($facetOptions['limit']);

    // Taxonomy preset filter(s).
    // Adding them only if facets are disabled.
    if (empty($config['exposed_filters_wrapper']['toggle_filters'])) {
      foreach ($config['general_filters'] as $filter_key => $filter_value) {
        if (!empty($filter_value['select'])) {
          $grid_options['filters'][$filter_key][$this->gridId] = implode(',', $filter_value['select']);

          $searchOptions['conditions'][] = [
            $filter_key,
            $filter_value['select'],
            'IN',
          ];
        }
      }
      $searchOptions['options_logic'] = !empty($config['general_filters']['options_logic']) ? $config['general_filters']['options_logic'] : 'and';
    }

    // Getting and building search results.
    $query_search_results = $this->searchHelper->getSearchResults($searchOptions, "grid_{$this->gridId}");
    $build['#attached']['drupalSettings']['cards'][$this->gridId]['searchOptions'] = $searchOptions;
    if ($query_search_results['resultsCount'] == 0) {
      $build['#no_results'] = $this->getSearchNoResult($searchOptions['keys']);
    }
    foreach ($query_search_results['results'] as $node) {
      $build['#items'][] = $this->nodeViewBuilder->view($node, 'card');
    }

    // Populating search form.
    if (!empty($config['exposed_filters_wrapper']['toggle_search'])) {
      // Preparing search form.
      $build['#input_form'] = $this->formBuilder->getForm(SearchForm::class, TRUE, $grid_options);
    }
    // Populating filters.
    // Save results for query before facets load.
    $query_results_count = $query_search_results['resultsCount'];
    if (!empty($config['exposed_filters_wrapper']['toggle_filters'])) {
      $query_search_results = $this->searchHelper->getSearchResults($facetOptions, "grid_{$this->gridId}_facets");
      [$build['#applied_filters_list'], $build['#filters']] = $this->searchHelper->processTermFacets($query_search_results['facets'], self::TAXONOMY_VOCABULARIES, $this->gridId);
    }

    // Output See all only if we have enough results.
    if ($query_search_results['resultsCount'] > count($build['#items'])) {
      $url = $this->searchHelper->getCurrentUrl();
      $url_options = $url->getOptions();
      $url_options['query']['see-all'] = 1;
      $url->setOptions($url_options);
      $build['#ajax_card_grid_link_text'] = $this->t('See all');
    }

    $build['#ajax_card_grid_heading'] = $config['title'];
    $build['#data_layer'] = [
      'grid_id' => $this->gridId,
      'grid_name' => $config['title'],
      'search_term' => $searchOptions['keys'],
      'search_results' => $query_results_count,
    ];
    $build['#graphic_divider'] = $this->themeConfiguratorParser->getGraphicDivider();
    $build['#theme_styles'] = 'drupal';
    $build['#theme'] = 'mars_search_grid_block';
    $build['#attached']['library'][] = 'mars_search/datalayer.card_grid';
    $build['#attached']['library'][] = 'mars_search/see_all_cards';

    return $build;
  }

  /**
   * Render search no result block.
   */
  private function getSearchNoResult($key) {
    $config = $this->configFactory->get('mars_search.search_no_results');
    return [
      '#no_results_heading' => str_replace('@keys', $key, $config->get('no_results_heading')),
      '#no_results_text' => $config->get('no_results_text'),
      '#theme' => 'mars_search_no_results',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['title'] = [
      '#title' => $this->t('Title'),
      '#type' => 'textfield',
      '#size' => 55,
      '#required' => TRUE,
      '#default_value' => $config['title'] ?? $this->t('All products'),
    ];

    $form['content_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Content type'),
      '#options' => self::CONTENT_TYPES,
      '#default_value' => $config['content_type'] ?? NULL,
      '#required' => TRUE,
    ];

    $form = array_merge($form, $this->buildExposedFilters());
    $form = array_merge($form, $this->buildGeneralFilters());
    $form = array_merge($form, $this->buildTopResults());

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->cleanValues()->getValues();

    // Disable default label to display.
    $values['label_display'] = FALSE;

    $this->setConfiguration($values);
  }

  /**
   * Build fieldset for predefined filters.
   *
   * @return array
   *   Selectors for filters.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function buildGeneralFilters() {
    $form = [];
    $config = $this->getConfiguration();

    $form['general_filters'] = [
      '#type' => 'details',
      '#title' => $this->t('Predefined filters'),
      '#open' => FALSE,
      '#states' => [
        'visible' => [
          ':input[name="settings[exposed_filters_wrapper][toggle_filters]"]' => ['checked' => FALSE],
        ],
      ],
    ];

    foreach (self::TAXONOMY_VOCABULARIES as $vocabulary => $vocabulary_data) {
      $label = $vocabulary_data['label'];
      /** @var \Drupal\taxonomy\TermInterface[] $terms */
      $terms = $this->entityTypeManager->getStorage('taxonomy_term')
        ->loadTree($vocabulary, 0, NULL, TRUE);
      if (!$terms) {
        continue;
      }

      $terms_options = [];
      foreach ($terms as $term) {
        $terms_options[$term->id()] = $term->label();
      }

      $conditions = [];
      foreach ($vocabulary_data['content_types'] as $content_type) {
        $conditions[] = [":input[name=\"settings[content_type]\"]" => ['value' => $content_type]];
      }

      $form['general_filters'][$vocabulary] = [
        '#type' => 'details',
        '#title' => $label,
        '#open' => FALSE,
        '#states' => [
          'enabled' => $conditions,
        ],
      ];
      $form['general_filters'][$vocabulary]['select'] = [
        '#type' => 'select',
        '#title' => $label,
        '#multiple' => TRUE,
        '#options' => $terms_options,
        '#default_value' => $config['general_filters'][$vocabulary]['select'] ?? NULL,
      ];
    }
    $form['general_filters']['options_logic'] = [
      '#type' => 'select',
      '#title' => $this->t('Logic operator'),
      '#description' => $this->t('AND filters are exclusive and narrow the result set. OR filters are inclusive and widen the result set.'),
      '#options' => [
        'and' => $this->t('AND'),
        'or' => $this->t('OR'),
      ],
      '#default_value' => $config['general_filters']['options_logic'] ?? 'and',
    ];

    return $form;
  }

  /**
   * Builds top results form element selection.
   *
   * @return array
   *   Top results form elements.
   */
  protected function buildTopResults() {
    $config = $this->getConfiguration();

    // Get default values.
    $default_top = $config['top_results_wrapper']['top_results'] ?? [];
    $default_top_ids = [];
    foreach ($default_top as $id) {
      $default_top_ids[] = array_shift($id);
    }
    $default_top = $default_top_ids ? $this->entityTypeManager->getStorage('node')
      ->loadMultiple($default_top_ids) : NULL;

    $form = [];
    $form['top_results_wrapper'] = [
      '#type' => 'details',
      '#title' => $this->t('Top results'),
      '#open' => FALSE,
    ];
    $form['top_results_wrapper']['top_results'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Top results'),
      '#selection_settings' => [
        'target_bundles' => array_keys(self::CONTENT_TYPES),
      ],
      '#tags' => TRUE,
      '#cardinality' => 8,
      '#default_value' => $default_top,
    ];

    return $form;
  }

  /**
   * Builds exposed filters form element.
   *
   * @return array
   *   Exposed form elements.
   */
  protected function buildExposedFilters() {
    // TODO Add filters configuration.
    $form = [];
    $config = $this->getConfiguration();

    $form['exposed_filters_wrapper'] = [
      '#type' => 'details',
      '#title' => $this->t('Exposed filters'),
      '#open' => TRUE,
    ];

    $form['exposed_filters_wrapper']['toggle_search'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable text search bar'),
      '#description' => $this->t('If enabled a text search bar appears on the grid.'),
      '#default_value' => $config['exposed_filters_wrapper']['toggle_search'] ?? FALSE,
    ];
    $form['exposed_filters_wrapper']['toggle_filters'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable exposed search filters'),
      '#description' => $this->t('If enabled search filters by taxonomy fields appear on the grid.'),
      '#default_value' => $config['exposed_filters_wrapper']['toggle_filters'] ?? FALSE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
