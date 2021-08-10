<?php

namespace Drupal\mars_common\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure emails configuration settings for this site.
 */
class MarsEmailsConfigurationForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'mars_common.emails_configuration';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'emails_configuration';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['grocery_list'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Email a grocery list'),
    ];
    $form['grocery_list']['grocery_list_from'] = [
      '#type' => 'textfield',
      '#title' => $this->t('FROM email'),
      '#default_value' => $config->get('grocery_list_from'),
    ];
    $form['grocery_list']['grocery_list_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email subject'),
      '#default_value' => $config->get('grocery_list_subject'),
    ];
    $form['grocery_list']['grocery_list_body'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Email body'),
      '#default_value' => $config->get('grocery_list_body'),
    ];
    $form['grocery_list']['grocery_list_signature'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Email signature'),
      '#default_value' => $config->get('grocery_list_signature'),
    ];

    $form['recipe'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Email a recipe'),
    ];
    $form['recipe']['recipe_from'] = [
      '#type' => 'textfield',
      '#title' => $this->t('FROM email'),
      '#default_value' => $config->get('recipe_from'),
    ];
    $form['recipe']['recipe_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email subject'),
      '#default_value' => $config->get('recipe_subject'),
    ];
    $form['recipe']['recipe_body'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Email body'),
      '#default_value' => $config->get('recipe_body'),
    ];
    $form['recipe']['recipe_signature'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Email signature'),
      '#default_value' => $config->get('recipe_signature'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      ->set('grocery_list_from', $form_state->getValue('grocery_list_from'))
      ->set('grocery_list_subject', $form_state->getValue('grocery_list_subject'))
      ->set('grocery_list_body', $form_state->getValue('grocery_list_body')['value'])
      ->set('grocery_list_signature', $form_state->getValue('grocery_list_signature')['value'])
      ->set('recipe_from', $form_state->getValue('recipe_from'))
      ->set('recipe_subject', $form_state->getValue('recipe_subject'))
      ->set('recipe_body', $form_state->getValue('recipe_body')['value'])
      ->set('recipe_signature', $form_state->getValue('recipe_signature')['value'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
