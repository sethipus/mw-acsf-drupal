<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_search\SearchProcessFactoryInterface;

/**
 * Class SearchGridBlock.
 *
 * @Block(
 *   id = "search_grid_block",
 *   admin_label = @Translation("MARS: Search Grid block"),
 *   category = @Translation("Mars Search"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Current Node"))
 *   }
 * )
 *
 * @package Drupal\mars_search\Plugin\Block
 */
class SearchGridBlock extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

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
   * Templates builder service .
   *
   * @var \Drupal\mars_search\Processors\SearchBuilder
   */
  protected $searchBuilder;

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('entity_type.manager')->getViewBuilder('node'),
      $container->get('config.factory'),
      $container->get('mars_search.search_factory')
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
    ThemeConfiguratorParser $themeConfiguratorParser,
    EntityViewBuilderInterface $node_view_builder,
    ConfigFactoryInterface $configFactory,
    SearchProcessFactoryInterface $searchProcessor
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->nodeViewBuilder = $node_view_builder;
    $this->configFactory = $configFactory;
    $this->searchProcessor = $searchProcessor;
    $this->searchQueryParser = $this->searchProcessor->getProcessManager('search_query_parser');
    $this->searchHelper = $this->searchProcessor->getProcessManager('search_helper');
    $this->searchTermFacetProcess = $this->searchProcessor->getProcessManager('search_facet_process');
    $this->searchBuilder = $this->searchProcessor->getProcessManager('search_builder');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    if (!$config['grid_id']) {
      $grid_id = uniqid(substr(md5(serialize($config)), 0, 12));
      $config['grid_id'] = $grid_id;
      $this->setConfiguration($config);
    }
    else {
      $grid_id = $config['grid_id'];
    }
    [$searchOptions, $query_search_results, $build] = $this->searchBuilder->buildSearchResults('grid', $config, $grid_id);

    // Populating search form.
    if (!empty($config['exposed_filters_wrapper']['toggle_search'])) {
      // Preparing search form.
      $build['#input_form'] = [
        '#type' => 'textfield',
        '#attributes' => [
          'placeholder' => $this->t('Search'),
          'class' => ['search-input__field', 'mars-autocomplete-field'],
          'data-grid-id' => $grid_id,
          'autocomplete' => 'off',
        ],
        '#value' => $searchOptions['keys'],
      ];
    }

    // After this line $facetOptions and $searchOptions become different.
    $facetOptions = $searchOptions;
    unset($facetOptions['limit']);
    // Populating filters.
    // Save results for query before facets load.
    $query_results_count = $query_search_results['resultsCount'];
    if (!empty($config['exposed_filters_wrapper']['toggle_filters'])) {
      $query_search_results = $this->searchHelper->getSearchResults($facetOptions, "grid_{$grid_id}_facets");
      [$build['#applied_filters_list'], $build['#filters']] = $this->searchTermFacetProcess->processFilter($query_search_results['facets'], self::TAXONOMY_VOCABULARIES, $grid_id);
    }

    // Output See all only if we have enough results.
    if ($query_search_results['resultsCount'] > count($build['#items'])) {
      $url = $this->searchHelper->getCurrentUrl();
      $url_options = $url->getOptions();
      $url_options['query']['see-all'] = 1;
      $url->setOptions($url_options);
      $build['#ajax_card_grid_link_text'] = $this->t('See all');
      $build['#ajax_card_grid_link_attributes']['href'] = $url->toString();
    }

    $build['#ajax_card_grid_heading'] = $config['title'];
    $build['#data_layer'] = [
      'page_id' => $this->getContextValue('node')->id(),
      'grid_id' => $grid_id,
      'grid_name' => $config['title'],
      'search_term' => $searchOptions['keys'],
      'search_results' => $query_results_count,
    ];
    $build['#attached']['drupalSettings']['cards'][$grid_id]['contentType'] = $config['content_type'];
    $build['#graphic_divider'] = $this->themeConfiguratorParser->getGraphicDivider();
    $build['#brand_border'] = $this->themeConfiguratorParser->getBrandBorder2();
    $build['#theme_styles'] = 'drupal';
    $build['#theme'] = 'mars_search_grid_block';
    $build['#attached']['library'][] = 'mars_search/datalayer.card_grid';
    $build['#attached']['library'][] = 'mars_search/see_all_cards';
    $build['#attached']['library'][] = 'mars_search/autocomplete';

    return $build;
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
    $values['grid_id'] = uniqid(substr(md5(serialize($values)), 0, 12));

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
