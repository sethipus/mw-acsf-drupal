<?php

namespace Drupal\mars_recommendations\Plugin\DynamicRecommendationsStrategy;

/**
 * Product Multipack Dynamic recommendations strategy plugin implementation.
 *
 * @DynamicRecommendationsStrategy(
 *   id = "product_multipack",
 *   label = @Translation("Product Multipack"),
 *   description = @Translation("Product Dynamic recommendations strategy that returns nodes with the same Flavor or Format."),
 *   fallback_plugin = "default",
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   }
 * )
 */
class ProductMultipack extends Product {}
