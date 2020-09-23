<?php

namespace Drupal\mars_recommendations;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Recommendations Logic plugin manager.
 */
class RecommendationsLogicPluginManager extends DefaultPluginManager {

  /**
   * Constructs Mars Recommendations Login Plugin Manager.
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
      'Plugin/MarsRecommendationsLogic',
      $namespaces,
      $module_handler,
      'Drupal\mars_recommendations\RecommendationsLogicPluginInterface',
      'Drupal\mars_recommendations\Annotation\MarsRecommendationsLogic'
    );
    $this->alterInfo('mars_recommendations_logic_info');
    $this->setCacheBackend($cache_backend, 'mars_recommendations_logic_plugins');
  }

}
