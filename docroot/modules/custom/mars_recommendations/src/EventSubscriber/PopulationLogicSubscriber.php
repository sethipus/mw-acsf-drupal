<?php

namespace Drupal\mars_recommendations\EventSubscriber;

use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\mars_recommendations\Event\PostLoadPopulationLogicPluginsEvent;
use Drupal\mars_recommendations\RecommendationsEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Mars Recommendations event subscriber.
 */
class PopulationLogicSubscriber implements EventSubscriberInterface {

  /**
   * Unsets plugin definitions that do not match current zone.
   *
   * @param \Drupal\mars_recommendations\Event\PostLoadPopulationLogicPluginsEvent $event
   *   Post load event.
   */
  public function validateZoneMatch(PostLoadPopulationLogicPluginsEvent $event) {
    if (!$event->getEntity() || !$event->getLayoutId()) {
      return;
    }

    $form_alter_class = mars_common_get_layout_alter_class($event->getEntity());
    $is_fixed_section = in_array($event->getLayoutId(), constant("$form_alter_class::FIXED_SECTIONS"));

    $zone_type = $is_fixed_section ? 'fixed' : 'flexible';

    $event->setDefinitions(
      array_filter(
        $event->getDefinitions(),
        function ($definition) use ($zone_type) {
          return empty($definition['zone_types']) || in_array($zone_type, $definition['zone_types']);
        }
      )
    );
  }

  /**
   * Unsets dynamic option for Content Hub pages.
   *
   * @param \Drupal\mars_recommendations\Event\PostLoadPopulationLogicPluginsEvent $event
   *   Post load event.
   */
  public function disableDynamicForContentHub(PostLoadPopulationLogicPluginsEvent $event) {
    if (!($entity = $event->getEntity())) {
      return;
    }

    $bundle = $entity instanceof LayoutBuilderEntityViewDisplay ? $entity->getTargetBundle() : $entity->bundle();
    if ($bundle != 'content_hub_page') {
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
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      RecommendationsEvents::POST_LOAD_POPULATION_LOGIC_PLUGINS => [
        ['validateZoneMatch', 10],
        ['disableDynamicForContentHub', -10],
      ],
    ];
  }

}
