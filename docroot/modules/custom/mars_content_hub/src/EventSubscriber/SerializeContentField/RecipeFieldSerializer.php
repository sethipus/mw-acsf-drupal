<?php

namespace Drupal\mars_content_hub\EventSubscriber\SerializeContentField;

use Drupal\acquia_contenthub\AcquiaContentHubEvents;
use Drupal\acquia_contenthub\Event\SerializeCdfEntityFieldEvent;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\acquia_contenthub\EventSubscriber\SerializeContentField\ContentFieldMetadataTrait;
use Drupal\layout_builder\SectionComponent;

/**
 * Subscribes to entity field serialization to handle layout builder fields.
 */
class RecipeFieldSerializer implements EventSubscriberInterface {

  use ContentFieldMetadataTrait;

  const FIELD_TYPE = 'layout_section';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * LayoutBuilderFieldSerializer constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AcquiaContentHubEvents::SERIALIZE_CONTENT_ENTITY_FIELD][] = ['onSerializeContentField', 200];
    return $events;
  }

  /**
   * Prepare layout builder field.
   *
   * @param \Drupal\acquia_contenthub\Event\SerializeCdfEntityFieldEvent $event
   *   The content entity field serialization event.
   */
  public function onSerializeContentField(SerializeCdfEntityFieldEvent $event) {
    $event_field_type = $event->getField()->getFieldDefinition()->getType();
    if ($event_field_type !== self::FIELD_TYPE) {
      return;
    }

    $this->setFieldMetaData($event);
    $data = [];
    /** @var \Drupal\Core\Entity\TranslatableInterface $entity */
    $entity = $event->getEntity();
    foreach ($entity->getTranslationLanguages() as $langcode => $language) {
      $field = $event->getFieldTranslation($langcode);

      if ($field->isEmpty()) {
        $data['value'][$langcode] = [];
        continue;
      }

      $data['value'][$langcode] = $this->handleSections($field);
    }
    $event->setFieldData($data);
  }

  /**
   * Prepares Layout Builder sections to be serialized.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   *   The field with the sections.
   *
   * @return array
   *   The prepared Layout Builder sections.
   */
  protected function handleSections(FieldItemListInterface $field) {
    $sections = [];
    foreach ($field as $item) {
      $section = $item->getValue()['section'];
      $component = $section->getComponents();
      $this->handleComponents($component);
      $sections[] = ['section' => $section->toArray()];
    }
    return $sections;
  }

  /**
   * Prepares component to be serialized.
   *
   * @param \Drupal\layout_builder\SectionComponent[] $components
   *   The component to add.
   */
  protected function handleComponents(array &$components) {
    foreach ($components as &$component) {
      $componentConfiguration = $this->getComponentConfiguration($component);
      if ($component->getPluginId() == 'recipe_feature_block') {
        if (!empty($componentConfiguration['recipe_id'])) {
          $entity = $this->entityTypeManager->getStorage('node')->load($componentConfiguration['recipe_id']);
          if (!empty($entity)) {
            $componentConfiguration['recipe_id'] = $entity->uuid();
          }
        }
      }
      elseif ($component->getPluginId() == 'product_content_pair_up_block') {
        if (!empty($componentConfiguration['article_recipe'])) {
          $entity = $this->entityTypeManager->getStorage('node')->load($componentConfiguration['article_recipe']);
          if (!empty($entity)) {
            $componentConfiguration['article_recipe'] = $entity->uuid();
          }
        }
      }
      $component->setConfiguration($componentConfiguration);
    }
  }

  /**
   * Gets configuration for a Layout Builder component.
   *
   * @param \Drupal\layout_builder\SectionComponent $component
   *   The Layout Builder component.
   *
   * @return array
   *   The component configuration.
   *
   * @throws \ReflectionException
   *
   * @todo Check pending patch to make SectionComponent::getConfiguration() public: https://www.drupal.org/project/drupal/issues/3046814
   */
  protected function getComponentConfiguration(SectionComponent $component) {
    $method = new \ReflectionMethod('\Drupal\layout_builder\SectionComponent', 'getConfiguration');
    $method->setAccessible(TRUE);

    return $method->invoke($component);
  }

}
