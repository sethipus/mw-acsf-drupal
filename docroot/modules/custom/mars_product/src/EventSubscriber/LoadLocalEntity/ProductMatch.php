<?php

namespace Drupal\mars_product\EventSubscriber\LoadLocalEntity;

use Acquia\ContentHubClient\CDF\CDFObject;
use Drupal\acquia_contenthub\AcquiaContentHubEvents;
use Drupal\acquia_contenthub\Event\LoadLocalEntityEvent;
use Drupal\Core\Entity\EntityInterface;
use Drupal\depcalc\DependentEntityWrapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

// @codingStandardsIgnoreStart
/**
 * Class ProductMatch.
 *
 * Matches remote product entities with an local instances.
 *
 * @package Drupal\mars_produc\EventSubscriber\LoadLocalEntity
 */
class ProductMatch implements EventSubscriberInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ProductMatch constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      AcquiaContentHubEvents::LOAD_LOCAL_ENTITY => [
        ['onLoadLocalEntity', 100],
      ],
    ];
  }

  /**
   * Load local terms with the same name, vocabulary and relative parent.
   *
   * @param \Drupal\acquia_contenthub\Event\LoadLocalEntityEvent $event
   *   Data tamper event.
   *
   * @throws \Exception
   */
  public function onLoadLocalEntity(LoadLocalEntityEvent $event) {
    $object = $event->getCdf();
    if (!$this->isSupported($object)) {
      return;
    }
    if ($event->getStack()->hasDependency($object->getUuid())) {
      return;
    }

    $title = $this->getNodeTitle($object);
    $product = $this->findProduct($title);
    if (!empty($product)) {
      $this->addDependency($event, $object, $product);
      $event->setEntity($product);
    }

  }

  /**
   * Checks should object be processed or not.
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject $cdf_object
   *   CDF Object.
   *
   * @return bool
   *   TRUE if CDF object is taxonomy term.
   */
  protected function isSupported(CDFObject $cdf_object): bool {
    $type = $cdf_object->getAttribute('entity_type');
    $local_entities = [
      'product',
      'product_variant',
      'product_multipack',
    ];

    return in_array($type->getValue()[CDFObject::LANGUAGE_UNDETERMINED], $local_entities);
  }

  /**
   * Get node title from CDF object.
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject $cdf_object
   *   CDF object.
   *
   * @return string|null
   *   Term label.
   */
  protected function getNodeTitle(CDFObject $cdf_object): ?string {
    $product_language = $cdf_object->getMetadata()['default_language'];
    $title = $cdf_object->getAttribute('title')->getValue();

    return $title[$product_language] ?? $title[CDFObject::LANGUAGE_UNDETERMINED] ?? NULL;
  }

  /**
   * Find local taxonomy term.
   *
   * @param string|null $label
   *   Term label.
   * @param string|null $vocabulary
   *   Vocabulary machine name.
   * @param string $parent
   *   Term's parent UUID.
   * @param \Drupal\depcalc\DependencyStack $stack
   *   The Dependency Stack.
   *
   * @return \Drupal\core\Entity\EntityInterface|null
   *   Node if exists, NULL otherwise.
   *
   * @throws \Exception
   */
  protected function findProduct(?string $title) {
    if (!$title) {
      return NULL;
    }

    $nodes = $this->getNodeStorage()->loadByProperties([
      'title' => $title,
    ]);

    return array_shift($nodes);
  }

  /**
   * Adds entity as dependency.
   *
   * @param \Drupal\acquia_contenthub\Event\LoadLocalEntityEvent $event
   *   Data tamper event.
   * @param \Acquia\ContentHubClient\CDF\CDFObject $object
   *   The CDF Object representing the remote entity.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The local entity.
   *
   * @throws \Exception
   */
  protected function addDependency(LoadLocalEntityEvent $event, CDFObject $object, EntityInterface $entity): void {
    $wrapper = new DependentEntityWrapper($entity);
    $wrapper->setRemoteUuid($object->getUuid());
    $event->getStack()->addDependency($wrapper);
  }

  /**
   * Gets the Node Storage.
   */
  protected function getNodeStorage() {
    return $this->entityTypeManager->getStorage('node');
  }

}
