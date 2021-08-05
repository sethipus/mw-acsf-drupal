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
class ProductFieldUnserializer implements EventSubscriberInterface {

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
    $events[AcquiaContentHubEvents::UNSERIALIZE_CONTENT_ENTITY_FIELD] = [
      'onUnserializeContentField',
      200,
    ];
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
      $this->handleComponents($section->getComponents(), $event);
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
    foreach ($components as &$component) {
      $componentConfiguration = $this->getComponentConfiguration($component);
      if ($component->getPluginId() == 'product_content_pair_up_block') {
        if (!empty($componentConfiguration['product'])) {
          $componentConfiguration['product'] = $this->getProductByGtin($componentConfiguration['product']);
        }
      }
      if ($component->getPluginId() == 'recommendations_module' && $componentConfiguration['population_plugin_id'] == 'manual') {
        foreach ($componentConfiguration['population_plugin_configuration']['nodes'] as $key => $gtin) {
          $componentConfiguration['population_plugin_configuration']['nodes'][$key] = $componentConfiguration['product'] = $this->getProductByGtin($gtin);
        }
      }
      $component->setConfiguration($componentConfiguration);
    }
  }

  /**
   * Retrieve product by GTIN from Local DB.
   *
   * @param string $gtin
   *   Product GTIN.
   */
  private function getProductByGtin(string $gtin) {
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    $productVariants = $nodeStorage->loadByProperties(['field_product_sku' => $gtin]);
    foreach ($productVariants as $variant) {
      $products = $nodeStorage->loadByProperties(['field_product_variants' => $variant->id()]);
      foreach ($products as $product) {
        return $product->id();
      }
    }
    return '';
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
