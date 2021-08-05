<?php

namespace Drupal\mars_print\EventSubscriber;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\mars_print\Event\ContentAlterEvent;
use Drupal\mars_print\MarsPrintEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides a implementation for Print Content alter logic.
 */
class ContentAlterSubscriber implements EventSubscriberInterface {

  /**
   * The entity display repository.
   *
   * @var \Drupal\printable\PrintableEntityManagerInterface
   */
  protected $displayRepository;

  /**
   * Constructs a ContentAlterSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repository
   *   The entity display repository.
   */
  public function __construct(EntityDisplayRepositoryInterface $display_repository) {
    $this->displayRepository = $display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MarsPrintEvents::CONTENT_ALTER] = 'onContentAlter';
    return $events;
  }

  /**
   * Change content array for recipe and article.
   *
   * 'Printable' display mode doesn't support layout section out of the box.
   * That's why we use full display mode.
   *
   * @param \Drupal\mars_print\Event\ContentAlterEvent $event
   *   The content alter event.
   */
  public function onContentAlter(ContentAlterEvent $event) {
    /** @var \Drupal\node\NodeInterface $entity */
    $entity = $event->getEntity();

    if ($entity->bundle() == 'recipe' || $entity->bundle() == 'article') {
      $display = $this->displayRepository
        ->getViewDisplay('node', $entity->bundle(), 'full');
      $content = $display->build($entity);
      unset($content['uid']);
      unset($content['created']);
      $event->setContent($content);
    }
  }

}
