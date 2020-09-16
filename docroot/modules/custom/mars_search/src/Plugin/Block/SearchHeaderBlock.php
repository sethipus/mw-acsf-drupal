<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\facets\Result\Result;
use Drupal\views\ViewExecutableFactory;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mars_common\ThemeConfiguratorParser;

/**
 * Provides a search page header block.
 *
 * @Block(
 *   id = "search_header_block",
 *   admin_label = @Translation("MARS: Search page header"),
 *   category = @Translation("Mars Search")
 * )
 */
class SearchHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The View executable object.
   *
   * @var \Drupal\views\ViewExecutable
   */
  protected $view;

  /**
   * The facet manager.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  protected $facetManager;

  /**
   * The entity storage used for facets.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $facetStorage;

  /**
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('views.executable'),
      $container->get('entity_type.manager')->getStorage('view'),
      $container->get('facets.manager'),
      $container->get('entity_type.manager')->getStorage('facets_facet'),
      $container->get('mars_common.theme_configurator_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ViewExecutableFactory $executable_factory,
    EntityStorageInterface $storage,
    DefaultFacetManager $facet_manager,
    EntityStorageInterface $facet_storage,
    ThemeConfiguratorParser $themeConfiguratorParser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $view = $storage->load('acquia_search');
    if (!empty($view)) {
      $this->view = $executable_factory->get($view);
      $this->view->setDisplay('page');
    }
    $this->facetManager = $facet_manager;
    $this->facetStorage = $facet_storage;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();
    if (empty($this->view) || !($facet = $this->facetStorage->load($conf['search_facet']))) {
      return [];
    }

    // No need to build the facet if it does not need to be visible.
    if ($facet->getOnlyVisibleWhenFacetSourceIsVisible() &&
      (!$facet->getFacetSource() || !$facet->getFacetSource()->isRenderedInCurrentRequest())) {
      return [];
    }

    $build = [];
    $build['#input_form'] = $this->view->display_handler->viewExposedFormBlocks();
    $build['#input_form']['search']['#attributes']['class'][] = 'search-input__field';
    $build['#input_form']['search']['#title_display'] = 'none';
    $build['#input_form']['search']['#placeholder'] = $this->t('Search products, recipes, articles...');
    unset($build['#input_form']['actions']['submit']);

    $search_results = [];

    /** @var \Drupal\facets\Entity\Facet $result_facet */
    $result_facet = $this->facetManager->build($facet)[0]['#facet'] ?? NULL;
    if ($result_facet && $result_facet->getWidget()['type'] == 'links') {
      $search_results = array_map(function (Result $result) {
        return [
          'title' => Link::fromTextAndUrl($result->getDisplayValue(), $result->getUrl()),
          'count' => $result->getCount(),
        ];
      }, $result_facet->getResults());
    }

    $build['#search_results'] = $search_results;
    $build['#search_header_heading'] = $conf['search_header_heading'] ?? $this->t('What are you looking for?');
    $build['#brand_shape'] = $this->themeConfiguratorParser->getFileWithId('brand_borders', 'search-header-border');
    $build['#theme'] = 'mars_search_header';

    return $build;
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

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();
    $all_facets = $this->facetStorage->loadMultiple();
    $options = [];
    foreach ($all_facets as $key => $facet) {
      $options[$key] = $facet->getName();
    }

    $form['search_header_heading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search heading title'),
      '#maxlength' => 2048,
      '#required' => TRUE,
      '#default_value' => $config['search_header_heading'] ?? '',
    ];

    $form['search_facet'] = [
      '#type' => 'select',
      '#title' => $this->t('Search facet type'),
      '#default_value' => $config['search_facet'] ?? '',
      '#options' => $options,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * This method processes the blockForm() form fields when the block
   * configuration form is submitted.
   *
   * The blockValidate() method can be used to validate the form submission.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues());
  }

}
