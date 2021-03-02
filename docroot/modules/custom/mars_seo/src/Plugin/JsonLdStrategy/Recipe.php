<?php

namespace Drupal\mars_seo\Plugin\JsonLdStrategy;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_seo\JsonLdStrategyPluginBase;
use Drupal\metatag\MetatagManager;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\Recipe as SchemaRecipe;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the Mars JSON LD Strategy for Recipes.
 *
 * @JsonLdStrategy(
 *   id = "recipe",
 *   label = @Translation("Recipe"),
 *   description = @Translation("Plugin for bundles that support Recipe schema."),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"), required = TRUE),
 *     "build" = @ContextDefinition("any", label = @Translation("Build array"))
 *   }
 * )
 */
class Recipe extends JsonLdStrategyPluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  protected $supportedBundles = ['recipe'];

  /**
   * Metatag manager.
   *
   * @var \Drupal\metatag\MetatagManager
   */
  protected $metatagManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_common.media_helper'),
      $container->get('url_generator'),
      $container->get('metatag.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MediaHelper $media_helper,
    UrlGeneratorInterface $url_generator,
    MetatagManager $metatag_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $media_helper, $url_generator);

    $this->metatagManager = $metatag_manager;
  }

  /**
   * {@inheritdoc}
   *
   * @todo Consider Builder pattern for better testability.
   */
  public function getStructuredData() {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->getContextValue('node');

    // Need to generate elements to get processed tokens.
    $metatags = $this->metatagManager->generateRawElements($this->metatagManager->tagsFromEntityWithDefaults($node), $node);

    // TODO: Import from rating engine or similar.
    return Schema::recipe()
      ->name($node->getTitle())
      ->if($node->field_recipe_image->target_id, function (SchemaRecipe $recipe) use ($node) {
        if ($url = $this->mediaHelper->getMediaUrl($node->field_recipe_image->target_id)) {
          $recipe->image([$url]);
        }
      })
      ->aggregateRating(Schema::aggregateRating()
        ->ratingValue(5)
        ->ratingCount(18)
      )
      ->totalTime(sprintf('PT%dM', (int) $node->field_recipe_cooking_time->value))
      ->description($node->field_recipe_description->value)
      ->recipeIngredient(array_map(function ($item) {
        return $item->value;
      }, iterator_to_array($node->field_recipe_ingredients)))
      ->if($node->field_recipe_number_of_servings->value, function (SchemaRecipe $recipe) use ($node) {
        $recipe->recipeYield($node->field_recipe_number_of_servings->value);
      })
      ->if(!empty($metatags['keywords']['#attributes']['content']), function (SchemaRecipe $recipe) use ($metatags) {
        $recipe->keywords($metatags['keywords']['#attributes']['content']);
      });
  }

}
