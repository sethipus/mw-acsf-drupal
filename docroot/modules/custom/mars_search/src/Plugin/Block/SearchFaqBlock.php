<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mars_search\SearchProcessFactoryInterface;

/**
 * Provides a no results block for the search page.
 *
 * @Block(
 *   id = "search_faq_block",
 *   admin_label = @Translation("MARS: Search FAQs"),
 *   category = @Translation("Mars Search")
 * )
 */
class SearchFaqBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Search helper.
   *
   * @var \Drupal\mars_search\SearchHelperInterface
   */
  protected $searchHelper;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Templates builder service .
   *
   * @var \Drupal\mars_search\Processors\SearchBuilder
   */
  protected $searchBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_search.search_factory'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    SearchProcessFactoryInterface $searchProcessor,
    ConfigFactoryInterface $configFactory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->searchProcessor = $searchProcessor;
    $this->searchHelper = $this->searchProcessor->getProcessManager('search_helper');
    $this->searchBuilder = $this->searchProcessor->getProcessManager('search_builder');
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['faq_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('FAQ block title'),
      '#maxlength' => 10,
      '#required' => TRUE,
      '#default_value' => $config['faq_title'] ?? 'FAQs',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $config_no_results = $this->configFactory->get('mars_search.search_no_results');

    [$searchOptions, $query_search_results, $build] = $this->searchBuilder->buildSearchResults('faq');
    $build = array_merge($build, $this->searchBuilder->buildFaqFilters());

    $cta_button_label = $this->t('See more');
    $cta_button_link = '/';

    $render_default = [
      '#theme' => 'mars_search_faq_block',
      '#faq_title' => $config['faq_title'],
      '#cta_button_label' => $cta_button_label,
      '#cta_button_link' => $cta_button_link,
      '#search_result_counter' => $query_search_results['resultsCount'],
      '#no_results_heading' => str_replace('@keys', $searchOptions['keys'], $config_no_results->get('no_results_heading')),
      '#no_results_text' => $config_no_results->get('no_results_text'),
      '#data_layer' => [
        'search_term' => $searchOptions['keys'],
        'search_results' => $query_search_results['resultsCount'],
      ],
      '#attached' => [
        'drupalSettings' => [
          'dataLayer' => [
            'searchPage' => 'faq',
            'siteSearchResults' => [
              'siteSearchTerm' => $searchOptions['keys'],
              'siteSearchResults' => $query_search_results['resultsCount'],
            ],
          ],
        ],
        'library' => [
          'mars_search/datalayer_search',
          'mars_search/search_filter_faq',
          'mars_search/search_pager',
        ],
      ],
    ];

    return array_merge($render_default, $build);
  }

}
