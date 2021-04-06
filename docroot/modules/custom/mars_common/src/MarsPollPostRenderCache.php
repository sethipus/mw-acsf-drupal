<?php

namespace Drupal\mars_common;

use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\poll\PollPostRenderCache;

/**
 * Defines a service for poll post render cache callbacks.
 */
class MarsPollPostRenderCache extends PollPostRenderCache {

  /**
   * The class resolver.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  protected $classResolver;

  /**
   * The class resolver.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a new PollPostRenderCache object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   The class resolver service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ClassResolverInterface $class_resolver,
    FormBuilderInterface $form_builder
  ) {
    parent::__construct($entity_type_manager);
    $this->classResolver = $class_resolver;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public function renderViewForm($id, $view_mode, $langcode = NULL) {
    /** @var \Drupal\poll\PollInterface $poll */
    $poll = $this->entityTypeManager->getStorage('poll')->load($id);

    if ($poll) {
      if ($langcode && $poll->hasTranslation($langcode)) {
        $poll = $poll->getTranslation($langcode);
      }
      /** @var \Drupal\poll\Form\PollViewForm $form_object */
      $form_object = $this->classResolver
        ->getInstanceFromDefinition('Drupal\mars_common\Form\MarsPollViewForm');
      $form_object->setPoll($poll);
      return $this->formBuilder
        ->getForm($form_object, \Drupal::request(), $view_mode);
    }
    else {
      return ['#markup' => ''];
    }
  }

}
