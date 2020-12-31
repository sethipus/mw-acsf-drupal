<?php

namespace Drupal\mars_print\Event;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event fired for altering content as a part of print functionality.
 *
 * @see \Drupal\mars_print\MarsPrintEvents
 */
class ContentAlterEvent extends Event {

  /**
   * The key eneity uuid from which this set of entities was calculated.
   *
   * @var array
   */
  protected $content;

  /**
   * Entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * Printable format.
   *
   * @var string
   */
  protected $format;

  /**
   * ContentAlterEvent constructor.
   *
   * @param array $content
   *   The content array.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The Entity.
   * @param string $format
   *   The printable format.
   */
  public function __construct(array $content, EntityInterface $entity, $format) {
    $this->content = $content;
    $this->entity = $entity;
    $this->format = $format;
  }

  /**
   * Get the content.
   *
   * @return array|null
   *   The content.
   */
  public function getContent() {
    return $this->content;
  }

  /**
   * Get the entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Get the printable format.
   *
   * @return string
   *   The printable format.
   */
  public function getFormat() {
    return $this->format;
  }

  /**
   * Set the content.
   *
   * @param array|null $content
   *   The content array.
   */
  public function setContent(?array $content) {
    $this->content = $content;
  }

}
