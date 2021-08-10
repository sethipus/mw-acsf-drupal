<?php

namespace Drupal\mars_content_hub\EventSubscriber\DependencyCollector;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\depcalc\DependencyCalculatorEvents;
use Drupal\depcalc\Event\SectionComponentDependenciesEvent;
use Drupal\depcalc\EventSubscriber\DependencyCollector\BaseDependencyCollector;

/**
 * Class ManualRecommendationBlockCollector is responsible for dep calculation.
 */
class ManualRecommendationBlockCollector extends BaseDependencyCollector {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * TermParentCollector constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[DependencyCalculatorEvents::SECTION_COMPONENT_DEPENDENCIES_EVENT][] = ['onCalculateSectionComponentDependencies'];
    return $events;
  }

  /**
   * Calculates the entities referenced on Recommendation component in LB.
   *
   * @param \Drupal\depcalc\Event\SectionComponentDependenciesEvent $event
   *   The dependency calculation event.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function onCalculateSectionComponentDependencies(SectionComponentDependenciesEvent $event) {
    $component = $event->getComponent();
    $config = $component->get('configuration');
    $productCT = ['product', 'product_variant', 'product_multipack'];
    if ($component->getPluginId() == 'recommendations_module' && $config['population_plugin_id'] == 'manual') {
      foreach ($config['population_plugin_configuration']['nodes'] as $nid) {
        /** @var \Drupal\core\Entity\EntityInterface $node */
        $node = $this->entityTypeManager->getStorage('node')->load($nid);
        if ($node !== NULL && !in_array($node->bundle(), $productCT)) {
          $event->addEntityDependency($node);
        }
      }
    }
  }

}
