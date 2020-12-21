<?php

namespace Drupal\mars_search\Processors;

/**
 * SearchPeocessManagerInterface.
 */
interface SearchProcessManagerInterface {

  /**
   * Return manager identifier name.
   *
   * @return string
   *   Manager name.
   */
  public function getManagerId();

}
