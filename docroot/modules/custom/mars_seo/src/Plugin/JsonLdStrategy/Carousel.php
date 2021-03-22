<?php

namespace Drupal\mars_seo\Plugin\JsonLdStrategy;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Render\Element;
use Drupal\mars_seo\JsonLdStrategyPluginBase;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\Product;

/**
 * Plugin implementation of the Mars JSON LD Strategy for Articles.
 *
 * @JsonLdStrategy(
 *   id = "carousel",
 *   label = @Translation("Carousel"),
 *   description = @Translation("Plugin for nodes that support Carousel schema."),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"), required = TRUE),
 *     "build" = @ContextDefinition("any", label = @Translation("Build array"))
 *   }
 * )
 */
class Carousel extends JsonLdStrategyPluginBase {

  /**
   * Elements that contain carousel-related data.
   *
   * @var array
   */
  protected $components;

  /**
   * {@inheritdoc}
   */
  public function isApplicable() {
    try {
      $build = $this->getContextValue('build');
    }
    catch (PluginException $e) {
      return FALSE;
    }

    if (!isset($build['_layout_builder'])) {
      return FALSE;
    }

    $this->components = $this->getSupportedLayoutBuilderComponents($build['_layout_builder']);

    return !empty($this->components);
  }

  /**
   * {@inheritdoc}
   */
  public function getStructuredData() {
    if (!isset($this->components)) {
      $build = $this->getContextValue('build');

      if (!isset($build['_layout_builder'])) {
        return NULL;
      }

      $this->components = $this->getSupportedLayoutBuilderComponents($build['_layout_builder']);

      if (empty($this->components)) {
        return NULL;
      }
    }

    // Return first discovered carousel.
    return $this->getComponentSchemaBuilder(reset($this->components));
  }

  /**
   * Helper method that gets supported components from layout builder config.
   *
   * @param array $element
   *   Node view render array.
   *
   * @return array
   *   Supported elements render arrays.
   */
  protected function getSupportedLayoutBuilderComponents(array $element) {
    $supported_plugin_ids = ['recommendations_module', 'pdp_hero_block'];
    $components = [];

    foreach (Element::children($element) as $delta) {
      $section = $element[$delta];

      if (!Element::isEmpty($section)) {
        /** @var \Drupal\Core\Layout\LayoutDefinition $layout */
        $layout = $section['#layout'];
        $regions = $layout->getRegionNames();

        foreach ($regions as $region) {
          if (isset($section[$region])) {
            foreach ($section[$region] as $component) {
              if (isset($component['#plugin_id']) && in_array($component['#plugin_id'], $supported_plugin_ids)) {
                $components[] = $component;
              }
            }
          }
        }
      }
    }

    return $components;
  }

  /**
   * Helper method that schema builder for page component.
   *
   * @param array $component
   *   Component render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   Thrown if node cannot be loaded from context.
   * @throws \Drupal\Core\Entity\EntityMalformedException
   *   Thrown if node URL cannot be generated.
   *
   * @return \Spatie\SchemaOrg\Type|null
   *   Structured Data Schema builder.
   */
  protected function getComponentSchemaBuilder(array $component) {
    switch ($component['#plugin_id']) {
      case 'recommendations_module':
        $items = $component['content']['#recommended_items'];

        return Schema::itemList()
          ->itemListElement(array_map(function ($delta, $value) {
            /** @var \Drupal\node\NodeInterface $node */
            $node = $value['#node'];

            return Schema::listItem()
              ->position($delta + 1)
              ->url($node->toUrl('canonical', ['absolute' => TRUE])->toString());
          }, array_keys($items), $items));

      case 'pdp_hero_block':
        /** @var \Drupal\node\NodeInterface $node */
        $node = $this->getContextValue('node');
        $variant_items = iterator_to_array($node->field_product_variants);
        $brand = !$this->configFactory->get('mars_common.system.site')->isNew() && !empty($this->configFactory->get('mars_common.system.site')->get('brand')) ? $this->configFactory->get('mars_common.system.site')->get('brand') : NULL;

        return Schema::itemList()
          ->name($node->getTitle())
          ->itemListElement(array_map(function ($delta, $item) use ($node, $brand) {
            $variant = $item->entity;
            $main_image_id = $this->mediaHelper->getEntityMainMediaId($variant);

            return Schema::listItem()
              ->position($delta + 1)
              ->item(
                Schema::product()
                  ->name($node->getTitle())
                  ->url($node->toUrl('canonical', ['absolute' => TRUE, 'fragment' => $variant->id()])->toString())
                  ->aggregateRating(Schema::aggregateRating()
                    ->ratingValue(5)
                    ->ratingCount(18)
                  )
                  ->sku($variant->field_product_sku->value)
                  ->if(!empty($brand), function (Product $product) use ($brand) {
                    $product->brand($brand);
                  })
                  ->if($node->field_product_description->value, function (Product $product) use ($node) {
                    $product->description($node->field_product_description->value);
                  })
                  ->if($main_image_id, function (Product $product) use ($main_image_id) {
                    $url = $this->mediaHelper->getMediaUrl($main_image_id);

                    if ($url) {
                      $product->image([$url]);
                    }
                  })
              );
          }, array_keys($variant_items), $variant_items));

      default:
        return NULL;
    }
  }

}
