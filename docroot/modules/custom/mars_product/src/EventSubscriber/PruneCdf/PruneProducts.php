<?php

namespace Drupal\mars_product\EventSubscriber\PruneCdf;

use Drupal\Core\Language\LanguageInterface;
use Acquia\ContentHubClient\Settings;
use Drupal\acquia_contenthub\AcquiaContentHubEvents;
use Drupal\acquia_contenthub\Event\PruneCdfEntitiesEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PruneProducts.
 */
class PruneProducts implements EventSubscriberInterface {

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
    $events[AcquiaContentHubEvents::PRUNE_CDF][] = 'onPruneCdf';
    return $events;
  }

  /**
   * Handles channel languages.
   *
   * @param \Drupal\acquia_contenthub\Event\PruneCdfEntitiesEvent $event
   *   The prune event.
   */
  public function onPruneCdf(PruneCdfEntitiesEvent $event) {
    $cdf = $event->getCdf();
    $pruneContentTypes = [
      'product',
      'product_multipack',
      'product_variant',
    ];
    foreach ($cdf->getEntities() as $cdfEntity) {
      $entity_type = $cdfEntity->getAttribute('entity_type')->getValue()[LanguageInterface::LANGCODE_NOT_SPECIFIED];
      if (in_array($entity_type, $pruneContentTypes)) {
        $cdf->removeCdfEntity($cdfEntity->getUuid());
      }
    }
  }

}
