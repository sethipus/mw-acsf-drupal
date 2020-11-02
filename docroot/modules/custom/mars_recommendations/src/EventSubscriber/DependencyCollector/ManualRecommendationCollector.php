<?php

namespace Drupal\mars_recommendations\EventSubscriber\DependencyCollector;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\depcalc\DependencyCalculatorEvents;
use Drupal\depcalc\DependentEntityWrapper;
use Drupal\depcalc\Event\CalculateEntityDependenciesEvent;
use Drupal\depcalc\EventSubscriber\DependencyCollector\BaseDependencyCollector;

/**
 * Class ManualRecommendationCollector.
 */
class ManualRecommendationCollector extends BaseDependencyCollector {

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
    $events[DependencyCalculatorEvents::CALCULATE_DEPENDENCIES][] = ['onCalculateDependencies'];
    return $events;
  }

  /**
   * Add recommended entities to dependency calculation.
   *
   * @param \Drupal\depcalc\Event\CalculateEntityDependenciesEvent $event
   *   The CalculateEntityDependenciesEvent event.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function onCalculateDependencies(CalculateEntityDependenciesEvent $event) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $event->getEntity();
    if (!$entity instanceof ContentEntityInterface) {
      return;
    }
    if (!$entity->hasField('layout_builder__layout')) {
      return;
    }

    /** @var \Drupal\layout_builder\Field\LayoutSectionItemList $layoutBuilderField */
    $layoutBuilderField = $entity->get('layout_builder__layout');
    /** @var \Drupal\layout_builder\Section[] $sections */
    $sections = $layoutBuilderField->getSections();

    // @TODO get all components
    foreach ($sections as $section) {
      $components = $section->getComponents();
      foreach ($components as $component) {
        $config = $component->get('configuration');
        if ($component->getPluginId() == 'recommendations_module' && $config['population_plugin_id'] == 'manual') {
          foreach ($config['nodes'] as $nid) {
            /** @var \Drupal\core\Entity\EntityInterface $node */
            $node = $this->entityTypeManager->getStorage('node')->load($nid);
            if (!$event->getStack()->hasDependency($node->uuid())) {
              $parent_wrapper = new DependentEntityWrapper($node);
              $local_dependencies = [];
              $this->mergeDependencies($parent_wrapper, $event->getStack(), $this->getCalculator()
                ->calculateDependencies($parent_wrapper, $event->getStack(), $local_dependencies));
              $event->addDependency($parent_wrapper);
            }
          }
        }
      }
    }
  }

}
