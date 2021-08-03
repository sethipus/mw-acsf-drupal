<?php

namespace Drupal\mars_google_analytics\DataCollector;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;

/**
 * Class PageDataCollector - collects data of the page.
 */
class PageDataCollector implements DataCollectorInterface, DataLayerCollectorInterface {

  /**
   * Current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

  /**
   * Collection of data.
   *
   * @var array
   */
  private $data = [];

  /**
   * Constructor method.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Current route match service.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;

    $this->data['page']['type'] = NULL;
  }

  /**
   * Get data layer id.
   *
   * @return string
   *   Data layer param id.
   */
  public function getDataLayerId() {
    return 'page_type';
  }

  /**
   * Collect page related info.
   */
  public function collect() {
    $node = $this->routeMatch->getParameter('node');

    if (isset($node) && $node instanceof NodeInterface) {
      $this->data['page']['type'] = $node->getType();
    }
  }

  /**
   * Get page type.
   *
   * @return null|string
   *   Rendered products.
   */
  public function getPageType() {
    return $this->data['page']['type'];
  }

  /**
   * Generate Google Analytics data string.
   *
   * @return string|null
   *   Google Analytics data.
   */
  public function getGaData() {
    return $this->getPageType();
  }

}
