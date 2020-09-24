<?php

namespace Drupal\mars_recommendations;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\mars_recommendations\Exception\RecommendationsLogicPluginNotFoundException;

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
   * @param string|null $zone_type
   *   Filters options by zone type if value is set.
   *
   * @return array
   *   Recommendations Population logic options.
   */
  public function getPopulationLogicOptions($zone_type = NULL) {
    $result = [];

    foreach ($this->pluginManager->getDefinitions() as $definition) {
      if (!isset($zone_type) || in_array($zone_type, $definition['zone_types'])) {
        $result[$definition['id']] = $definition['label'];
      }
    }

    return $result;
  }

  /**
   * Factory method for Recommendations Population Logic plugin.
   *
   * @param string $plugin_id
   *   Recommendations Population Logic plugin id.
   * @param array $plugin_configuration
   *   Plugin configuration.
   *
   * @throws \Drupal\mars_recommendations\Exception\RecommendationsLogicPluginNotFoundException|\Drupal\Component\Plugin\Exception\PluginException
   *   Thrown in case Recommendations Logic plugin not found.
   *
   * @return \Drupal\mars_recommendations\RecommendationsLogicPluginInterface
   *   Recommendations Population Logic plugin.
   */
  public function getPopulationLogicPlugin($plugin_id, array $plugin_configuration = []) {
    if (!$this->pluginManager->hasDefinition($plugin_id)) {
      throw new RecommendationsLogicPluginNotFoundException(sprintf('Recommendations Logic plugin <em>%s</em> not found.', $plugin_id));
    }

    return $this->pluginManager->createInstance($plugin_id, $plugin_configuration);
  }

}
