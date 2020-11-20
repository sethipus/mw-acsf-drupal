<?php

namespace Drupal\mars_search;

/**
 * Class SearchProcessFactory.
 */
class SearchProcessFactory {

  /**
   * The managers for search processing.
   *
   * @var array
   */
  protected $managers = [];

  /**
   * Adds manager to internal storage.
   */
  public function addProcessManager(SearchPeocessManagerInterface $service) {
    $this->managers[] = $service;
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
