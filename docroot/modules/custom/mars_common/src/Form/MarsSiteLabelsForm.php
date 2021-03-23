<?php

namespace Drupal\mars_common\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_search\SearchProcessFactoryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form for site level labels.
 *
 * @internal
 */
class MarsSiteLabelsForm extends ConfigFormBase {

  /**
   * Search processing factory.
   *
   * @var \Drupal\mars_search\SearchProcessFactoryInterface
   */
  protected $searchProcessor;

  /**
   * Search categories processor.
   *
   * @var \Drupal\mars_search\Processors\SearchCategoriesInterface
   */
  protected $searchCategories;

  /**
   * MarsSiteLabelsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\mars_search\SearchProcessFactoryInterface $searchProcessor
   *   Search processor factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory, SearchProcessFactoryInterface $searchProcessor) {
    parent::__construct($config_factory);
    $this->searchProcessor = $searchProcessor;
    $this->searchCategories = $this->searchProcessor->getProcessManager('search_categories');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('mars_search.search_factory'),
    );
  }

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
      '#required' => TRUE,
    ];

    $form['article_published'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Article published label'),
      '#default_value' => $site_label_config->get('article_published'),
      '#required' => TRUE,
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
      '#required' => TRUE,
    ];

    $form['search']['header_search_overlay_close'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header search overlay close label'),
      '#default_value' => $site_label_config->get('header_search_overlay_close'),
      '#required' => TRUE,
    ];

    $form['search']['faq_card_grid_search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Card grid and FAQ search label'),
      '#default_value' => $site_label_config->get('faq_card_grid_search'),
      '#required' => TRUE,
    ];

    foreach ($this->searchCategories->getCategories() as $index => $field) {
      $bundles = implode(',', $field['content_types']);
      $machineName = $field['machine_name'] ?? $index;
      // @codingStandardsIgnoreStart
      $form['search'][$index] = [
        '#type' => 'textfield',
        '#title' => $this->t("Search filter label for the field {$machineName}, used in node bundles: {$bundles}"),
        '#default_value' => $field['label'],
      ];
      // @codingStandardsIgnoreEnd
    }

    $form['card'] = [
      '#type' => 'details',
      '#title' => $this->t('Card labels'),
      '#open' => FALSE,
    ];

    $form['card']['card_new_badge'] = [
      '#type' => 'textfield',
      '#title' => $this->t('New badge label'),
      '#default_value' => $site_label_config->get('card_new_badge'),
      '#required' => TRUE,
    ];

    $form['card']['recipe_card_button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Recipe card button label'),
      '#default_value' => $site_label_config->get('recipe_card_button'),
      '#required' => TRUE,
    ];

    $form['card']['product_card_button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product card button label'),
      '#default_value' => $site_label_config->get('product_card_button'),
      '#required' => TRUE,
    ];

    $form['card']['article_card_button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Article card button label'),
      '#default_value' => $site_label_config->get('article_card_button'),
      '#required' => TRUE,
    ];

    $form['card']['landing_card_button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Landing card button label'),
      '#default_value' => $site_label_config->get('landing_card_button'),
      '#required' => TRUE,
    ];

    $form['card']['campaign_card_button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Campaign card button label'),
      '#default_value' => $site_label_config->get('campaign_card_button'),
      '#required' => TRUE,
    ];

    $form['card']['content_hub_card_button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Content hub card button label'),
      '#default_value' => $site_label_config->get('content_hub_card_button'),
      '#required' => TRUE,
    ];

    $form['recipe_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Recipe details labels'),
      '#open' => FALSE,
    ];

    $form['recipe_details']['recipe_details_time'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Time label'),
      '#default_value' => $site_label_config->get('recipe_details_time'),
      '#required' => TRUE,
    ];

    $form['recipe_details']['recipe_details_ingredients'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ingredients label'),
      '#default_value' => $site_label_config->get('recipe_details_ingredients'),
      '#required' => TRUE,
    ];

    $form['recipe_details']['recipe_details_ingredients_measurement'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ingredients measurement label'),
      '#default_value' => $site_label_config->get('recipe_details_ingredients_measurement'),
      '#required' => TRUE,
    ];

    $form['recipe_details']['recipe_details_servings'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Servings label'),
      '#default_value' => $site_label_config->get('recipe_details_servings'),
      '#required' => TRUE,
    ];

    $form['recipe_details']['recipe_details_servings_measurement'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Servings measurement label'),
      '#default_value' => $site_label_config->get('recipe_details_servings_measurement'),
      '#required' => TRUE,
    ];

    $form['recipe_details']['body'] = [
      '#type' => 'details',
      '#title' => $this->t('Recipe body'),
      '#open' => FALSE,
    ];

    $form['recipe_details']['body']['recipe_body_ingredients_used'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ingredients used label'),
      '#default_value' => $site_label_config->get('recipe_body_ingredients_used'),
      '#required' => TRUE,
    ];

    $form['recipe_details']['body']['recipe_body_products_used'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Products used label'),
      '#default_value' => $site_label_config->get('recipe_body_products_used'),
      '#required' => TRUE,
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
      '#required' => TRUE,
    ];

    $form['footer']['footer_social_header'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Social header label'),
      '#default_value' => $site_label_config->get('footer_social_header'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('mars_common.site_labels');
    foreach (array_keys($this->searchCategories->getCategories()) as $index) {
      $config->set($index, $form_state->getValue($index));
    }
    $config->set('article_recipe_share', $form_state->getValue('article_recipe_share'))
      ->set('article_published', $form_state->getValue('article_published'))
      ->set('header_search_overlay', $form_state->getValue('header_search_overlay'))
      ->set('header_search_overlay_close', $form_state->getValue('header_search_overlay_close'))
      ->set('faq_card_grid_search', $form_state->getValue('faq_card_grid_search'))
      ->set('card_new_badge', $form_state->getValue('card_new_badge'))
      ->set('recipe_card_button', $form_state->getValue('recipe_card_button'))
      ->set('product_card_button', $form_state->getValue('product_card_button'))
      ->set('article_card_button', $form_state->getValue('article_card_button'))
      ->set('landing_card_button', $form_state->getValue('landing_card_button'))
      ->set('campaign_card_button', $form_state->getValue('campaign_card_button'))
      ->set('content_hub_card_button', $form_state->getValue('content_hub_card_button'))
      ->set('recipe_body_products_used', $form_state->getValue('recipe_body_products_used'))
      ->set('recipe_body_ingredients_used', $form_state->getValue('recipe_body_ingredients_used'))
      ->set('recipe_details_time', $form_state->getValue('recipe_details_time'))
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
