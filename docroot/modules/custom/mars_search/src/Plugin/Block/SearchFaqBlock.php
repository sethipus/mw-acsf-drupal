<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_search\Form\SearchForm;
use Drupal\mars_search\SearchHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    SearchHelperInterface $search_helper,
    FormBuilderInterface $form_builder
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->searchHelper = $search_helper;
    $this->formBuilder = $form_builder;
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

    $options = [
      'conditions' => [
        ['type', 'faq', '='],
      ],
      'limit' => 4,
      'sort' => [
        'faq_item_queue_weight' => 'ASC',
        'created' => 'DESC',
      ],
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
      foreach ($search_results['results'] as $row_key => $search_result) {
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
    $search_from = $this->formBuilder->getForm(SearchForm::class, $search_results['resultsCount']);

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
      '#facets' => $this->searchHelper->prepareFacetsLinks($facets_search_results['facets'][$faq_facet_key], $faq_facet_key),
    ];
  }

}
