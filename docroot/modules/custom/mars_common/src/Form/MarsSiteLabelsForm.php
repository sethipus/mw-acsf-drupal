<?php

namespace Drupal\mars_common\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Override for the site information form.
 *
 * @internal
 */
class MarsSiteLabelsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'site_labels';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mars_common.site_labels'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $site_label_config = $this->config('mars_common.site_labels');

    $form['article_recipe_share'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Recipe/Article share label'),
      '#default_value' => $site_label_config->get('article_recipe_share'),
    ];

    $form['article_published'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Article published label'),
      '#default_value' => $site_label_config->get('article_published'),
    ];

    $form['search'] = [
      '#type' => 'details',
      '#title' => $this->t('Search labels'),
      '#open' => FALSE,
    ];

    $form['search']['header_search_overlay'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header search overlay input label'),
      '#default_value' => $site_label_config->get('header_search_overlay'),
    ];

    $form['search']['header_search_overlay_close'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header search overlay close label'),
      '#default_value' => $site_label_config->get('header_search_overlay_close'),
    ];

    $form['search']['faq_card_grid_search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Card grid and FAQ search label'),
      '#default_value' => $site_label_config->get('faq_card_grid_search'),
    ];

    $form['card'] = [
      '#type' => 'details',
      '#title' => $this->t('Card labels'),
      '#open' => FALSE,
    ];

    $form['card']['card_button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button label'),
      '#default_value' => $site_label_config->get('card_button'),
    ];

    $form['card']['card_new_badge'] = [
      '#type' => 'textfield',
      '#title' => $this->t('New badge label'),
      '#default_value' => $site_label_config->get('card_new_badge'),
    ];

    $form['recipe_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Recipe details labels'),
      '#open' => FALSE,
    ];

    $form['recipe_details']['recipe_details_products_used'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Products used label'),
      '#default_value' => $site_label_config->get('recipe_details_products_used'),
    ];

    $form['recipe_details']['recipe_details_time'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Time label'),
      '#default_value' => $site_label_config->get('recipe_details_time'),
    ];

    $form['recipe_details']['recipe_details_time_measurement'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Time measurement label'),
      '#default_value' => $site_label_config->get('recipe_details_time_measurement'),
    ];

    $form['recipe_details']['recipe_details_ingredients'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ingredients label'),
      '#default_value' => $site_label_config->get('recipe_details_ingredients'),
    ];

    $form['recipe_details']['recipe_details_ingredients_measurement'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ingredients measurement label'),
      '#default_value' => $site_label_config->get('recipe_details_ingredients_measurement'),
    ];

    $form['recipe_details']['recipe_details_servings'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Servings label'),
      '#default_value' => $site_label_config->get('recipe_details_servings'),
    ];

    $form['recipe_details']['recipe_details_servings_measurement'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Servings measurement label'),
      '#default_value' => $site_label_config->get('recipe_details_servings_measurement'),
    ];

    $form['footer'] = [
      '#type' => 'details',
      '#title' => $this->t('Footer labels'),
      '#open' => FALSE,
    ];

    $form['footer']['footer_region'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Select region label'),
      '#default_value' => $site_label_config->get('footer_region'),
    ];

    $form['footer']['footer_social_header'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Social header label'),
      '#default_value' => $site_label_config->get('footer_social_header'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('mars_common.site_labels');
    $config->set('article_recipe_share', $form_state->getValue('article_recipe_share'))
      ->set('article_published', $form_state->getValue('article_published'))
      ->set('header_search_overlay', $form_state->getValue('header_search_overlay'))
      ->set('header_search_overlay_close', $form_state->getValue('header_search_overlay_close'))
      ->set('faq_card_grid_search', $form_state->getValue('faq_card_grid_search'))
      ->set('card_button', $form_state->getValue('card_button'))
      ->set('card_new_badge', $form_state->getValue('card_new_badge'))
      ->set('recipe_details_products_used', $form_state->getValue('recipe_details_products_used'))
      ->set('recipe_details_time', $form_state->getValue('recipe_details_time'))
      ->set('recipe_details_time_measurement', $form_state->getValue('recipe_details_time_measurement'))
      ->set('recipe_details_ingredients', $form_state->getValue('recipe_details_ingredients'))
      ->set('recipe_details_ingredients_measurement', $form_state->getValue('recipe_details_ingredients_measurement'))
      ->set('recipe_details_servings', $form_state->getValue('recipe_details_servings'))
      ->set('recipe_details_servings_measurement', $form_state->getValue('recipe_details_servings_measurement'))
      ->set('footer_region', $form_state->getValue('footer_region'))
      ->set('footer_social_header', $form_state->getValue('footer_social_header'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
