<?php

namespace Drupal\mars_google_analytics\Entity;

use Drupal\mars_google_analytics\Decorator;

/**
 * Class EntityDecorator.
 */
class EntityDecorator extends Decorator {

  /**
   * Entities.
   *
   * @var array
   */
  protected $entities;

  /**
   * Get entities.
   *
   * @return mixed
   *   Entities.
   */
  public function getEntities() {
    return $this->entities;
  }

}
