<?php

namespace Drupal\mars_search;

use Drupal\mars_search\Processors\SearchProcessManagerInterface;

/**
 * Class SearchProcessFactoryInterface.
 */
interface SearchProcessFactoryInterface {

  /**
   * Adds manager to internal storage.
   */
  public function addProcessManager(SearchProcessManagerInterface $service);

  /**
   * Get search manager by id.
   *
   * @param string $manager_id
   *   Search manager identifier.
   *
   * @return \Drupal\mars_search\Processors\SearchProcessManagerInterface
   *   Search manager.
   */
  public function getProcessManager(string $manager_id);

  /**
   * Get search managers.
   *
   * @return array
   *   Managers.
   */
  public function getProcessManagers();

}
