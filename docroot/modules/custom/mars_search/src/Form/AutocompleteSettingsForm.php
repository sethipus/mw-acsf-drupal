<?php

namespace Drupal\mars_search\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Autocomplete form settings.
 */
class AutocompleteSettingsForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Constructs a new AutocompleteSettingsForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

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
  public function getFormId() {
    return 'mars_search_autocomplete_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mars_search.autocomplete'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('mars_search.autocomplete');

    $views_storage = $this->entityTypeManager->getStorage('view');
    // We consider only Searcgh API views.
    $views = $views_storage->getQuery()
      ->condition('base_field', 'search_api_id')
      ->condition('status', 'true')
      ->execute();

    if (!$views) {
      $form['#markup'] = $this->t('There are no Search API views available to configure autocomplete for.');
      return $form;
    }

    $form['views'] = [
      '#type' => 'checkboxes',
      '#options' => $views,
      '#title' => $this->t('Enable for views'),
      '#default_value' => $config->get('views'),
    ];

    $form['empty_text_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Autocomplete empty text description'),
      '#description' => $this->t('Will be used when autocomplete search returns no results'),
      '#default_value' => $config->get('empty_text_description'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this
      ->config('mars_search.autocomplete')
      ->set('views', $form_state->getValue('views'))
      ->set('empty_text_description', $form_state->getValue('empty_text_description'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
