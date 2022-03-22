<?php

namespace Drupal\mars_recipes\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\Traits\OverrideThemeTextColorTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class RecipeBody.
 *
 * @Block(
 *   id = "recipe_detail_body",
 *   admin_label = @Translation("MARS: Recipe detail body"),
 *   category = @Translation("Recipe"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Recipe"))
 *   }
 * )
 *
 * @package Drupal\mars_recipes\Plugin\Block
 */
class RecipeDetailBody extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  use OverrideThemeTextColorTrait;

  /**
   * A view builder instance.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * Node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $config;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config,
    LanguageHelper $language_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->viewBuilder = $entity_type_manager->getViewBuilder('node');
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->config = $config;
    $this->languageHelper = $language_helper;
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
      $container->get('mars_common.language_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $text_color_override = FALSE;
    if (!empty($this->configuration['override_text_color']['override_color'])) {
      $text_color_override = static::$overrideColor;
    }
    $node = $this->getContextValue('node');

    // Load custom product.
    if (!empty($this->configuration['recipe'])) {
      $node = $this->nodeStorage->load($this->configuration['recipe']) ?? $node;
    }

    // Skip build process for non product entities.
    if (empty($node) || $node->bundle() != 'recipe') {
      return [];
    }

    $ingredients_list = [];
    if (!$node->get('field_recipe_ingredients')->isEmpty()) {
      $ingredients = $node->get('field_recipe_ingredients')->getValue();
      $ingredients_list = array_map(
        function ($ingredient) {
          return ['content' => $ingredient['value']];
        },
        $ingredients
      );
    }
    $product_used_items = [];
    if (!$node->get('field_product_reference')->isEmpty()) {
      $products = $node->get('field_product_reference')->referencedEntities();
      // Sort A-z.
      usort($products, function ($a, $b) {
        return strcmp($a->title->value, $b->title->value);
      });
      // Limit amount of cards.
      //As per the discussion, there will be a chance for multiple products for a recipes 
     // $products = array_slice($products, 0, 2);
      foreach ($products as $product) {
        if (!empty($text_color_override)) {
          $product_used_items[] = array_merge($this->viewBuilder->view($product, 'card'), ['#text_color_override' => $text_color_override]);
        }
        else {
          $product_used_items[] = $this->viewBuilder->view($product, 'card');
        }
      }
    }

    $label_config = $this->config->get('mars_common.site_labels');
    $ingredients_used_label = $label_config->get('recipe_body_ingredients_used');
    $products_used_label = $label_config->get('recipe_body_products_used');

    $build = [
      '#ingredients_list' => $ingredients_list,
      '#nutrition_module' => $node->field_recipe_nutrition_module->value,
      '#product_used_items' => $product_used_items,
      '#ingredients_used_label' => $this->languageHelper->translate($ingredients_used_label),
      '#products_used_label' => $this->languageHelper->translate($products_used_label),
      '#text_color_override' => $text_color_override,
      '#theme' => 'recipe_detail_body_block',
    ];

    $cacheMetadata = CacheableMetadata::createFromRenderArray($build);
    $cacheMetadata->addCacheableDependency($label_config);
    $cacheMetadata->applyTo($build);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['recipe'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Default recipe'),
      '#default_value' => isset($this->configuration['recipe']) ? $this->nodeStorage->load($this->configuration['recipe']) : NULL,
      '#selection_settings' => [
        'target_bundles' => ['recipe'],
      ],
    ];

    $config = $this->getConfiguration();
    $this->buildOverrideColorElement($form, $config);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues());
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIf($this->getContextValue('node')->bundle() == 'recipe');
  }
}