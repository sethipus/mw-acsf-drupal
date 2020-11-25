<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_search\Form\SearchForm;
use Drupal\mars_search\SearchHelperInterface;
use Drupal\mars_search\SearchQueryParserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

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
   * Mars Search logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Language helper service.
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
      $container->get('mars_search.search_helper'),
      $container->get('form_builder'),
      $container->get('mars_search.search_query_parser'),
      $container->get('logger.factory')->get('mars_search'),
      $container->get('config.factory'),
      $container->get('current_route_match'),
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
    SearchHelperInterface $search_helper,
    FormBuilderInterface $form_builder,
    SearchQueryParserInterface $search_query_parser,
    LoggerInterface $logger,
    ConfigFactoryInterface $configFactory,
    RouteMatchInterface $route_match,
    LanguageHelper $language_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->searchHelper = $search_helper;
    $this->formBuilder = $form_builder;
    $this->searchQueryParser = $search_query_parser;
    $this->logger = $logger;
    $this->configFactory = $configFactory;
    $this->routeMatch = $route_match;
    $this->languageHelper = $language_helper;
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
    $faq_facet_key = 'faq_filter_topic';

    $options = $this->searchQueryParser->parseQuery();
    // Overriding some default options with FAQ specific values.
    // Overriding first condition from SearchQueryParser::getDefaultOptions().
    $options['conditions'][0] = ['type', 'faq', '=', TRUE];

    $options['limit'] = 4;
    $options['sort'] = [
      'faq_item_queue_weight' => 'ASC',
      'created' => 'DESC',
    ];

    // That means filter topic filter is active.
    if ($this->searchHelper->request->get($faq_facet_key)) {
      // Disabling entityqueue sorting when topic filter is active.
      unset($options['sort']['faq_item_queue_weight']);
    }
    $search_results = $this->searchHelper->getSearchResults($options);
    $faq_items = [];
    $cta_button_label = $cta_button_link = '';

    if ($search_results['results']) {
      /** @var \Drupal\node\NodeInterface $search_result */
      foreach ($search_results['results'] as $row_key => $search_result) {
        // Do not fail page load if search index is not in sync with database.
        if ($search_result->bundle() != 'faq') {
          $search_results['resultsCount']--;

          $this->logger->warning('Node ID %title (ID: %id) is not a FAQ node.', [
            '%id' => $search_result->id(),
            '%title' => $search_result->getTitle(),
          ]);

          continue;
        }

        $question_value = !empty($search_results['highlighted_fields'][$row_key]['field_qa_item_question'][0]) ? $search_results['highlighted_fields'][$row_key]['field_qa_item_question'][0] : $search_result->get('field_qa_item_question')->value;
        $answer_value = !empty($search_results['highlighted_fields'][$row_key]['field_qa_item_answer'][0]) ? $search_results['highlighted_fields'][$row_key]['field_qa_item_answer'][0] : $search_result->get('field_qa_item_answer')->value;

        $faq_items[$row_key]['content'] = [
          'question' => $question_value,
          'answer' => $answer_value,
          'order' => $row_key,
        ];
      }

      if ($search_results['resultsCount'] > count($faq_items)) {
        $cta_button_label = $this->languageHelper->translate('See all');
        $url = $this->searchHelper->getCurrentUrl();
        $url_options = $url->getOptions();
        $url_options['query']['see-all'] = 1;
        $url->setOptions($url_options);
        $cta_button_link = $url->toString();
      }
    }
    // Getting search form.
    // Preparing options for autocomplete.
    $autocomplete_options['filters'] = [
      // FAQ specific flag to distinguish FAQ query.
      'faq' => TRUE,
    ];
    $search_form = $this->formBuilder->getForm(SearchForm::class, TRUE, $autocomplete_options);
    $search_form['#input_form']['search']['#attributes']['class'][] = 'data-layer-search-form-input';

    // Facets query.
    $options['disable_filters'] = TRUE;
    $facets_search_results = $this->searchHelper->getSearchResults($options, 'faq_facets');

    return [
      '#theme' => 'mars_search_faq_block',
      '#faq_title' => $config['faq_title'],
      '#qa_items' => $faq_items,
      '#cta_button_label' => $cta_button_label,
      '#cta_button_link' => $cta_button_link,
      '#search_form' => render($search_form),
      '#search_result_counter' => $search_results['resultsCount'],
      '#search_result_text' => (!empty($options['keys']) && $search_results['resultsCount'] > 0) ? $this->formatPlural($search_results['resultsCount'], 'Result for "@keys"', 'Results for "@keys"', ['@keys' => $options['keys']]) : '',
      '#facets' => $this->searchHelper->prepareFacetsLinks($facets_search_results['facets'][$faq_facet_key], $faq_facet_key),
      '#no_results_heading' => str_replace('@keys', $options['keys'], $config_no_results->get('no_results_heading')),
      '#no_results_text' => $config_no_results->get('no_results_text'),
      '#attached' => [
        'drupalSettings' => [
          'dataLayer' => [
            'searchPage' => 'faq',
            'siteSearchResults' => [
              'siteSearchTerm' => $options['keys'],
              'siteSearchResults' => $search_results['resultsCount'],
            ],
          ],
        ],
        'library' => [
          'mars_search/datalayer.search',
          'mars_search/see_all_cards',
        ],
      ],
    ];
  }

}
