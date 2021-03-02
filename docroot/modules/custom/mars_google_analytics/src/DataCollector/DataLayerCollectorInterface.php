<?php

namespace Drupal\mars_google_analytics\DataCollector;

/**
 * Interface DataLayerCollectorInterface.
 */
interface DataLayerCollectorInterface {

  /**
   * Get data layer id.
   *
   * @return string
   *   Data layer param id.
   */
  public function getDataLayerId();

  /**
   * Generate Google Analytics data string.
   *
   * @return string|null
   *   Google Analytics data.
   */
  public function getGaData();

}
