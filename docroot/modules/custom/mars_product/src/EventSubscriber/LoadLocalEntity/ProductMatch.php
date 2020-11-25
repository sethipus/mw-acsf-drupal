<?php

namespace Drupal\mars_product\EventSubscriber\LoadLocalEntity;

use Acquia\ContentHubClient\CDF\CDFObject;
use Drupal\acquia_contenthub\AcquiaContentHubEvents;
use Drupal\acquia_contenthub\Event\LoadLocalEntityEvent;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Entity\EntityInterface;
use Drupal\depcalc\DependencyStack;
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





    $vocabulary_name = $this->getVocabularyName($object);
    $storage = $this->getVocabularyStorage();
    // If the vocabulary doesn't exist, the term couldn't possibly exist.
    if (!$vocabulary = $storage->load($vocabulary_name)) {
      return;
    }

    $parents = $this->extractParentAttribute($object);
    $label = $this->getTermLabel($object);





    $salsify_id = $this->getSalsifyId($object);

    $product = $this->findProduct($salsify_id, $event->getStack());
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
      'media',
    ];

    return in_array($type->getValue()[CDFObject::LANGUAGE_UNDETERMINED], $local_entities);
  }

  /**
   * Get term label from CDF object.
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject $cdf_object
   *   CDF object.
   *
   * @return string|null
   *   Term label.
   */
  protected function getTermLabel(CDFObject $cdf_object): ?string {
    $term_language = $cdf_object->getMetadata()['default_language'];
    $label = $cdf_object->getAttribute('label')->getValue();

    return $label[$term_language] ?? $label[CDFObject::LANGUAGE_UNDETERMINED] ?? NULL;
  }

  /**
   * Extracts vocabulary name from CDF object.
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject $cdf_object
   *   CDF object.
   *
   * @return string|null
   *   Vocabulary machine name.
   */
  protected function getVocabularyName(CDFObject $cdf_object): ?string {
    $bundle = $cdf_object
      ->getAttribute('bundle')
      ->getValue();

    return $bundle[CDFObject::LANGUAGE_UNDETERMINED] ?? NULL;
  }

  /**
   * Extracts 'parent' attribute from CDF object.
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject $object
   *   CDF Object.
   *
   * @return array|null
   *   Attribute value or NULL.
   */
  protected function extractParentAttribute(CDFObject $object) {
    $parent_attribute = $object->getAttribute('parent');
    if (empty($parent_attribute)) {
      return ['0'];
    }

    $attribute_value = $parent_attribute->getValue();
    if (empty($attribute_value[CDFObject::LANGUAGE_UNDETERMINED])) {
      return ['0'];
    }

    return $attribute_value[CDFObject::LANGUAGE_UNDETERMINED];
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
   * @return \Drupal\taxonomy\Entity\Term|null
   *   Taxonomy term if exists, NULL otherwise.
   *
   * @throws \Exception
   */
  protected function findTaxonomyTerm(?string $label, ?string $vocabulary, string $parent, DependencyStack $stack) {
    if (!$label || !$vocabulary) {
      return NULL;
    }

    if (Uuid::isValid($parent)) {
      $parent_term = $stack->getDependency($parent);
      // The stack should ALWAYS have a term representing the parent. This
      // could be a local or remote term, but the remote uuid should always
      // retrieve it.
      if (!$parent_term) {
        throw new \Exception(sprintf("Taxonomy term %s could not be found in the dependency stack during DataTamper.", $parent));
      }
      $parent = $parent_term->getId();
    }

    $terms = $this->getTermStorage()->loadByProperties([
      'name' => $label,
      'vid' => $vocabulary,
      'parent' => $parent,
    ]);

    // No local terms were found that match our criteria. This is the normal
    // state of a new import.
    if ($parent && empty($terms)) {
      return NULL;
    }

    return array_shift($terms);
  }



  /**
   * Extracts vocabulary name from CDF object.
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject $cdf_object
   *   CDF object.
   *
   * @return string|null
   *   Vocabulary machine name.
   */
  protected function getSalsifyId(CDFObject $cdf_object): ?string {
    $bundle = $cdf_object
      ->getAttribute('bundle')
      ->getValue();

    return $bundle[CDFObject::LANGUAGE_UNDETERMINED] ?? NULL;
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
  protected function findProduct(?string $salsify_id, DependencyStack $stack) {





    if (!$label || !$vocabulary) {
      return NULL;
    }

    if (Uuid::isValid($parent)) {
      $parent_term = $stack->getDependency($parent);
      // The stack should ALWAYS have a term representing the parent. This
      // could be a local or remote term, but the remote uuid should always
      // retrieve it.
      if (!$parent_term) {
        throw new \Exception(sprintf("Taxonomy term %s could not be found in the dependency stack during DataTamper.", $parent));
      }
      $parent = $parent_term->getId();
    }

    $terms = $this->getTermStorage()->loadByProperties([
      'salsify_id' => $salsify_id,
    ]);

    // No local terms were found that match our criteria. This is the normal
    // state of a new import.
    if ($parent && empty($terms)) {
      return NULL;
    }

    return array_shift($terms);

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
   * Gets the Vocabulary Storage.
   */
  protected function getVocabularyStorage() {
    return $this->entityTypeManager->getStorage('taxonomy_vocabulary');
  }

  /**
   * Gets the Taxonomy Term Storage.
   */
  protected function getTermStorage() {
    return $this->entityTypeManager->getStorage('taxonomy_term');
  }

  /**
   * Gets the Node Storage.
   */
  protected function getNodeStorage() {
    return $this->entityTypeManager->getStorage('node');
  }

}

// @codingStandardsIgnoreEnd
