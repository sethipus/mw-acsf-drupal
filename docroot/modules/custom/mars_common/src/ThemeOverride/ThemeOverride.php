<?php

namespace Drupal\mars_common\ThemeOverride;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\layout_builder\SectionComponent;
use Drupal\mars_common\Cache\Context\ThemeOverrideContext;
use Drupal\mars_common\Utils\NodeLBComponentIterator;
use Drupal\node\NodeInterface;

/**
 * Contain details about a theme override.
 */
class ThemeOverride implements CacheableDependencyInterface {

  const THEME_CONFIGURATION_BLOCK_ID = 'theme_configuration_block';

  const EMPTY_ID = 'empty';

  const BLOCK_CONFIG_THEME_PARENT_KEYS = [
    'color_settings',
    'font_settings',
    'icons_settings',
    'product_layout',
  ];

  /**
   * Creates an empty ThemeOverride object.
   *
   * @return ThemeOverride
   *   The empty theme override object.
   */
  public static function createEmpty(): self {
    return new self(self::EMPTY_ID);
  }

  /**
   * Creates an override object based on a given node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return ThemeOverride
   *   The ThemeOverride object.
   */
  public static function createFromNode(NodeInterface $node): self {
    foreach (new NodeLBComponentIterator($node) as $component) {
      try {
        $override_values = static::getOverrideValues($component);
      }
      catch (PluginException $e) {
        // Skip this component.
      }
      if (!empty($override_values)) {
        $cache_tag = 'node:' . $node->id();
        return new ThemeOverride($cache_tag, $override_values, [$cache_tag]);
      }
    }
    return static::createEmpty();
  }

  /**
   * Extracts theme configuration from a layout builder block.
   *
   * @param \Drupal\layout_builder\SectionComponent $component
   *   Layout builder block.
   *
   * @return array
   *   Theme configuration.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private static function getOverrideValues(
    SectionComponent $component
  ): array {
    $override_values = [];

    $configuration = $component->get('configuration') ?? NULL;
    if ($configuration['id'] == self::THEME_CONFIGURATION_BLOCK_ID) {
      $override_values = self::removeParentKeys($configuration);
    }

    return $override_values;
  }

  /**
   * Removes parent keys from config override block configuration.
   *
   * @param array $component_configuration
   *   The component configuration array.
   *
   * @return array
   *   The resulting array without the parent keys.
   */
  private static function removeParentKeys(array $component_configuration): array {
    foreach (self::BLOCK_CONFIG_THEME_PARENT_KEYS as $key) {
      $component_configuration = array_merge(
        $component_configuration,
        $component_configuration[$key] ?? []
      );
    }
    return $component_configuration;
  }

  /**
   * The override values.
   *
   * @var array
   */
  private $values;

  /**
   * The override id.
   *
   * @var string
   */
  private $id;

  /**
   * The cache tags of this override.
   *
   * @var array
   */
  private $cacheTags;

  /**
   * ThemeOverride constructor.
   *
   * @param string $id
   *   The unique id of the override.
   * @param array $override_values
   *   The override values.
   * @param string[] $cache_tags
   *   The cache tags for the override.
   */
  public function __construct(
    string $id,
    array $override_values = [],
    array $cache_tags = []
  ) {
    $this->values = $override_values;
    $this->id = $id;
    $this->cacheTags = $cache_tags;
  }

  /**
   * Returns the unique id of the override.
   *
   * @return string
   *   Unique id of the override.
   */
  public function id(): string {
    return $this->id;
  }

  /**
   * Decides if the current theme override is empty or not.
   *
   * @return bool
   *   The result.
   */
  public function isEmpty(): bool {
    return empty($this->values);
  }

  /**
   * Checks if the override has value for the given field.
   *
   * @param string $field_name
   *   The field name.
   *
   * @return bool
   *   The result.
   */
  public function valueExists(string $field_name) {
    return !empty($this->values[$field_name]);
  }

  /**
   * Returns the override value for the given field.
   *
   * @param string $field_name
   *   The field name.
   *
   * @return mixed|null
   *   The override value or NULL of it does not exits.
   */
  public function getValue(string $field_name) {
    return $this->values[$field_name] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return [
      ThemeOverrideContext::NAME,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return $this->cacheTags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

}
