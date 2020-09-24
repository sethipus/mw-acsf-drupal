<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class GridBlock.
 *
 * @Block(
 *   id = "grid_block",
 *   admin_label = @Translation("Grid block"),
 *   category = @Translation("Grid")
 * )
 *
 * @package Drupal\mars_search\Plugin\Block
 */
class GridBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * List of vocabularies which are included in indexing.
   *
   * @var array
   */
  const TAXONOMY_VOCABULARIES = [
    'mars_brand_initiatives' => [
      'label' => 'Brand initiatives',
      'content_types' => ['article'],
    ],
    'mars_flavor' => [
      'label' => 'Flavor',
      'content_types' => ['product', 'product_multipack'],
    ],
    'mars_format' => [
      'label' => 'Format',
      'content_types' => ['product', 'product_multipack'],
    ],
    'mars_occasions' => [
      'label' => 'Occasions',
      'content_types' => ['article', 'product', 'product_multipack'],
    ],
  ];

  /**
   * List of content types which are included in indexing.
   *
   * @var array
   */
  const CONTENT_TYPES = [
    'product' => 'Product',
    'product_multipack' => 'Product multipack',
    'article' => 'Article',
    'recipe' => 'Recipe',
  ];

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // TODO: Implement build() method.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['title'] = [
      '#title' => $this->t('Title'),
      '#type' => 'textfield',
      '#size' => 35,
      '#required' => TRUE,
      '#default_value' => $config['title'] ?? $this->t('All products'),
    ];

    $form['no_results_heading'] = [
      '#title' => $this->t('Heading for no results case'),
      '#default_value' => $config['no_results_heading'] ?? $this->t('There are no matching results for'),
      '#type' => 'textfield',
      '#size' => 35,
      '#required' => TRUE,
    ];
    $form['no_results_text'] = [
      '#title' => $this->t('Text for no results case'),
      '#default_value' => $config['no_results_text'] ?? $this->t('Please try entering a different search'),
      '#type' => 'textfield',
      '#size' => 50,
      '#required' => TRUE,
    ];

    $form['content_type'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content type'),
      '#multiple' => TRUE,
      '#options' => self::CONTENT_TYPES,
      '#default_value' => $config['content_type'] ?? NULL,
    ];

    $form = array_merge($form, $this->buildExposedFilters());
    $form = array_merge($form, $this->buildTopResults());
    $form = array_merge($form, $this->buildGeneralFilters());

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    // TODO Validate that top list fits content type and other filters?
    // TODO Test that top list cardinality is respected.
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
        $conditions[] = [":input[name=\"settings[content_type][{$content_type}]\"]" => ['checked' => TRUE]];
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
      $form['general_filters'][$vocabulary]['options_logic'] = [
        '#type' => 'select',
        '#title' => $this->t('Operator for %vocabulary options', ['%vocabulary' => $label]),
        '#description' => $this->t('AND filters are exclusive and narrow the result set. OR filters are inclusive and widen the result set.'),
        '#options' => [
          'and' => $this->t('AND'),
          'or' => $this->t('OR'),
        ],
        '#default_value' => $config['general_filters'][$vocabulary]['options_logic'] ?? NULL,
        '#required' => TRUE,
      ];
      $form['general_filters'][$vocabulary]['facet_logic'] = [
        '#type' => 'select',
        '#title' => $this->t('Behavior for %vocabulary facet', ['%vocabulary' => $label]),
        '#description' => $this->t('AND filters are exclusive and narrow the result set. OR filters are inclusive and widen the result set.'),
        '#options' => [
          'and' => $this->t('AND'),
          'or' => $this->t('OR'),
        ],
        '#default_value' => $config['general_filters'][$vocabulary]['facet_logic'] ?? NULL,
        '#required' => TRUE,
      ];
    }

    return $form;
  }

  /**
   * Builds top results form element selection.
   *
   * @return array
   *   Top results form elements.
   */
  protected function buildTopResults() {
    // TODO add default values.
    // TODO find better widget.
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
    // A facet block cannot be cached, because it must always match the current
    // search results, and Search API gets those search results from a data
    // source that can be external to Drupal. Therefore it is impossible to
    // guarantee that the search results are in sync with the data managed by
    // Drupal. Consequently, it is not possible to cache the search results at
    // all. If the search results cannot be cached, then neither can the facets,
    // because they must always match.
    // Fortunately, facet blocks are rendered using a lazy builder (like all
    // blocks in Drupal), which means their rendering can be deferred (unlike
    // the search results, which are the main content of the page, and deferring
    // their rendering would mean sending an empty page to the user). This means
    // that facet blocks can be rendered and sent *after* the initial page was
    // loaded, by installing the BigPipe (big_pipe) module.
    //
    // When BigPipe is enabled, the search results will appear first, and then
    // each facet block will appear one-by-one, in DOM order.
    // See https://www.drupal.org/project/big_pipe.
    //
    // In a future version of Facet API, this could be refined, but due to the
    // reliance on external data sources, it will be very difficult if not
    // impossible to improve this significantly.
    //
    // Note: when using Drupal core's Search module instead of the contributed
    // Search API module, the above limitations do not apply, but for now it is
    // not considered worth the effort to optimize this just for Drupal core's
    // Search.
    return 0;
  }

}
