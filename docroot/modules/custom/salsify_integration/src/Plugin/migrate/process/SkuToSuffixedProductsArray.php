<?php

namespace Drupal\salsify_integration\Plugin\migrate\process;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Prepare array of technical skus of products as a part of multipack.
 *
 * @MigrateProcessPlugin(
 *   id = "sku_to_suffixed_products_array"
 * )
 */
class SkuToSuffixedProductsArray extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $result_value = [];
    foreach ($this->configuration['multipack_suffixes'] as $suffix) {
      $result_value[] = $value . '_' . $suffix;
    }
    return $result_value;
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }

}
