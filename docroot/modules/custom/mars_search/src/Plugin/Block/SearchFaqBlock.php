<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
      $container->get('logger.factory')->get('mars_search')
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
    LoggerInterface $logger
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->searchHelper = $search_helper;
    $this->formBuilder = $form_builder;
    $this->searchQueryParser = $search_query_parser;
    $this->logger = $logger;
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

        $question_value = $search_result->get('field_qa_item_question')->value;
        $answer_value = $search_result->get('field_qa_item_answer')->value;

        $faq_items[$row_key]['content'] = [
          'question' => $question_value,
          'answer' => $answer_value,
          'order' => $row_key,
        ];
      }

      if ($search_results['resultsCount'] > count($faq_items)) {
        $cta_button_label = $this->t('See all');
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
    $search_from = $this->formBuilder->getForm(SearchForm::class, TRUE, $autocomplete_options);

    // Facets query.
    $options['disable_filters'] = TRUE;
    $facets_search_results = $this->searchHelper->getSearchResults($options, 'faq_facets');

    return [
      '#theme' => 'mars_search_faq_block',
      '#faq_title' => $config['faq_title'],
      '#qa_items' => $faq_items,
      '#cta_button_label' => $cta_button_label,
      '#cta_button_link' => $cta_button_link,
      '#search_form' => render($search_from),
      '#search_result_counter' => !empty($options['keys']) ? $this->formatPlural($search_results['resultsCount'], '1 Result for "@keys"', '@count Results for "@keys"', ['@keys' => $options['keys']]) : '',
      '#facets' => $this->searchHelper->prepareFacetsLinks($facets_search_results['facets'][$faq_facet_key], $faq_facet_key),
    ];
  }

}
