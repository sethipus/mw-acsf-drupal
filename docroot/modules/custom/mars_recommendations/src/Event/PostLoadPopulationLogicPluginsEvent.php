<?php

namespace Drupal\mars_recommendations\Event;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Post Load Recommendations Population Logic Plugins event.
 */
class PostLoadPopulationLogicPluginsEvent extends Event {

  /**
   * Plugin definitions.
   *
   * @var array
   */
  protected $definitions = [];

  /**
   * Original Plugin definitions.
   *
   * @var array
   */
  protected $originalDefinitions = [];

  /**
   * Section Layout ID.
   *
   * @var string
   */
  protected $layoutId;

  /**
   * Node or Layout Builder View Display entity.
   *
   * @var \Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay|\Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * PostLoadPopulationLogicPluginsEvent constructor.
   *
   * @param array $definitions
   *   Plugin definitions.
   * @param string $layout_id
   *   Section Layout ID.
   * @param \Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay|\Drupal\Core\Entity\EntityInterface $entity
   *   Node or Layout Builder View Display entity.
   */
  public function __construct(array $definitions, string $layout_id, EntityInterface $entity) {
    $this->definitions = $this->originalDefinitions = $definitions;
    $this->layoutId = $layout_id;
    $this->entity = $entity;
  }

  /**
   * Plugin definitions getter.
   *
   * @return array
   *   Plugin definitions.
   */
  public function getDefinitions(): array {
    return $this->definitions;
  }

  /**
   * Original Plugin definitions getter.
   *
   * @return array
   *   Plugin definitions.
   */
  public function getOriginalDefinitions(): array {
    return $this->originalDefinitions;
  }

  /**
   * Plugin definitions setter.
   *
   * @param array $definitions
   *   Plugin definitions.
   */
  public function setDefinitions(array $definitions): void {
    $this->definitions = $definitions;
  }

  /**
   * Section Layout ID getter.
   *
   * @return string
   *   Layout ID.
   */
  public function getLayoutId(): string {
    return $this->layoutId;
  }

  /**
   * Entity getter.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay
   *   Node or Layout Builder View Display entity.
   */
  public function getEntity() {
    return $this->entity;
  }

}
