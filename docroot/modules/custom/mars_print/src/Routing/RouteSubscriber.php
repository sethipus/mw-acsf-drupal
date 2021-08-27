<?php

namespace Drupal\mars_print\Routing;

use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\printable\PrintableEntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RouteSubscriber - route alter logic.
 *
 * @package Drupal\mars_print\Routing
 */
class RouteSubscriber implements EventSubscriberInterface {

  /**
   * The printable entity manager service.
   *
   * @var \Drupal\printable\PrintableEntityManagerInterface
   */
  protected $printableEntityManager;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[RoutingEvents::ALTER][] = ['onAlterRoutes', -100];
    return $events;
  }

  /**
   * Constructs a printable RouteSubscriber object.
   *
   * @param \Drupal\printable\PrintableEntityManagerInterface $printable_entity_manager
   *   The printable entity manager service.
   */
  public function __construct(PrintableEntityManagerInterface $printable_entity_manager = NULL) {
    $this->printableEntityManager = $printable_entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function onAlterRoutes(RouteBuildEvent $event) {
    $collection = $event->getRouteCollection();
    foreach ($this->printableEntityManager->getPrintableEntities() as $entity_type => $entity_definition) {

      if ($route = $collection->get('printable.show_format.' . $entity_type)) {
        $route->setDefault(
          '_controller',
          'Drupal\mars_print\Controller\PrintableController::showFormat'
        );
      }
    }
  }

}
