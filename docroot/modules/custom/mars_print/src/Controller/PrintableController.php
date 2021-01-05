<?php

namespace Drupal\mars_print\Controller;

use Drupal\Core\Config\ConfigFactory;
use Drupal\mars_print\Event\ContentAlterEvent;
use Drupal\mars_print\MarsPrintEvents;
use Drupal\printable\Controller\PrintableController as PrintableControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\printable\PrintableFormatPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller to display an entity in a particular printable format.
 */
class PrintableController extends PrintableControllerBase implements ContainerInjectionInterface {

  /**
   * The Event Dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * Constructs a \Drupal\printable\Controller\PrintableController object.
   *
   * @param \Drupal\printable\PrintableFormatPluginManager $printable_format_manager
   *   The printable format plugin manager.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory class instance.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The Event Dispatcher service.
   */
  public function __construct(
    PrintableFormatPluginManager $printable_format_manager,
    ConfigFactory $config_factory,
    EventDispatcherInterface $dispatcher
  ) {
    parent::__construct($printable_format_manager, $config_factory);
    $this->dispatcher = $dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('printable.format_plugin_manager'),
      $container->get('config.factory'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * Returns the entity rendered via the given printable format.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be printed.
   * @param string $printable_format
   *   The identifier of the hadcopy format plugin.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The printable response.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function showFormat(EntityInterface $entity, $printable_format) {
    if ($this->printableFormatManager->getDefinition($printable_format)) {
      $format = $this->printableFormatManager->createInstance($printable_format);
      $content = $this->entityTypeManager()
        ->getViewBuilder(
          $entity->getEntityTypeId()
        )
        ->view($entity, 'default');

      $event = new ContentAlterEvent($content, $entity, $format);
      $this->dispatcher->dispatch(MarsPrintEvents::CONTENT_ALTER, $event);
      $content = $event->getContent();
      $format->setContent($content);
      return $format->getResponse();
    }
    else {
      throw new NotFoundHttpException();
    }
  }

}
