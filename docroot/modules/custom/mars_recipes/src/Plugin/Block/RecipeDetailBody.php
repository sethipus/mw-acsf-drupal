<?php

namespace Drupal\mars_recipes\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class RecipeBody.
 *
 * @Block(
 *   id = "recipe_detail_body",
 *   admin_label = @Translation("Recipe detail body"),
 *   category = @Translation("Recipe"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Recipe"))
 *   }
 * )
 *
 * @package Drupal\mars_recipes\Plugin\Block
 */
class RecipeDetailBody extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  /**
   * A view builder instance.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->viewBuilder = $entity_type_manager->getViewBuilder('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->getContextValue('node');
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
      $products = array_slice($products, 0, 2);
      foreach ($products as $product) {
        $product_used_items[] = $this->viewBuilder->view($product, 'card');
      }
    }

    return [
      '#ingredients_list' => $ingredients_list,
      '#nutrition_module' => $node->field_recipe_nutrition_module->value,
      '#product_used_items' => $product_used_items,
      '#theme' => 'recipe_detail_body_block',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIf($this->getContextValue('node')->bundle() == 'recipe');
  }

}
