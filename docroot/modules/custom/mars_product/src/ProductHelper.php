<?php

namespace Drupal\mars_product;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\mars_common\LanguageHelper;

/**
 * Helper class for Product related logic.
 */
class ProductHelper {

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * ProductHelper constructor.
   *
   * @param \Drupal\mars_common\LanguageHelper $language_helper
   *   The Language helper service.
   */
  public function __construct(LanguageHelper $language_helper) {
    $this->languageHelper = $language_helper;
  }

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

      $firstVariant = $this->languageHelper->getTranslation($firstVariant);
    }
    catch (MissingDataException $e) {
      $firstVariant = NULL;
    }

    return $firstVariant;
  }

}
