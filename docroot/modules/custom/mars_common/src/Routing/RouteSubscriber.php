<?php

namespace Drupal\mars_common\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\mars_common\Form\MarsSiteInformationForm;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber - route alter logic.
 *
 * @package Drupal\mars_common\Routing
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.menu.add_link_form')) {
      $route->setRequirement('_menu_add_access', 'mars_common.menu_add_access::access');
    }
    if ($route = $collection->get('system.site_information_settings')) {
      $route->setDefault('_form', MarsSiteInformationForm::class);
    }
  }

}
