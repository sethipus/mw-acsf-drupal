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
      '#default_value' => $site_label_config->get('article_recipe_share_label') ?? 'Hard coded label',
    ];

    $form['article_published'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Article published label'),
      '#default_value' => $site_label_config->get('article_published') ?? 'Hard coded label',
    ];

    $form['search'] = [
      '#type' => 'details',
      '#title' => $this->t('Search labels'),
      '#open' => FALSE,
    ];

    $form['search']['header_search_overlay_input'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header search overlay input label'),
      '#default_value' => $site_label_config->get('header_search_overlay') ?? 'Hard coded label',
    ];

    $form['search']['header_search_overlay_close'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header search overlay close label'),
      '#default_value' => $site_label_config->get('header_search_overlay_close') ?? 'Hard coded label',
    ];

    $form['search']['faq_card_grid_search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Card grid and FAQ search label'),
      '#default_value' => $site_label_config->get('faq_card_grid_search') ?? 'Hard coded label',
    ];

    $form['card'] = [
      '#type' => 'details',
      '#title' => $this->t('Card labels'),
      '#open' => FALSE,
    ];

    $form['card']['card_button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button label'),
      '#default_value' => $site_label_config->get('card_button') ?? 'Hard coded label',
    ];

    $form['card']['card_new_badge'] = [
      '#type' => 'textfield',
      '#title' => $this->t('New badge label'),
      '#default_value' => $site_label_config->get('card_new_badge') ?? 'Hard coded label',
    ];

    $form['recipe_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Recipe details labels'),
      '#open' => FALSE,
    ];

    $form['recipe_details']['recipe_details_products_used'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Products used label'),
      '#default_value' => $site_label_config->get('recipe_details_products_used') ?? 'Hard coded label',
    ];

    $form['recipe_details']['recipe_details_time'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Time label'),
      '#default_value' => $site_label_config->get('recipe_details_time') ?? 'Hard coded label',
    ];

    $form['recipe_details']['recipe_details_time_measurement'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Time measurement label'),
      '#default_value' => $site_label_config->get('recipe_details_time_measurement') ?? 'Hard coded label',
    ];

    $form['recipe_details']['recipe_details_ingredients'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ingredients label'),
      '#default_value' => $site_label_config->get('recipe_details_ingredients') ?? 'Hard coded label',
    ];

    $form['recipe_details']['recipe_details_ingredients_measurement'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ingredients measurement label'),
      '#default_value' => $site_label_config->get('recipe_details_ingredients_measurement') ?? 'Hard coded label',
    ];

    $form['recipe_details']['recipe_details_servings'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Servings label'),
      '#default_value' => $site_label_config->get('recipe_details_servings') ?? 'Hard coded label',
    ];

    $form['recipe_details']['recipe_details_servings_measurement'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Servings measurement label'),
      '#default_value' => $site_label_config->get('recipe_details_servings_measurement') ?? 'Hard coded label',
    ];

    $form['footer'] = [
      '#type' => 'details',
      '#title' => $this->t('Footer labels'),
      '#open' => FALSE,
    ];

    $form['footer']['footer_region'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Select region label'),
      '#default_value' => $site_label_config->get('footer_region') ?? 'Hard coded label',
    ];

    $form['footer']['footer_social_header'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Social header label'),
      '#default_value' => $site_label_config->get('footer_social_header') ?? 'Hard coded label',
    ];

    return $form;
  }

}
