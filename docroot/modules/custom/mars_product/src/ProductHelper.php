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
    if ($contentEntity === NULL ||
      !in_array($contentEntity->bundle(), ['product', 'product_multipack'])
    ) {
      return NULL;
    }

    try {
      $variants = $contentEntity
        ->get('field_product_variants')
        ->referencedEntities();

      /** @var \Drupal\Core\Entity\ContentEntityInterface $main_variant */
      $main_variant = NULL;
      $main_variant_size = NULL;

      foreach ($variants as $variant) {
        $is_master = $variant->get('field_product_family_master')->value;
        $size = $variant->get('field_product_size')->value;
        $size = (is_numeric($size)) ? (float) $size : (is_string($size) ?
          explode(' ', $size)[0] : NULL);

        if ($is_master) {
          $main_variant = $variant;
          break;
        }
        elseif ($main_variant && !is_numeric($main_variant_size) &&
          is_numeric($size)) {

          $main_variant = $variant;
          $main_variant_size = $size;
        }
        elseif ($main_variant && is_numeric($main_variant_size) &&
           is_numeric($size) && ($size < $main_variant_size)) {

          $main_variant = $variant;
          $main_variant_size = $size;
        }
        elseif (!$main_variant) {
          $main_variant = $variant;
          $main_variant_size = $size;
        }

      }

      if ($main_variant instanceof ContentEntityInterface) {
        $main_variant = $this->languageHelper->getTranslation($main_variant);
      }
    }
    catch (MissingDataException $e) {
      $main_variant = NULL;
    }

    return $main_variant;
  }

}
