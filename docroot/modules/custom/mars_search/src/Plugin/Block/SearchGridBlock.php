<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_search\SearchProcessFactoryInterface;
use Drupal\mars_search\Processors\SearchBuilderInterface;

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
   * The language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

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
      $container->get('mars_search.search_factory'),
      $container->get('mars_common.language_helper')
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
    SearchProcessFactoryInterface $searchProcessor,
    LanguageHelper $language_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->searchProcessor = $searchProcessor;
    $this->searchHelper = $this->searchProcessor->getProcessManager('search_helper');
    $this->searchBuilder = $this->searchProcessor->getProcessManager('search_builder');
    $this->languageHelper = $language_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    if (!isset($config['grid_id']) || !$config['grid_id']) {
      $grid_id = uniqid(substr(md5(serialize($config)), 0, 12));
      $config['grid_id'] = $grid_id;
      $this->setConfiguration($config);
    }
    else {
      $grid_id = $config['grid_id'];
    }
    [$searchOptions, $query_search_results, $build] = $this->searchBuilder->buildSearchResults('grid', $config, $grid_id);
    $build = array_merge($build, $this->searchBuilder->buildSearchFacets($config, $grid_id));

    // "See more" link should be visible only if it makes sense.
    $build['#ajax_card_grid_link_text'] = $this->languageHelper->translate('See more');
    $build['#ajax_card_grid_link_attributes']['href'] = '/';
    if ($query_search_results['resultsCount'] > count($build['#items'])) {
      $build['#ajax_card_grid_link_attributes']['class'] = 'active';
    }

    $build['#ajax_card_grid_heading'] = $this->languageHelper->translate($config['title']);
    $build['#data_layer'] = [
      'page_id' => $this->getContextValue('node')->id(),
      'grid_id' => $grid_id,
      'grid_name' => $config['title'],
      'search_term' => $searchOptions['keys'],
      'search_results' => $query_search_results['resultsCount'],
    ];
    $build['#graphic_divider'] = $this->themeConfiguratorParser->getGraphicDivider();
    $build['#brand_border'] = $this->themeConfiguratorParser->getBrandBorder2();
    $build['#theme_styles'] = 'drupal';
    $build['#theme'] = 'mars_search_grid_block';
    $build['#attached']['library'][] = 'mars_search/datalayer.card_grid';
    $build['#attached']['library'][] = 'mars_search/search_pager';
    $build['#attached']['library'][] = 'mars_search/autocomplete';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['title'] = [
      '#title' => $this->languageHelper->translate('Title'),
      '#type' => 'textfield',
      '#size' => 55,
      '#required' => TRUE,
      '#default_value' => $config['title'] ?? $this->languageHelper->translate('All products'),
    ];

    $form['content_type'] = [
      '#type' => 'radios',
      '#title' => $this->languageHelper->translate('Content type'),
      '#options' => SearchBuilderInterface::CONTENT_TYPES,
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
      '#title' => $this->languageHelper->translate('Predefined filters'),
      '#open' => FALSE,
      '#states' => [
        'visible' => [
          ':input[name="settings[exposed_filters_wrapper][toggle_filters]"]' => ['checked' => FALSE],
        ],
      ],
    ];

    foreach (SearchBuilderInterface::TAXONOMY_VOCABULARIES as $vocabulary => $vocabulary_data) {
      $label = $vocabulary_data['label'];
      /** @var \Drupal\taxonomy\TermStorageInterface $term_storage */
      $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
      /** @var \Drupal\taxonomy\TermInterface[] $terms */
      $terms = $term_storage->loadTree($vocabulary, 0, NULL, TRUE);
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
      '#title' => $this->languageHelper->translate('Logic operator'),
      '#description' => $this->languageHelper->translate('AND filters are exclusive and narrow the result set. OR filters are inclusive and widen the result set.'),
      '#options' => [
        'and' => $this->languageHelper->translate('AND'),
        'or' => $this->languageHelper->translate('OR'),
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
      '#title' => $this->languageHelper->translate('Top results'),
      '#open' => FALSE,
    ];
    $form['top_results_wrapper']['top_results'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->languageHelper->translate('Top results'),
      '#selection_settings' => [
        'target_bundles' => array_keys(SearchBuilderInterface::CONTENT_TYPES),
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
      '#title' => $this->languageHelper->translate('Exposed filters'),
      '#open' => TRUE,
    ];

    $form['exposed_filters_wrapper']['toggle_search'] = [
      '#type' => 'checkbox',
      '#title' => $this->languageHelper->translate('Enable text search bar'),
      '#description' => $this->languageHelper->translate('If enabled a text search bar appears on the grid.'),
      '#default_value' => $config['exposed_filters_wrapper']['toggle_search'] ?? FALSE,
    ];
    $form['exposed_filters_wrapper']['toggle_filters'] = [
      '#type' => 'checkbox',
      '#title' => $this->languageHelper->translate('Enable exposed search filters'),
      '#description' => $this->languageHelper->translate('If enabled search filters by taxonomy fields appear on the grid.'),
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

  /**
   * {@inheritdoc}
   */
  public function getContextMapping() {
    $mapping = parent::getContextMapping();
    $mapping['node'] = $mapping['node'] ?? 'layout_builder.entity';
    return $mapping;
  }

}
