<?php

namespace Drupal\mars_recommendations\EventSubscriber;

use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\mars_recommendations\Event\AlterManualLogicBundlesEvent;
use Drupal\mars_recommendations\Event\AlterPopulationLogicOptionsEvent;
use Drupal\mars_recommendations\RecommendationsEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Mars Recommendations event subscriber.
 */
class ManualPagesSubscriber implements EventSubscriberInterface {

  const MANUAL_PAGES = [
    'landing_page',
    'campaign',
  ];


  /**
   * Unsets dynamic option for MANUAL_PAGES array.
   *
   * @param \Drupal\mars_recommendations\Event\AlterPopulationLogicOptionsEvent $event
   *   Alter options event.
   */
  public function disableDynamicForPages(AlterPopulationLogicOptionsEvent $event) {
    if (!($entity = $event->getEntity())) {
      return;
    }
    $bundle = $entity instanceof LayoutBuilderEntityViewDisplay ? $entity->getTargetBundle() : $entity->bundle();
    if (!in_array($bundle, self::MANUAL_PAGES)) {
      return;
    }

    $event->setDefinitions(
      array_filter(
        $event->getDefinitions(),
        function ($definition) {
          return $definition['id'] !== 'dynamic';
        }
      )
    );
  }

  /**
   * Limits autocomplete on Manual logic config form to Content Hub Page bundle.
   *
   * @param \Drupal\mars_recommendations\Event\AlterManualLogicBundlesEvent $event
   *   Alter bundles event.
   */
  public function setAutocompleteBundle(AlterManualLogicBundlesEvent $event) {
    if (!($entity = $event->getEntity())) {
      return;
    }

    $bundle = $entity instanceof LayoutBuilderEntityViewDisplay ? $entity->getTargetBundle() : $entity->bundle();
    if (!in_array($bundle, self::MANUAL_PAGES)) {
      return;
    }

    $event->setBundles([$bundle]);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      RecommendationsEvents::ALTER_POPULATION_LOGIC_OPTIONS => [
        ['disableDynamicForPages', 0],
      ],
      RecommendationsEvents::ALTER_MANUAL_LOGIC_BUNDLES => ['setAutocompleteBundle'],
    ];
  }
}
