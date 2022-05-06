<?php

namespace Drupal\mars_google_analytics\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EntityManagerWrapper - wrapper for entities.
 */
class EntityManagerWrapper extends EntityTypeManager implements EntityTypeManagerInterface, ContainerAwareInterface {

  /**
   * Loaded entities.
   *
   * @var array
   */
  private $loaded;

  /**
   * Rendered entities.
   *
   * @var array
   */
  private $rendered;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  private $entityManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *   Entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityManager) {
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getStorage($entity_type) {
    return $this->entityManager->getStorage($entity_type);
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getViewBuilder($entity_type) {
    /** @var \Drupal\Core\Entity\EntityViewBuilderInterface $handler */
    $handler = $this->getHandler($entity_type, 'view_builder');

    if ($handler instanceof EntityViewBuilderInterface) {
      if (!isset($this->rendered[$entity_type])) {
        $handler = new EntityViewBuilderDecorator($handler);
        $this->rendered[$entity_type] = $handler;
      }
      else {
        $handler = $this->rendered[$entity_type];
      }
    }

    return $handler;
  }

  /**
   * Get loaded entities by type.
   *
   * @param mixed $type
   *   Content or config.
   * @param mixed $entity_type
   *   Entity type.
   *
   * @return array
   *   Loaded entities.
   */
  public function getLoaded($type, $entity_type) {
    return $this->loaded[$type][$entity_type] ?? NULL;
  }

  /**
   * Get rendered entities by type.
   *
   * @param mixed $entity_type
   *   Entity type.
   *
   * @return array
   *   Rendered entities.
   */
  public function getRendered($entity_type) {
    return $this->rendered[$entity_type] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function useCaches($use_caches = FALSE) {
    $this->entityManager->useCaches($use_caches);
  }

  /**
   * {@inheritdoc}
   */
  public function hasDefinition($plugin_id) {
    return $this->entityManager->hasDefinition($plugin_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessControlHandler($entity_type) {
    return $this->entityManager->getAccessControlHandler($entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public function clearCachedDefinitions() {
    $this->entityManager->clearCachedDefinitions();
    $this->loaded = NULL;
    $this->rendered = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getListBuilder($entity_type) {
    return $this->entityManager->getListBuilder($entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormObject($entity_type, $operation) {
    return $this->entityManager->getFormObject($entity_type, $operation);
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteProviders($entity_type) {
    return $this->entityManager->getRouteProviders($entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public function hasHandler($entity_type, $handler_type) {
    return $this->entityManager->hasHandler($entity_type, $handler_type);
  }

  /**
   * {@inheritdoc}
   */
  public function getHandler($entity_type, $handler_type) {
    return $this->entityManager->getHandler($entity_type, $handler_type);
  }

  /**
   * {@inheritdoc}
   */
  public function createHandlerInstance(
    $class,
    EntityTypeInterface $definition = NULL
  ) {
    return $this->entityManager->createHandlerInstance($class, $definition);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition($entity_type_id, $exception_on_invalid = TRUE) {
    return $this->entityManager->getDefinition(
      $entity_type_id,
      $exception_on_invalid
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    return $this->entityManager->getDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    return $this->entityManager->createInstance($plugin_id, $configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function getInstance(array $options) {
    return $this->entityManager->getInstance($options);
  }

  /**
   * {@inheritdoc}
   */
  public function setContainer(ContainerInterface $container = NULL) {
    $this->entityManager->setContainer($container);
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveDefinition($entity_type_id) {
    return $this->entityManager->getActiveDefinition($entity_type_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveFieldStorageDefinitions($entity_type_id) {
    return $this->entityManager->getActiveFieldStorageDefinitions($entity_type_id);
  }

}
