<?php

namespace Drupal\mars_seo\Plugin\JsonLdStrategy;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_seo\JsonLdStrategyPluginBase;
use Drupal\metatag\MetatagManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the Mars JSON LD Strategy for Recipes.
 *
 * @JsonLdStrategy(
 *   id = "recipe",
 *   label = @Translation("Recipe"),
 *   description = @Translation("Plugin for bundles that support Recipe schema."),
 *   bundles = {
 *     "recipe"
 *   },
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"), required = TRUE),
 *     "build" = @ContextDefinition("any", label = @Translation("Build array"))
 *   }
 * )
 */
class Recipe extends JsonLdStrategyPluginBase implements ContainerFactoryPluginInterface {

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
    MetatagManager $metatag_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

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

    $data = [
      '@context' => 'https://schema.org',
      '@type' => 'Recipe',
    ];

    $data['name'] = $node->getTitle();

    if ($node->field_recipe_image->target_id && ($url = $this->getMediaUrl($node->field_recipe_image->entity))) {
      $data['image'][] = $url;
    }

    // TODO: Import from rating engine or similar.
    $data['aggregateRating'] = [
      '@type' => 'AggregateRating',
      'ratingValue' => 5,
      'ratingCount' => 15,
    ];

    if (!empty($cooking_time = (int) $node->field_recipe_cooking_time->value)) {
      $data['totalTime'] = sprintf('PT%dM', $cooking_time);
    }

    if ($node->field_recipe_description->value) {
      $data['description'] = $node->field_recipe_description->value;
    }

    foreach ($node->field_recipe_ingredients as $item) {
      $data['recipeIngredient'] = $item->value;
    }

    if ($node->field_recipe_number_of_servings->value) {
      $data['recipeYield'] = $node->field_recipe_number_of_servings->value;
    }

    if ($node->field_recipe_video->target_id) {
      /** @var \Drupal\media\Entity\Media $media */
      $media = $node->field_recipe_video->entity;

      if ($url = $this->getMediaUrl($media)) {
        $data['video'] = [
          '@type' => 'Clip',
          'name' => $media->getName(),
          'url' => $url,
        ];
      }
    }

    // Need to generate elements to get processed tokens.
    $metatags = $this->metatagManager->generateRawElements($this->metatagManager->tagsFromEntityWithDefaults($node), $node);
    if (!empty($metatags['keywords']['#attributes']['content'])) {
      $data['keywords'] = $metatags['keywords']['#attributes']['content'];
    }

    return $data;
  }

}
