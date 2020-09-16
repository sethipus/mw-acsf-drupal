<?php

namespace Drupal\mars_recommendations;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Mars Node Recommendations Service.
 */
class RecommendationsService {

  /**
   * Recommendations Population Logic plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * RecommendationsService constructor.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   Recommendations Population Logic plugin manager.
   */
  public function __construct(PluginManagerInterface $plugin_manager) {
    $this->pluginManager = $plugin_manager;
  }

  /**
   * Returns a list of Recommendations Population logic flows.
   *
   * @return array
   *   Recommendations Population logic options.
   */
  public function getPopulationLogicOptions() {
    $result = [];

    foreach ($this->pluginManager->getDefinitions() as $definition) {
      $result[$definition['id']] = $definition['label'];
    }

    return $result;
  }

}
