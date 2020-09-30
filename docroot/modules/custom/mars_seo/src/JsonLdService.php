<?php

namespace Drupal\mars_seo;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * JSON LD provider service.
 */
class JsonLdService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Mars JSON LD Strategy plugin manager.
   *
   * @var \Drupal\mars_seo\JsonLdStrategyPluginManager
   */
  protected $pluginManager;

  /**
   * Constructs a JsonLdService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\mars_seo\JsonLdStrategyPluginManager $strategy_plugin_manager
   *   Mars JSON LD Strategy plugin manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, JsonLdStrategyPluginManager $strategy_plugin_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->pluginManager = $strategy_plugin_manager;
  }

  /**
   * Returns structured data for node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Drupal node.
   * @param array $build
   *   Node build array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   Thrown if plugin context cannot be set.
   *
   * @return array|null
   *   Structured data array.
   */
  public function getStructuredData(NodeInterface $node, array $build = []) {
    /** @var \Drupal\mars_seo\JsonLdStrategyInterface $plugin */
    if (!($plugin = $this->pluginManager->getInstance(['bundle' => $node->bundle()]))) {
      return;
    }

    $plugin->setContextValue('node', $node);
    $plugin->setContextValue('build', $build);

    return $plugin->getStructuredData();
  }

}
