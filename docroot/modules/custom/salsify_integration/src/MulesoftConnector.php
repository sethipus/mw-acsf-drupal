<?php

namespace Drupal\salsify_integration;

use Drupal\Component\Serialization\Json;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\Exception\MissingDataException;

/**
 * Class MulesofConnector.
 *
 * @package Drupal\salsify_integration
 */
class MulesoftConnector {

  use StringTranslationTrait;

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
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function transformData(string $response) {
    // Filter products and product fields in order
    // to reduce memory usage.
    $response = $this->productHelper
      ->filterProductsInResponse($response);
    $response = $this->productHelper
      ->filterProductFields($response);

    // Process product variants in response in order to populate
    // data by products and product multipacks.
    $response = $this->productHelper->addProducts($response);
    $response = $this->productHelper->addProductMultipacks($response);
    $response = $this->productHelper->addProductDualLabel($response);

    $data = [
      'attributes' => $this->productHelper->getAttributesByProducts($response),
      'attribute_values' => $this->productHelper->getAttributeValuesByProducts($response),
      'digital_assets' => $this->productHelper->getDigitalAssetsByProducts($response),
      'mapping' => $this->productHelper->getPrimaryMapping(),
    ];

    $response_array = Json::decode($response);

    if (empty($response_array['data'])) {
      $message = $this->t('Empty data set for the Import.');
      throw new MissingDataException($message);
    }

    $data['products'] = $response_array['data'] ?? [];
    $data['market'] = $response_array['country'] ?? NULL;

    return $data;
  }

}
