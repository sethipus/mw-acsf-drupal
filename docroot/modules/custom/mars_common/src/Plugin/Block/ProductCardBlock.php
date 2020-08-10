<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a product card block.
 *
 * @Block(
 *   id = "product_card",
 *   admin_label = @Translation("Product Card Block"),
 *   category = @Translation("Custom")
 * )
 */
class ProductCardBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#theme'] = 'product_card_block';

    return $build;
  }

}
