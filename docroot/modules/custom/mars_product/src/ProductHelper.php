<?php

namespace Drupal\mars_product;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\TypedData\Exception\MissingDataException;

/**
 * Helper class for Product related logic.
 */
class ProductHelper {

  /**
   * Returns the main variant of a content if it's a product.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface|null $contentEntity
   *   The content entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   Returns the main variant of the product or NULL if it's not a product.
   */
  public function mainVariant(
    ?ContentEntityInterface $contentEntity
  ): ?ContentEntityInterface {
    if ($contentEntity === NULL || $contentEntity->bundle() !== 'product') {
      return NULL;
    }

    try {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $firstVariant */
      $firstVariant = $contentEntity
        ->get('field_product_variants')
        ->first()
        ->entity;
    }
    catch (MissingDataException $e) {
      $firstVariant = NULL;
    }

    return $firstVariant;
  }

}
