<?php

namespace Drupal\mars_google_analytics;

use Drupal\mars_google_analytics\DataCollector\DataCollectorInterface;

/**
 * Class DataCollectorServiceManager.
 */
class DataCollectorServiceManager {

  /**
   * The services for manager.
   *
   * @var array
   */
  protected $services = [];

  /**
   * Adds service to internal storage.
   *
   * @param \Drupal\mars_google_analytics\DataCollector\DataCollectorInterface $service
   *   The service instance.
   */
  public function addService(DataCollectorInterface $service) {
    $this->services[] = $service;
  }

  /**
   * Get tagged services.
   *
   * @return array
   *   Services.
   */
  public function getServices() {
    return $this->services;
  }

}
