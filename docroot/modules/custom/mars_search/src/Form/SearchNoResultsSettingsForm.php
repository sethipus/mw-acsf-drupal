<?php

namespace Drupal\mars_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Search no results form settings.
 */
class SearchNoResultsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mars_search_no_results_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mars_search.search_no_results'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('mars_search.search_no_results');

    $form['no_results_heading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Heading for no results case'),
      '#description' => $this->t('Will be used when search returns no results'),
      '#default_value' => $config->get('no_results_heading') ?? $this->t('There are no matching results for "@keys"'),
    ];

    $form['no_results_heading_empty_str'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Heading for no results in case of empty string'),
      '#description' => $this->t('Will be used when search returns no results (empty string)'),
      '#default_value' => $config->get('no_results_heading_empty_str') ?? $this->t('There are no matching results'),
    ];

    $form['no_results_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text for no results case'),
      '#description' => $this->t('Will be used when search returns no results'),
      '#default_value' => $config->get('no_results_text') ?? $this->t('Please try entering a different search'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this
      ->config('mars_search.search_no_results')
      ->set('no_results_heading', $form_state->getValue('no_results_heading'))
      ->set('no_results_heading_empty_str', $form_state->getValue('no_results_heading_empty_str'))
      ->set('no_results_text', $form_state->getValue('no_results_text'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
