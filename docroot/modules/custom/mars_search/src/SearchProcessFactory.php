<?php

namespace Drupal\mars_search;

use Drupal\mars_search\Processors\SearchProcessManagerInterface;

/**
 * Class SearchProcessFactory - store info about processing managers.
 */
class SearchProcessFactory implements SearchProcessFactoryInterface {

  /**
   * The managers for search processing.
   *
   * @var array
   */
  protected $managers = [];

  /**
   * Adds manager to internal storage.
   */
  public function addProcessManager(SearchProcessManagerInterface $service) {
    $this->managers[$service->getManagerId()] = $service;
  }

  /**
   * Get search manager by id.
   *
   * @param string $manager_id
   *   Search manager identifier.
   *
   * @return \Drupal\mars_search\Processors\SearchProcessManagerInterface
   *   Search manager.
   */
  public function getProcessManager(string $manager_id) {
    return $this->managers[$manager_id];
  }

  /**
   * Get search managers.
   *
   * @return array
   *   Managers.
   */
  public function getProcessManagers() {
    return $this->managers;
  }

}
