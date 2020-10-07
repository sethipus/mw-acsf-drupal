<?php

namespace Drupal\mars_recommendations\EventSubscriber;

use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\mars_recommendations\Event\AlterManualLogicBundlesEvent;
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
   * Limits autocomplete on Manual logic config form to MANUAL_PAGES bundles.
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
      RecommendationsEvents::ALTER_MANUAL_LOGIC_BUNDLES => ['setAutocompleteBundle'],
    ];
  }

}
