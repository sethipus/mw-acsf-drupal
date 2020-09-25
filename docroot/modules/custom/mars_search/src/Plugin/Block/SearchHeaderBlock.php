<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Link;
use Drupal\mars_search\Form\SearchForm;
use Drupal\mars_search\SearchHelperInterface;
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
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * Search helper.
   *
   * @var \Drupal\mars_search\SearchHelperInterface
   */
  protected $searchHelper;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('mars_search.search_helper'),
      $container->get('form_builder')
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
    SearchHelperInterface $search_helper,
    FormBuilderInterface $form_builder
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->searchHelper = $search_helper;
    $this->formBuilder = $form_builder;
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
    $build['#input_form']['search']['#attributes']['placeholder'] = $this->t('Search products, recipes, articles...');

    // Getting search results from SOLR.
    $query_search_results = $this->searchHelper->getSearchResults([], 'main_search');

    // Preparing content type facet filter.
    $type_facet_key = 'type';
    $search_filters = [];
    if (!empty($query_search_results['facets'][$type_facet_key])) {
      foreach ($query_search_results['facets'][$type_facet_key] as $type_facet) {
        $url = $this->searchHelper->getCurrentUrl();
        $url_options = $url->getOptions();
        // That means facet is active.
        if ($this->searchHelper->request->query->get($type_facet_key) == $type_facet['filter']) {
          // Removing facet query from active filter to allow deselect it.
          unset($url_options['query'][$type_facet_key]);
        }
        else {
          // Adding facet filter to the query.
          $url_options['query'][$type_facet_key] = $type_facet['filter'];
        }
        $url->setOptions($url_options);

        $search_filters[] = [
          'title' => Link::fromTextAndUrl($type_facet['filter'], $url),
          'count' => $type_facet['count'],
        ];
      }
    }

    $build['#search_filters'] = $search_filters;
    $build['#search_header_heading'] = $conf['search_header_heading'] ?? $this->t('What are you looking for?');
    $build['#brand_shape'] = $this->themeConfiguratorParser->getFileWithId('brand_borders', 'search-header-border');
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
      '#maxlength' => 2048,
      '#required' => TRUE,
      '#default_value' => $config['search_header_heading'] ?? '',
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
