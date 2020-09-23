<?php

namespace Drupal\mars_recommendations;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for dynamic_recommendations_strategy plugins.
 */
abstract class DynamicRecommendationsStrategyPluginBase extends ContextAwarePluginBase implements DynamicRecommendationsStrategyInterface, ContainerFactoryPluginInterface {

  /**
   * Node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->nodeStorage = $entity_type_manager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackPluginId() {
    return $this->pluginDefinition['fallback_plugin'];
  }

  /**
   * {@inheritdoc}
   */
  abstract public function generate();

  /**
   * Loads fallback plugin in case of some missed conditions.
   *
   * @return \Drupal\mars_recommendations\DynamicRecommendationsStrategyInterface
   *   Fallback plugin.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   Thrown if fallback plugin not found.
   */
  protected function getFallbackPlugin() {
    /** @var \Drupal\mars_recommendations\DynamicRecommendationsStrategyPluginManager $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.dynamic_recommendations_strategy');

    /** @var \Drupal\mars_recommendations\DynamicRecommendationsStrategyInterface $plugin */
    $plugin = $plugin_manager->createInstance($this->getFallbackPluginId() ?? 'default');
    $plugin->setContext('node', $this->getContext('node'));

    return $plugin;
  }

}
