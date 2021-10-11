<?php

namespace Drupal\salsify_integration\Plugin\migrate_plus\data_parser;

/**
 * Obtain JSON column for migration.
 *
 * @code
 * source:
 *   data_parser_plugin: multipack_json_filter
 *   filter: callable
 *   multipack_suffix: integer
 * @endcode
 *
 * @DataParser(
 *   id = "multipack_json_filter",
 *   title = @Translation("Multipack JSON filter")
 * )
 */
class MultipackJsonFilter extends JsonFilter {

  /**
   * {@inheritdoc}
   */
  protected function getSourceData($url) {
    $items = parent::getSourceData($url);

    $items = array_filter($items, [$this, 'itemIsNotEmpty']);

    return array_map(function ($item) {
      $item['field_product_sku'] = !empty($item['field_product_sku'])
        ? [reset($item['field_product_sku']) . '_' . $this->configuration['multipack_suffix']]
        : [];
      return $item;
    }, $items);
  }

  /**
   * Check whether item is not empty or not.
   *
   * @param array $item
   *   Item array.
   *
   * @return bool
   *   Is not empty
   */
  public function itemIsNotEmpty(array $item): bool {
    $filtered_item = array_filter($item, function ($field_value, $field_key) {
      if (preg_match('/_' . $this->configuration['multipack_suffix'] . '$/', $field_key) &&
        !preg_match('/^field_product_image/', $field_key) &&
        !empty(reset($field_value))) {

        return TRUE;
      }
      return FALSE;
    }, ARRAY_FILTER_USE_BOTH);

    return !empty($filtered_item);
  }

}
