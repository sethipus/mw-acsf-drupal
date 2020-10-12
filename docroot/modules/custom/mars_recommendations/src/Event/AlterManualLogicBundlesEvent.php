<?php

namespace Drupal\mars_recommendations\Event;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Alters supported bundles by Manual Recommendations Logic plugin.
 */
class AlterManualLogicBundlesEvent extends Event {

  /**
   * Entity bundles list for autocomplete field.
   *
   * @var string[]
   */
  protected $bundles = [];

  /**
   * Original Entity bundles.
   *
   * @var array
   */
  protected $originalBundles = [];

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
    $this->bundles = $this->originalBundles = $definitions;
    $this->layoutId = $layout_id;
    $this->entity = $entity;
  }

  /**
   * Entity bundles list getter.
   *
   * @return array
   *   Entity bundles list.
   */
  public function getBundles(): array {
    return $this->bundles;
  }

  /**
   * Original bundles list getter.
   *
   * @return array
   *   Entity bundles list.
   */
  public function getOriginalBundles(): array {
    return $this->originalBundles;
  }

  /**
   * Entity bundles setter.
   *
   * @param array $bundles
   *   Entity bundles list.
   */
  public function setBundles(array $bundles): void {
    $this->bundles = $bundles;
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
