<?php

namespace Drupal\mars_entry_gate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for Entry Gate.
 */
class EntryGateConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entry_gate_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'mars_entry_gate.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('mars_entry_gate.settings');

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $config->get('enabled') ?? FALSE,
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $config->get('title') ?? 'Our Promise',
      '#maxlength' => 55,
      '#required' => TRUE,
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $config->get('description') ?? 'As a responsible manufacturer and in accordance with our marketing code, we have to check your age at this point.',
      '#maxlength' => 150,
      '#required' => TRUE,
    ];

    $form['heading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Heading'),
      '#default_value' => $config->get('heading') ?? 'Please complete your date of birth:',
      '#maxlength' => 45,
      '#required' => TRUE,
    ];

    $form['marketing_message'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Marketing message'),
      '#default_value' => $config->get('marketing_message') ?? '<p>For more information about responsible use of our products, please follow the link to the <a href="https://twix.de/assets/media/Mars-Code.pdf">Mars Marketing Code</a>.</p>',
      '#required' => TRUE,
    ];

    $form['minimum_age'] = [
      '#type' => 'number',
      '#title' => $this->t('Required minimal age'),
      '#default_value' => $config->get('minimum_age') ?? 13,
      '#min' => 1,
      '#max' => 150,
      '#step' => 1,
      '#required' => TRUE,
    ];

    $form['error_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error title'),
      '#default_value' => $config->get('error_title') ?? 'We are sorry',
      '#maxlength' => 25,
      '#required' => TRUE,
    ];

    $form['error_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Error message'),
      '#default_value' => $config->get('error_message') ?? 'Our marketing code states that you are not authorized to access the content you requested. Unfortunately, you cannot view the additional content in this section of the website.',
      '#maxlength' => 180,
      '#required' => TRUE,
    ];

    $form['error_link_1'] = [
      '#type' => 'url',
      '#title' => $this->t('Error link 1 (marketing code)'),
      '#default_value' => $config->get('error_link_1') ?? 'https://twix.de/assets/media/Mars-Code.pdf',
      '#required' => TRUE,
    ];

    $form['error_link_2'] = [
      '#type' => 'url',
      '#title' => $this->t('Error link 2 (imprint)'),
      '#default_value' => $config->get('error_link_2') ?? 'https://deu.mars.com/site-owner',
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('mars_entry_gate.settings');

    $config->set('enabled', $form_state->getValue('enabled'));
    $config->set('title', $form_state->getValue('title'));
    $config->set('description', $form_state->getValue('description'));
    $config->set('heading', $form_state->getValue('heading'));
    $config->set(
      'marketing_message',
      $form_state->getValue('marketing_message')['value'] ?? NULL
    );
    $config->set('minimum_age', $form_state->getValue('minimum_age'));
    $config->set('error_title', $form_state->getValue('error_title'));
    $config->set('error_message', $form_state->getValue('error_message'));
    $config->set('error_link_1', $form_state->getValue('error_link_1'));
    $config->set('error_link_2', $form_state->getValue('error_link_2'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
