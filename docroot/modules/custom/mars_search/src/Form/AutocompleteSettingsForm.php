<?php

namespace Drupal\mars_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Autocomplete form settings.
 */
class AutocompleteSettingsForm extends ConfigFormBase {

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
      ->set('empty_text_description', $form_state->getValue('empty_text_description'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
