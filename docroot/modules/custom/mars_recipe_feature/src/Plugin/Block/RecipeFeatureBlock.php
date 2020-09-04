<?php

namespace Drupal\mars_recipe_feature\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class RecipeFeatureBlock.
 *
 * @Block(
 *   id = "recipe_feature_block",
 *   admin_label = @Translation("Recipe feature block"),
 *   category = @Translation("Recipe"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label =
 *   @Translation("Recipe"))
 *   }
 * )
 *
 * @package Drupal\mars_recipe_feature\Plugin\Block
 */
class RecipeFeatureBlock extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  /**
   * EntityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityTypeManager;

  /**
   * NodeStorage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
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
    $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    // $theme_settings = $this->configFactory->
    // get('emulsifymars.settings')->get();
    $build = [
      '#eyebrow' => $config['eyebrow'],
      '#recipe_id_from_url' => $config['take_recipe_id'],
      '#referenced_recipe' => $config['recipe_id'],
      '#recipe_media' => $config['recipe_media'],
      '#cta' => $config['cta'],
      '#theme' => 'recipe_feature_block',
    ];

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

    $form['take_recipe_id'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Take ID from argument'),
      '#attributes' => [
        'name' => 'recipe_id_cb',
      ],
    ];

    $form['recipe_id'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Recipe ID'),
      '#default_value' => isset($config['recipe_id']) ? $this->nodeStorage->load($this->configuration['recipe_id']) : NULL,
      '#selection_settings' => [
        'target_bundles' => ['recipe'],
      ],
      '#states' => [
        'visible' => [
          ':input[name="recipe_id_cb"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['recipe_media'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'media',
      '#title' => $this->t('Recipe Media'),
      '#default_value' => isset($config['recipe_media']) ? $this->entityTypeManager->getStorage('media')->load($this->configuration['recipe_media']) : NULL,
      '#selection_settings' => ['target_bundles' => ['lighthouse_video', 'lighthouse_image']],
    ];

    $form['cta'] = [
      '#type' => 'details',
      '#title' => $this->t('CTA'),
      '#open' => TRUE,
    ];
    $form['cta']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Link Title'),
      '#maxlength' => 15,
      '#required' => TRUE,
      '#default_value' => $config['cta']['title'] ?? $this->t('Get started'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->setConfiguration($values);
  }

}
