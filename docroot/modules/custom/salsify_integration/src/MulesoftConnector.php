<?php

namespace Drupal\salsify_integration;

use Drupal\Component\Serialization\Json;

/**
 * Class MulesofConnector.
 *
 * @package Drupal\salsify_integration
 */
class MulesoftConnector {

  /**
   * The Product helper class.
   *
   * @var \Drupal\salsify_integration\ProductHelper
   */
  protected $productHelper;

  /**
   * Constructs a \Drupal\salsify_integration\MulesoftConnector object.
   *
   * @param \Drupal\salsify_integration\ProductHelper $product_helper
   *   The product helper service.
   */
  public function __construct(
    ProductHelper $product_helper
  ) {
    $this->productHelper = $product_helper;
  }

  /**
   * Transform Mulesoft data to Salsify format.
   *
   * @param string $response
   *   Response string.
   *
   * @return array
   *   Data array.
   */
  public function transformData(string $response) {
    $mapping = $this->productHelper
      ->getParentEntitiesMapping($response);
    $response = $this->productHelper
      ->filterProductsInResponse($response);
    $data = [
      'attributes' => $this->productHelper->getAttributesByProducts($response),
      'attribute_values' => $this->productHelper->getAttributeValuesByProducts($response),
      'digital_assets' => $this->productHelper->getDigitalAssetsByProducts($response),
      'mapping' => $mapping,
    ];

    $response_array = Json::decode($response);
    $data['products'] = $response_array['data'] ?? [];
    $data['market'] = $response_array['country'] ?? NULL;

    return $data;
  }

}
