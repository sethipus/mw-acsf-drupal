<?php

namespace Drupal\salsify_integration\Plugin\migrate_plus\data_parser;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Json;
use Drupal\salsify_integration\Form\ConfigForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Obtain JSON data for migration.
 *
 * @DataParser(
 *   id = "salsify_json",
 *   title = @Translation("JSON")
 * )
 */
class SalsifyJson extends Json {

  /**
   * The theme config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $config_factory->get('salsify_integration.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function currentUrl(): ?string {
    $keys = array_keys($this->urls);
    $index = $this->activeUrl ?: array_shift($keys);
    return isset($this->urls[$index]) ? $this->urls[$index] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSourceData($url) {
    $response = $this->getDataFetcherPlugin()->getResponseContent($url);
    // Convert objects to associative arrays.
    $source_data = json_decode($response, TRUE);
    $brand_name = $this->config->get('salsify_multichannel_approach.' . ConfigForm::BRAND_NAME);
    $brand_name_trimmed = $this->brandConfigToArray($brand_name);
    // Check is brand name established in
    // configuration.
    if (!empty($brand_name_trimmed)) {
      foreach ($source_data as $key => $product) {
        $product_brand_name = isset($source_data[$key]['field_brand_name']) ? reset($source_data[$key]['field_brand_name']) : '';
        // Unset products without brand field or
        // not equals to brand from configuration.
        if (!in_array(strtolower($product_brand_name), $brand_name_trimmed)) {
          unset($source_data[$key]);
        }
      }
    }

    // If json_decode() has returned NULL, it might be that the data isn't
    // valid utf8 - see http://php.net/manual/en/function.json-decode.php#86997.
    if (is_null($source_data)) {
      $utf8response = utf8_encode($response);
      $source_data = json_decode($utf8response, TRUE);
    }
    // Backwards-compatibility for depth selection.
    if (is_int($this->itemSelector)) {
      return $this->selectByDepth($source_data);
    }

    // Otherwise, we're using xpath-like selectors.
    $selectors = explode('/', trim($this->itemSelector, '/'));
    foreach ($selectors as $selector) {
      if (!empty($selector)) {
        $source_data = $source_data[$selector];
      }
    }
    return $source_data;
  }

  /**
   * Convert string brand name value to array.
   *
   * @param string $brand_name
   *   Brand name config value.
   *
   * @return array
   *   Array of brand names.
   */
  private function brandConfigToArray(string $brand_name): array {
    $brand_name_trimmed = explode(', ', trim($brand_name));

    return array_map(function ($value) {
      return strtolower($value);
    }, $brand_name_trimmed);
  }

}
