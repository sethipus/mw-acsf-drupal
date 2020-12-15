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
class MediaFieldSerializer implements EventSubscriberInterface {

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
      $components = $section->getComponents();
      $this->handleComponents($components);
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
      $this->iterateConfig($componentConfiguration);
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

  /**
   * Collect all media ids from block configuration.
   *
   * @param array $config
   *   Block configuration array.
   */
  private function iterateConfig(array &$config) {
    foreach ($config as &$element) {
      if (is_string($element) && strpos($element, 'media:') !== FALSE) {
        $media_id = explode(':', $element)[1];
        $entity = $this->entityTypeManager->getStorage('media')->load($media_id);
        if (!empty($entity)) {
          $element = 'media:' . $entity->uuid();
        }
      }
      if (is_array($element)) {
        $this->iterateConfig($element);
      }
    }
  }

}
