<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_common\Traits\OverrideThemeTextColorTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_search\SearchProcessFactoryInterface;
use Drupal\mars_search\Processors\SearchCategoriesInterface;

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

  use OverrideThemeTextColorTrait;

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
    $this->searchCategories = $this->searchProcessor->getProcessManager('search_categories');
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

    // Results will be populated after ajax request. It's not possible to
    // know right desktop type without page inner width.
    $build['#items'] = [];
    $query_search_results['results'] = [];
    $build = array_merge($build, $this->searchBuilder->buildSearchFacets('grid', $config, $grid_id));

    // "See more" link should be visible only if it makes sense.
    $build['#ajax_card_grid_link_text'] = $this->languageHelper->translate(strtoupper('See more'));
    $build['#ajax_card_grid_link_attributes']['href'] = '/';
    if ($query_search_results['resultsCount'] > count($build['#items'])) {
      $build['#ajax_card_grid_link_attributes']['class'] = 'active';
    }
    // Extracting the node context.
    $context_node = $this->getContextValue('node');

    $build['#ajax_card_grid_heading'] = $this->languageHelper->translate($config['title']);
    $build['#data_layer'] = [
      'page_id' => $context_node->id(),
      'page_revision_id' => $context_node->getRevisionId(),
      'grid_id' => $grid_id,
      'grid_name' => $config['title'],
      'search_term' => $searchOptions['keys'],
      'search_results' => $query_search_results['resultsCount'],
    ];
    $build['#graphic_divider'] = $this->themeConfiguratorParser->getGraphicDivider();
    $file_border_content = $this->themeConfiguratorParser->getBrandBorder2();
    $build['#brand_border'] = !empty($config['with_brand_borders']) ? $file_border_content : NULL;
    $build['#filter_title_transform'] = $this->themeConfiguratorParser->getSettingValue('facets_text_transform', 'uppercase');
    $build['#theme_styles'] = 'drupal';
    $build['#theme'] = 'mars_search_grid_block';
    $build['#attached']['library'][] = 'mars_search/datalayer.card_grid';
    $build['#attached']['library'][] = 'mars_search/search_pager';
    $build['#attached']['library'][] = 'mars_search/autocomplete';
    $build['#text_color_override'] = FALSE;
    if (!empty($config['override_text_color']['override_color'])) {
      $build['#text_color_override'] = static::$overrideColor;
    }
    if (!empty($config['override_text_color']['override_filter_title_color'])) {
      $build['#override_filter_title_color'] = static::$overrideColor;
    }

    $build['#overlaps_previous'] = $config['overlaps_previous'] ?? NULL;
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
      '#options' => SearchCategoriesInterface::CONTENT_TYPES,
      '#default_value' => $config['content_type'] ?? NULL,
      '#required' => TRUE,
    ];

    $form = array_merge($form, $this->buildExposedFilters());
    $form = array_merge($form, $this->buildGeneralFilters());
    $form = array_merge($form, $this->buildExcludedFilters());
    $form = array_merge($form, $this->buildTopResults());

    $this->buildOverrideColorElement($form, $config, TRUE);

    $form['with_brand_borders'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('With/without brand border'),
      '#default_value' => $config['with_brand_borders'] ?? FALSE,
    ];

    $form['overlaps_previous'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('With/without overlaps previous'),
      '#default_value' => $config['overlaps_previous'] ?? FALSE,
    ];

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
    // Process Top Results.
    $values['top_results_wrapper']['top_results'] = [];
    foreach ($values['top_results_wrapper'] as $key => $results) {
      if ($key == 'top_results') {
        continue;
      }
      if ($results == NULL) {
        unset($values['top_results_wrapper'][$key]);
        continue;
      }
      $values['top_results_wrapper']['top_results'][] = reset($results);
      unset($values['top_results_wrapper'][$key]);
    }

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
    ];

    foreach ($this->searchCategories->getCategories() as $vocabulary => $vocabulary_data) {
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
   * Build fieldset for excluded filters.
   *
   * @return array
   *   Selectors for filters.
   */
  protected function buildExcludedFilters() {
    $form = [];
    $config = $this->getConfiguration();

    $form['exclude_filters'] = [
      '#type' => 'details',
      '#title' => $this->languageHelper->translate('Exclude filters'),
      '#open' => FALSE,
    ];

    $exclude_options = [];
    foreach ($this->searchCategories->getCategories() as $vocabulary => $vocabulary_data) {
      $label = $vocabulary_data['label'];
      /** @var \Drupal\taxonomy\TermStorageInterface $term_storage */
      $exclude_options[$vocabulary] = $label;
    }

    $form['exclude_filters']['filters'] = [
      '#type' => 'checkboxes',
      'label' => $this->t('Filters to exclude'),
      '#options' => $exclude_options,
      '#default_value' => $config['exclude_filters']['filters'] ?? NULL,
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
    for ($i = 1; $i < 9; $i++) {
      $form['top_results_wrapper']['top_results_' . $i] = [
        '#type' => 'entity_autocomplete',
        '#target_type' => 'node',
        '#title' => $this->languageHelper->translate('Top results'),
        '#selection_settings' => [
          'target_bundles' => array_keys(SearchCategoriesInterface::CONTENT_TYPES),
        ],
        '#tags' => TRUE,
        '#cardinality' => 1,
        '#default_value' => is_array($default_top) && count($default_top) > 0 ? array_shift($default_top) : [],
      ];
      if ($i > 1) {
        $prev = $i - 1;
        $form['top_results_wrapper']['top_results_' . $i]['#states'] = [
          'visible' => [
            ":input[name = 'settings[top_results_wrapper][top_results_$prev]']" => [
              'filled' => TRUE,
            ],
          ],
        ];
      }
    }

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
