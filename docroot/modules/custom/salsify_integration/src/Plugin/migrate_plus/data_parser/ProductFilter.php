<?php

namespace Drupal\salsify_integration\Plugin\migrate_plus\data_parser;

/**
 * Obtain JSON column for migration.
 *
 * @code
 * source:
 *   data_parser_plugin: product_filter
 *   filter: callable
 *   column: string
 * @endcode
 *
 * @DataParser(
 *   id = "product_filter",
 *   title = @Translation("Product filter")
 * )
 */
class ProductFilter extends JsonFilter
{
  /**
   * Products aggregatec by family id.
   *
   * @var array
   */
    private array $aggregatedProducts;

    /**
     * {@inheritdoc}
     */
    protected function getSourceData($url)
    {
        $items = parent::getSourceData($url);
        $column = $this->configuration['column'];

        foreach ($items as $item) {
            if (!empty($item['field_variety'])) {
                continue;
            }
            if ($this->familyIdIsNotEmpty($item, $column)) {
                $family_id = reset($item[$column]);
                $this->aggregatedProducts[$family_id] = $this->compareAndGetMasterProduct($item);
            } else {
                $item[$column] = [reset($item['field_product_name'])];
                $this->aggregatedProducts[] = $item;
            }
        }

        return !empty($this->aggregatedProducts) ? array_values($this->aggregatedProducts) : [];
    }

    /**
     * Compare current item with existing one and get master.
     *
     * @param array $item
     *   Item array.
     *
     * @return array
     *   Result item.
     */
    private function compareAndGetMasterProduct(array $item): array
    {
        $column = $this->configuration['column'];
        $family_id = reset($item[$column]);

        if (!isset($this->aggregatedProducts[$family_id]) ||
      $this->isFamilyMaster($item)) {
            return $item;
        } else {
            return $this->aggregatedProducts[$family_id];
        }
    }

    /**
     * Whether product is master or not.
     *
     * @param array $item
     *   Item array.
     *
     * @return bool
     *   Master or not.
     */
    private function isFamilyMaster(array $item): bool
    {
        return (isset($item['field_pdt_var_grp_primary']) &&
      !empty($item['field_pdt_var_grp_primary']) &&
      (strtolower(reset($item['field_pdt_var_grp_primary'])) == 'yes'));
    }

    /**
     * Whether size of current product is less or not.
     *
     * @param array $item
     *   Item array.
     *
     * @return bool
     *   Master or not.
     */
    private function sizeIsLess(array $item): bool
    {
        $column = $this->configuration['column'];
        $family_id = reset($item[$column]);
        $item_size = $this->getSizeByItem($item);
        $size_to_compare = $this->getSizeByItem($this->aggregatedProducts[$family_id]);

        if (!is_numeric($size_to_compare) ||
      (is_numeric($item_size) && ($item_size <= $size_to_compare))) {
            $is_less = true;
        } else {
            $is_less = false;
        }

        return $is_less;
    }

    /**
     * Get size by item array.
     *
     * @param array $item
     *   Item array.
     *
     * @return mixed
     *   Size value which might be float or null.
     */
    private function getSizeByItem(array $item)
    {
        $size = (isset($item['field_product_size']) &&
      !empty($item['field_product_size']))
      ? reset($item['field_product_size'])
      : null;
        return $this->sizeToNumeric($size);
    }

    /**
     * Convert size to float value.
     *
     * @param mixed $size
     *   Size value.
     *
     * @return float|mixed|string|null
     *   Converted value.
     */
    private function sizeToNumeric($size)
    {
        return (is_numeric($size))
      ? (float) $size
      : (is_string($size) ? explode(' ', $size)[0] : null);
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
    private function familyIdIsNotEmpty(array $item, string $column): bool
    {
        return isset($item[$column]) &&
      !empty($item[$column]) &&
      !empty(reset($item[$column]));
    }
}
