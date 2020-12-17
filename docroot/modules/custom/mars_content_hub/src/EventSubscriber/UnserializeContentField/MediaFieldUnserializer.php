<?php

namespace Drupal\mars_content_hub\EventSubscriber\UnserializeContentField;

use Drupal\acquia_contenthub\AcquiaContentHubEvents;
use Drupal\acquia_contenthub\Event\UnserializeCdfEntityFieldEvent;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\acquia_contenthub\EventSubscriber\UnserializeContentField\FieldEntityDependencyTrait;

/**
 * Layout builder field unserializer fallback subscriber.
 */
class MediaFieldUnserializer implements EventSubscriberInterface {

  use FieldEntityDependencyTrait;

  /**
   * Layout section field type definition.
   *
   * @var string
   */
  protected $fieldType = 'layout_section';

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
    $events[AcquiaContentHubEvents::UNSERIALIZE_CONTENT_ENTITY_FIELD] = ['onUnserializeContentField', 200];
    return $events;
  }

  /**
   * Handling for Layout Builder sections.
   *
   * @param \Drupal\acquia_contenthub\Event\UnserializeCdfEntityFieldEvent $event
   *   The unserialize event.
   */
  public function onUnserializeContentField(UnserializeCdfEntityFieldEvent $event) {
    $event_field_type = $event->getFieldMetadata()['type'];
    if ($event_field_type !== $this->fieldType) {
      return;
    }

    $field = $event->getField();
    $values = [];
    if (!empty($field['value'])) {
      foreach ($field['value'] as $langcode => $sections) {
        $values[$langcode][$event->getFieldName()] = $this->handleSections($sections, $event);
      }
      $event->setValue($values);
    }
  }

  /**
   * Prepares Layout Builder sections to be unserialized.
   *
   * @param array $sections
   *   The Layout Builder sections to unserialize.
   * @param \Drupal\acquia_contenthub\Event\UnserializeCdfEntityFieldEvent $event
   *   The subscribed event.
   *
   * @return array
   *   The prepared sections.
   */
  protected function handleSections(array $sections, UnserializeCdfEntityFieldEvent $event) {
    $values = [];
    foreach ($sections as $sectionArray) {
      $section = Section::fromArray($sectionArray['section']);
      $components = $section->getComponents();
      $this->handleComponents($components, $event);
      $values[] = ['section' => $section];
    }
    return $values;
  }

  /**
   * Prepares Layout Builder components to be unserialized.
   *
   * @param \Drupal\layout_builder\SectionComponent[] $components
   *   The components to unserialize.
   * @param \Drupal\acquia_contenthub\Event\UnserializeCdfEntityFieldEvent $event
   *   The subscribed event.
   */
  protected function handleComponents(array &$components, UnserializeCdfEntityFieldEvent $event) {
    // @TODO Add dependency restore for recommendation module manual logic and content pair up and recipe feature.
    foreach ($components as &$component) {
      $componentConfiguration = $this->getComponentConfiguration($component);
      $this->iterateConfig($componentConfiguration, $event);
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
   * @param \Drupal\acquia_contenthub\Event\UnserializeCdfEntityFieldEvent $event
   *   The subscribed event.
   */
  private function iterateConfig(array &$config, UnserializeCdfEntityFieldEvent $event) {
    foreach ($config as &$element) {
      if (is_string($element) && strpos($element, 'media:') !== FALSE) {
        $media_uuid = explode(':', $element)[1];
        $entity = $this->getEntity($media_uuid, $event);
        $element = 'media:' . $entity->id();
      }
      if (is_array($element)) {
        $this->iterateConfig($element, $event);
      }
    }
  }

}
