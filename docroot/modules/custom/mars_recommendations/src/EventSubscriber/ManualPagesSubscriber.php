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
    'article',
    'campaign',
    'content_hub_page',
    'landing_page',
    'product',
    'product_multipack',
    'recipe',
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

    // Passing const value to the variable to have an ability to manage items.
    $available_bundles = self::MANUAL_PAGES;
    // AB#223856: Unset landing page CT if the current bundle is campaign page.
    if ($bundle === 'campaign') {
      unset($available_bundles['landing_page']);
    }
    // Pass all content types to the autocomplete.
    $event->setBundles($available_bundles);
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
