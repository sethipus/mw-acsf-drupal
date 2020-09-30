<?php

namespace Drupal\mars_seo;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Mars JSON LD Strategy plugin manager.
 */
class JsonLdStrategyPluginManager extends DefaultPluginManager {

  /**
   * Static cache for discovered plugins mapping.
   *
   * @var array
   */
  protected $bundlePlugins = [];

  /**
   * Constructs MarsJsonLdStrategyPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/JsonLdStrategy',
      $namespaces,
      $module_handler,
      'Drupal\mars_seo\JsonLdStrategyInterface',
      'Drupal\mars_seo\Annotation\JsonLdStrategy'
    );
    $this->alterInfo('mars_json_ld_strategy_info');
    $this->setCacheBackend($cache_backend, 'mars_json_ld_strategy_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function getInstance(array $options) {
    if (empty($options['bundle'])) {
      return FALSE;
    }

    $bundle = $options['bundle'];

    if (!empty($this->bundlePlugins[$bundle])) {
      $plugin_id = end($this->bundlePlugins[$bundle]);

      return $this->createInstance($plugin_id);
    }

    foreach ($this->getDefinitions() as $definition) {
      foreach ($definition['bundles'] as $plugin_bundle) {
        $this->bundlePlugins[$plugin_bundle][] = $definition['id'];
      }

      if (in_array($bundle, $definition['bundles'])) {
        return $this->createInstance($definition['id']);
      }
    }

    return FALSE;
  }

}
