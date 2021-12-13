<?php

namespace Drupal\mars_common\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GpcSubscriber implements EventSubscriberInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs Gpc Subscriber.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory
  ) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
    );
  }

  /**
   * Adding data to http response header.
   */
  public function addGpc(FilterResponseEvent $event) {
    $response = $event->getResponse();
    $secgpc_value = $this->configFactory->get('mars_common.system.site');
    if (!$secgpc_value->get('response_header')) {
      $response->headers->set('Sec-GPC', '1');
    } 
  }

  /**
   * Listen to kernel.request events and call customRedirection.
   *
   * @return array
   *   Event names to listen to (key) and methods to call (value).
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['addGpc'];
    return $events;
  }
}
