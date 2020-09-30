<?php

namespace Drupal\mars_seo\Plugin\JsonLdStrategy;

use Drupal\mars_seo\JsonLdStrategyPluginBase;

/**
 * Plugin implementation of the Mars JSON LD Strategy for Products.
 *
 * @JsonLdStrategy(
 *   id = "product",
 *   label = @Translation("Product"),
 *   description = @Translation("Plugin for bundles that support Product schema."),
 *   bundles = {
 *     "product",
 *     "product_multipack"
 *   },
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"), required = TRUE),
 *     "build" = @ContextDefinition("any", label = @Translation("Build array"))
 *   }
 * )
 */
class Product extends JsonLdStrategyPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getStructuredData() {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->getContextValue('node');

    $data = [
      '@context' => 'https://schema.org',
      '@type' => 'Product',
    ];

    $data['name'] = $node->getTitle();

    // TODO: Import from rating engine or similar.
    $data['aggregateRating'] = [
      '@type' => 'AggregateRating',
      'ratingValue' => 5,
      'ratingCount' => 15,
    ];

    if ($node->field_product_brand->target_id) {
      /** @var \Drupal\taxonomy\Entity\Term $term */
      $term = $node->field_product_brand->entity;

      $data['brand'] = [
        '@type' => 'Brand',
        'name' => $term->getName(),
      ];
    }

    if ($node->field_product_description->value) {
      $data['description'] = $node->field_product_description->value;
    }

    $data['sku'] = $node
      ->field_product_variants
      ->first()
      ->entity
      ->field_product_sku
      ->value;

    foreach ($node->field_product_variants as $item) {
      if (!$item->field_product_key_image->target_id || !$item->field_product_key_image->entity->image->target_id) {
        continue;
      }

      $data['image'][] = $item->field_product_key_image
        ->entity
        ->image
        ->entity
        ->createFileUrl(FALSE);
    }

    return $data;
  }

}
