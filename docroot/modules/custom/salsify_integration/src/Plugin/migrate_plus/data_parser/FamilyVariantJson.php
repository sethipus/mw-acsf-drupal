<?php

namespace Drupal\salsify_integration\Plugin\migrate_plus\data_parser;

/**
 * Obtain JSON column for migration.
 *
 * @code
 * source:
 *   data_parser_plugin: family_variant_json
 *   family_field_check_empty: true
 *   column: field_product_variant_grp_title
 * @endcode
 *
 * @DataParser(
 *   id = "family_variant_json",
 *   title = @Translation("Family variant json")
 * )
 */
class FamilyVariantJson extends SalsifyJson {

  /**
   * {@inheritdoc}
   */
  protected function getSourceData($url) {
    $items = parent::getSourceData($url);

    if (isset($this->configuration['family_field_check_empty']) &&
          $this->configuration['family_field_check_empty']) {
      $column = $this->configuration['column'];
      foreach ($items as &$item) {
        if (!empty($item['field_variety'])) {
          continue;
        }
        $item[$column] = $this->familyIdIsNotEmpty($item, $column)
                ? [reset($item[$column])]
                : [reset($item['field_product_name'])];
      }
    }

    return $items;
  }

  /**
   * Check whether family id is empty or not.
   *
   * @param array $item
   *   Item array.
   * @param string $column
   *   Column name.
   *
   * @return bool
   *   Whether it's empty or not.
   */
  private function familyIdIsNotEmpty(array $item, string $column): bool {
    return isset($item[$column]) &&
      !empty($item[$column]) &&
      !empty(reset($item[$column]));
  }

}
