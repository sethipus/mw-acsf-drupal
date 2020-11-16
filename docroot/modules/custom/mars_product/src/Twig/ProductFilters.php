<?php

namespace Drupal\mars_product\Twig;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\mars_product\ProductHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class that adds product twig filters.
 */
class ProductFilters extends AbstractExtension {

  /**
   * The media helper service.
   *
   * @var \Drupal\mars_product\ProductHelper
   */
  private $productHelper;

  /**
   * MediaHelperFilters constructor.
   *
   * @param \Drupal\mars_product\ProductHelper $product_helper
   *   The media helper service.
   */
  public function __construct(ProductHelper $product_helper) {
    $this->productHelper = $product_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return [
      new TwigFilter('mainVariant', [$this, 'mainVariant']),
      new TwigFilter('productCardMedia', [$this, 'productCardMedia']),
    ];
  }

  /**
   * Returns the main variant of a content if it's a product.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface|null $contentEntity
   *   The content entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   The media ID or NULL if it has none or the entity was NULL.
   */
  public function mainVariant(
    ?ContentEntityInterface $contentEntity
  ): ?ContentEntityInterface {
    return $this->productHelper->mainVariant($contentEntity);
  }

  /**
   * Filter to return the product card media id if there are any.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface|null $contentEntity
   *   The entity that we are processing for product card media.
   *
   * @return string|null
   *   The media ID or NULL if it has none or the entity was NULL.
   */
  public function productCardMedia(
    ?ContentEntityInterface $contentEntity
  ): ?string {
    if ($contentEntity === NULL || $contentEntity->bundle() !== 'product_variant') {
      return NULL;
    }

    $media_id = NULL;
    if (!$contentEntity->get('field_product_card_imageoverride')->isEmpty()) {
      $media_id = $contentEntity
        ->get('field_product_card_imageoverride')
        ->first()
        ->target_id;
    }

    return $media_id;
  }

}
