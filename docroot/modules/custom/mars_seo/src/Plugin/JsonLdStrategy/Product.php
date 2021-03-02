<?php

namespace Drupal\mars_seo\Plugin\JsonLdStrategy;

use Drupal\mars_seo\JsonLdStrategyPluginBase;
use Spatie\SchemaOrg\Product as SchemaProduct;
use Spatie\SchemaOrg\Schema;

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
  protected $supportedBundles = ['product', 'product_multipack'];

  /**
   * {@inheritdoc}
   */
  public function getStructuredData() {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->getContextValue('node');

    $main_image_id = $this->mediaHelper->getEntityMainMediaId($node);
    $main_image_url = $this->mediaHelper->getMediaUrl($main_image_id);

    $options = ['absolute' => TRUE];
    $url = $this->urlGenerator->generateFromRoute('entity.node.canonical', ['node' => $node->id()], $options);

    // TODO: Import from rating engine or similar.
    return Schema::product()
      ->name($node->getTitle())
      ->identifier($url)
      ->if($node->field_product_brand->target_id, function (SchemaProduct $product) use ($node) {
        $product->brand($node->field_product_brand->entity->getName());
      })
      ->if($node->field_product_description->value, function (SchemaProduct $product) use ($node) {
        $product->description($node->field_product_description->value);
      })
      ->if($node->field_product_variants->first(), function (SchemaProduct $product) use ($node) {
        $product->sku($node->field_product_variants->first()->entity->field_product_sku->value);
      })
      ->if($main_image_url, function (SchemaProduct $product) use ($main_image_url) {
        $product->image([$main_image_url]);
      });
  }

}
