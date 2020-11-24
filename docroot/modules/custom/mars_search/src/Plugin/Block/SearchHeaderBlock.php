<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\mars_search\Form\SearchForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_search\Processors\SearchQueryParserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_search\SearchProcessFactoryInterface;

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
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('form_builder'),
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
    ThemeConfiguratorParser $themeConfiguratorParser,
    FormBuilderInterface $form_builder,
    SearchProcessFactoryInterface $searchProcessor
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->formBuilder = $form_builder;
    $this->searchProcessor = $searchProcessor;
    $this->searchQueryParser = $this->searchProcessor->getProcessManager('search_query_parser');
    $this->searchHelper = $this->searchProcessor->getProcessManager('search_helper');
    $this->searchTermFacetProcess = $this->searchProcessor->getProcessManager('search_facet_process');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $conf = $this->getConfiguration();

    // Preparing search form.
    $build['#input_form'] = $this->formBuilder->getForm(SearchForm::class);
    $build['#input_form']['search']['#attributes']['class'][] = 'search-input__field';
    $build['#input_form']['search']['#attributes']['class'][] = 'data-layer-search-form-input';
    $build['#input_form']['search']['#attributes']['placeholder'] = $conf['search_header_placeholder'] ?? $this->t('Search products, recipes, articles...');

    // Getting search results from SOLR.
    $options = $this->searchQueryParser->parseQuery();
    $query_search_results = $this->searchHelper->getSearchResults($options, 'main_search_facets');

    $build['#search_filters'] = $this->searchTermFacetProcess->prepareFacetsLinksWithCount($query_search_results['facets'], 'type', SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID);
    $build['#search_header_heading'] = $conf['search_header_heading'] ?? $this->t('What are you looking for?');
    $build['#brand_shape'] = $this->themeConfiguratorParser->getBrandBorder();
    $build['#theme'] = 'mars_search_header';

    return $build;
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
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['search_header_heading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search heading title'),
      '#maxlength' => 35,
      '#required' => TRUE,
      '#default_value' => $config['search_header_heading'] ?? $this->t('What are you looking for?'),
    ];

    $form['search_header_placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search input hint'),
      '#required' => TRUE,
      '#default_value' => $config['search_header_placeholder'] ?? $this->t('Search products, recipes, articles...'),
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
