<?php

namespace Drupal\mars_grid\Form;

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
    return 'mars_grid';
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

    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content type'),
      '#multiple' => TRUE,
      '#options' => GridSettingsForm::CONTENT_TYPES,
    ];

    $form = array_merge($form, $this->buildGeneralFilters());

    $form['toggle_search'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable text search bar'),
      '#description' => $this->t('If enabled a text search bar appears on the grid.'),
    ];
    $form['toggle_filters'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable exposed search filters'),
      '#description' => $this->t('If enabled search filters appear on the grid.'),
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
      '#type' => 'fieldset',
      '#title' => $this->t('General / Predefined filters'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
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

}
