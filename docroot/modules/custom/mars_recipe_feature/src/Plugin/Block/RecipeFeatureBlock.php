<?php

namespace Drupal\mars_recipe_feature\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RecipeFeatureBlock.
 *
 * @Block(
 *   id = "recipe_feature",
 *   admin_label = @Translation("Recipe feature"),
 *   category = @Translation("Recipe"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label =
 *   @Translation("Recipe"))
 *   }
 * )
 *
 * @package Drupal\mars_recipe_feature\Plugin\Block
 */
class RecipeFeatureBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // $config = $this->getConfiguration();
    // $theme_settings = $this->config->get('emulsifymars.settings')->get();
    $build = [];
    // $build['#theme'] = 'recipe_feature_block';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#maxlength' => 15,
      '#required' => TRUE,
      '#default_value' => $config['eyebrow'] ?? '',
    ];

    $form['title'] = [
      '#type' => 'details',
      '#title' => $this->t('Title'),
      '#open' => TRUE,
    ];
    $form['title']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Title Link URL'),
      '#maxlength' => 2048,
      '#required' => TRUE,
      '#default_value' => $config['title']['url'] ?? '',
    ];
    $form['title']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title label'),
      '#maxlength' => 50,
      '#required' => TRUE,
      '#default_value' => $config['title']['label'] ?? '',
    ];
    $form['cta'] = [
      '#type' => 'details',
      '#title' => $this->t('CTA'),
      '#open' => TRUE,
    ];
    $form['cta']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('CTA Link URL'),
      '#maxlength' => 2048,
      '#required' => TRUE,
      '#default_value' => $config['cta']['url'] ?? '',
    ];
    $form['cta']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Link Title'),
      '#maxlength' => 15,
      '#required' => TRUE,
      '#default_value' => $config['cta']['title'] ?? 'Explore',
    ];

    return $form;
  }

}
