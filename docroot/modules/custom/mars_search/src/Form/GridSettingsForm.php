<?php

namespace Drupal\mars_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class GridSettingsForm.
 *
 * @package Drupal\mars_grid\Form
 */
class GridSettingsForm extends FormBase {

  /**
   * List of vocabularies which are included in indexing.
   *
   * @var array
   */
  const TAXONOMY_VOCABULARIES = [
    'mars_brand_initiatives' => 'Brand initiatives',
    'mars_flavor' => 'Flavor',
    'mars_format' => 'Format',
    'mars_occasions' => 'Occasions',
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
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mars_search_grid';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['title'] = [
      '#title' => $this->t('Title'),
      '#type' => 'textfield',
      '#size' => 35,
      '#required' => TRUE,
      '#default_value' => $this->t('All products'),
    ];

    $form['no_results_heading'] = [
      '#title' => $this->t('Heading for no results case'),
      '#default_value' => $this->t('There are no matching results for'),
      '#type' => 'textfield',
      '#size' => 35,
      '#required' => TRUE,
    ];
    $form['no_results_text'] = [
      '#title' => $this->t('Text for no results case'),
      '#default_value' => $this->t('Please try entering a different search'),
      '#type' => 'textfield',
      '#size' => 50,
      '#required' => TRUE,
    ];

    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content type'),
      '#multiple' => TRUE,
      '#options' => GridSettingsForm::CONTENT_TYPES,
    ];

    $form = array_merge($form, $this->buildTopResults());
    $form = array_merge($form, $this->buildGeneralFilters());
    $form = array_merge($form, $this->buildExposedFilters());

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
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

    $form['general_filters'] = [
      '#type' => 'details',
      '#title' => $this->t('Predefined filters'),
      '#open' => FALSE,
    ];

    foreach (GridSettingsForm::TAXONOMY_VOCABULARIES as $vocabulary => $label) {
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

      $form['general_filters'][] = [
        '#type' => 'select',
        '#title' => $label,
        '#multiple' => TRUE,
        '#options' => $terms_options,
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
        'target_bundles' => array_keys(GridSettingsForm::CONTENT_TYPES),
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
    $form = [];
    $form['exposed_filters_wrapper'] = [
      '#type' => 'details',
      '#title' => $this->t('Exposed filters'),
      '#open' => FALSE,
    ];

    $form['exposed_filters_wrapper']['toggle_search'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable text search bar'),
      '#description' => $this->t('If enabled a text search bar appears on the grid.'),
    ];
    $form['exposed_filters_wrapper']['toggle_filters'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable exposed search filters'),
      '#description' => $this->t('If enabled search filters appear on the grid.'),
    ];
    return $form;
  }

}
