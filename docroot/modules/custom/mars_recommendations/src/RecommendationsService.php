<?php

namespace Drupal\mars_recommendations;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\mars_recommendations\Event\AlterPopulationLogicOptionsEvent;
use Drupal\mars_recommendations\Exception\RecommendationsLogicPluginNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Mars Node Recommendations Service.
 */
class RecommendationsService {

  /**
   * Symfony event dispatcher interface.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Recommendations Population Logic plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * RecommendationsService constructor.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Symfony event dispatcher.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   Recommendations Population Logic plugin manager.
   */
  public function __construct(EventDispatcherInterface $event_dispatcher, PluginManagerInterface $plugin_manager) {
    $this->eventDispatcher = $event_dispatcher;
    $this->pluginManager = $plugin_manager;
  }

  /**
   * Returns a list of Recommendations Population logic flows.
   *
   * @param string|null $layout_id
   *   Entity bundle.
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   Node or Layout Builder Display entity.
   *
   * @return array
   *   Recommendations Population logic options.
   */
  public function getPopulationLogicOptions($layout_id = NULL, EntityInterface $entity = NULL) {
    $result = [];

    $definitions = $this->pluginManager->getDefinitions();

    $event = new AlterPopulationLogicOptionsEvent($definitions, $layout_id, $entity);
    $this->eventDispatcher->dispatch(RecommendationsEvents::ALTER_POPULATION_LOGIC_OPTIONS, $event);

    foreach ($event->getDefinitions() as $definition) {
      $result[$definition['id']] = $definition['label'];
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
