<?php

namespace Drupal\acquia_contenthub\EventSubscriber\UnserializeContentField;

use Drupal\acquia_contenthub\AcquiaContentHubEvents;
use Drupal\acquia_contenthub\Event\UnserializeCdfEntityFieldEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Entity/image/file field reference handling.
 */
class EntityReferenceImageField implements EventSubscriberInterface {
  use FieldEntityDependencyTrait;

  /**
   * Image field type declaration.
   *
   * @var array
   *   Array of field types,
   */
  protected $fieldTypes = ['image'];

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AcquiaContentHubEvents::UNSERIALIZE_CONTENT_ENTITY_FIELD] = ['onUnserializeContentField', 8];
    return $events;
  }

  /**
   * Extracts the target storage and retrieves the referenced entity.
   *
   * @param \Drupal\acquia_contenthub\Event\UnserializeCdfEntityFieldEvent $event
   *   The unserialize event.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function onUnserializeContentField(UnserializeCdfEntityFieldEvent $event) {
    $field = $event->getField();
    if (!in_array($event->getFieldMetadata()['type'], $this->fieldTypes)) {
      return;
    }
    $values = [];
    if (!empty($field['value'])) {
      foreach ($field['value'] as $langcode => $value) {
        if (empty($value)) {
          continue;
        }
        if (!is_array(reset($value))) {
          $entity = $this->getEntity($value['target_id'], $event);
          $value['target_id'] = $entity->id();
          $values[$langcode][$event->getFieldName()] = $value;
        }
        else {
          foreach ($value as $item) {
            $entity = $this->getEntity($item['target_id'], $event);
            $item['target_id'] = $entity->id();
            $values[$langcode][$event->getFieldName()][] = $item;
          }
        }
      }
    }
    $event->setValue($values);
    $event->stopPropagation();
  }

}
