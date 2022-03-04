<?php

namespace Drupal\mars_onetrust\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * OneTrustConfigForm class - configuration for one trust banner.
 */
class OneTrustConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'onetrust_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);

    // Default settings.
    $config = $this->config('mars_onetrust.settings');

    // Data domain field that will be different per environment.
    $form['data_domain'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Data domain'),
      '#default_value' => $config->get('mars_onetrust.data_domain'),
    ];
    $form['google_analytics_tag_attr'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable Google Analytics Custom Attributes.'),
      '#default_value' => $config->get('mars_onetrust.google_analytics_tag_attr'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('mars_onetrust.settings')
      ->set('mars_onetrust.data_domain', $form_state->getValue('data_domain'))
      ->set('mars_onetrust.google_analytics_tag_attr', $form_state->getValue('google_analytics_tag_attr'))
      ->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    // This function returns the name of the settings files we will
    // create / use.
    return [
      'mars_onetrust.settings',
    ];
  }

}
