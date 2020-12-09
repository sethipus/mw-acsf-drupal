<?php

namespace Drupal\mars_google_analytics\DataCollector;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_google_analytics\Entity\EntityDecorator;

/**
 * Class ProductsDataCollector.
 */
class ProductsDataCollector implements DataCollectorInterface, DataLayerCollectorInterface {

  const PRODUCT_CONTENT_TYPE = 'product';

  /**
   * Entity Type manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  private $entityManager;

  /**
   * Collection of data.
   *
   * @var array
   */
  private $data = [];

  /**
   * Constructor method.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *   Entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityManager) {
    $this->entityManager = $entityManager;

    $this->data['products']['rendered'] = [];
  }

  /**
   * Get data layer id.
   *
   * @return string
   *   Data layer param id.
   */
  public function getDataLayerId() {
    return 'products';
  }

  /**
   * Collect rendered product nodes.
   */
  public function collect() {
    $rendered = $this->entityManager->getRendered('node');

    if ($rendered) {
      $this->data['products']['rendered'] = array_merge(
        $this->data['products']['rendered'],
        $this->getProductsData($rendered)
      );
    }
  }

  /**
   * Get rendered products.
   *
   * @return array
   *   Rendered products.
   */
  public function getRenderedProducts() {
    return $this->data['products']['rendered'];
  }

  /**
   * Add rendered products.
   *
   * @param mixed $gtin
   *   Gtin.
   */
  public function addRenderedProduct($gtin) {
    $this->data['products']['rendered'][$gtin] = $gtin;
  }

  /**
   * Get product related data.
   *
   * @param \Drupal\mars_google_analytics\Entity\EntityDecorator $decorator
   *   Decorator.
   *
   * @return array
   *   Array of product gtins.
   */
  private function getProductsData(EntityDecorator $decorator) {
    $products = [];

    /** @var \Drupal\node\NodeInterface $node */
    foreach ($decorator->getEntities() as $node) {
      if (isset($node) &&
        $node->bundle() == self::PRODUCT_CONTENT_TYPE &&
        $node->hasField('salsify_id')
      ) {
        $variants = $node
          ->get('field_product_variants')
          ->referencedEntities();
        $variant = reset($variants);
        if ($variant !== FALSE) {
          $product_sku = $variant->get('field_product_sku')->value;
          $products[$product_sku] = $product_sku;
        }
      }
    }

    return $products;
  }

  /**
   * Generate Google Analytics data string.
   *
   * @return string|null
   *   Google Analytics data.
   */
  public function getGaData() {
    $ga_data = NULL;

    if (
      !empty($this->data['products']['rendered']) &&
      is_array($this->data['products']['rendered']) &&
      count($this->data['products']['rendered']) > 0
    ) {
      $ga_data = array_filter($this->data['products']['rendered']);
      $ga_data = count($ga_data) > 1 ? implode(', ', $ga_data) : reset($ga_data);
    }

    return $ga_data;
  }

}
