<?php

namespace Drupal\mars_content_hub\EventSubscriber\DependencyCollector;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\depcalc\DependencyCalculatorEvents;
use Drupal\depcalc\Event\SectionComponentDependenciesEvent;
use Drupal\depcalc\EventSubscriber\DependencyCollector\BaseDependencyCollector;

/**
 * Class MediaEntityBlockCollector is responsible for dep calculation.
 */
class MediaEntityBlockCollector extends BaseDependencyCollector {

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
    if (is_array($config)) {
      $media_ids = $this->iterateConfig($config);
      $medias = $this->entityTypeManager->getStorage('media')->loadMultiple($media_ids);
      foreach ($medias as $media) {
        $event->addEntityDependency($media);
      }
    }
  }

  /**
   * Collect all media ids from block configuration.
   *
   * @param array $config
   *   Block configuration array.
   */
  private function iterateConfig(array $config) {
    $media_ids = [];
    foreach ($config as $element) {
      if (is_string($element) && strpos($element, 'media:') !== FALSE) {
        $media_ids[] = explode(':', $element)[1];
      }
      if (is_array($element)) {
        $media_ids = array_merge($media_ids, $this->iterateConfig($element));
      }
    }
    return $media_ids;
  }

}
