<?php

namespace Drupal\mars_content_hub\EventSubscriber\EnqueueEntity;

use Drupal\acquia_contenthub_publisher\ContentHubPublisherEvents;
use Drupal\acquia_contenthub_publisher\Event\ContentHubEntityEligibilityEvent;
use Acquia\ContentHubClient\Settings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EnqueueProducts.
 */
class EnqueueProducts implements EventSubscriberInterface {

  /**
   * The client settings.
   *
   * @var \Acquia\ContentHubClient\Settings
   */
  protected $clientSettings;

  /**
   * HandleChannelLanguages constructor.
   *
   * @param \Acquia\ContentHubClient\Settings $client_settings
   *   The client settings.
   */
  public function __construct(Settings $client_settings) {
    $this->clientSettings = $client_settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ContentHubPublisherEvents::ENQUEUE_CANDIDATE_ENTITY][] = 'onEnqueueCandidateEntity';
    return $events;
  }

  /**
   * Skips entities of product content-type.
   *
   * @param \Drupal\acquia_contenthub_publisher\Event\ContentHubEntityEligibilityEvent $event
   *   The event to determine entity eligibility.
   *
   * @throws \Exception
   */
  public function onEnqueueCandidateEntity(ContentHubEntityEligibilityEvent $event) {
    $entity = $event->getEntity();

    $productContentTypes = [
      'product',
      'product_multipack',
      'product_variant',
    ];

    if (in_array($entity->bundle(), $productContentTypes)) {
      $event->setEligibility(FALSE);
      $event->stopPropagation();
    }
  }

}
