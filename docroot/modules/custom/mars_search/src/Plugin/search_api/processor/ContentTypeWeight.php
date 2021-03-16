<?php

namespace Drupal\mars_search\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\search_api\Plugin\PluginFormTrait;

/**
 * Adds content type weight.
 *
 * @SearchApiProcessor(
 *   id = "content_type_weight",
 *   label = @Translation("Content type weight setup"),
 *   description = @Translation("Setup content type weight for proper items sort."),
 *   stages = {
 *     "add_properties" = 0,
 *   }
 * )
 */
class ContentTypeWeight extends ProcessorPluginBase implements PluginFormInterface {

  use PluginFormTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'content_type_weight' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $formState) {
    foreach ($this->index->getDatasources() as $datasource_id => $datasource) {
      $datasource_configuration = [];
      if (isset($this->configuration['bundle_weights'][$datasource_id])) {
        $datasource_configuration = $this->configuration['bundle_weights'][$datasource_id];
      }
      $datasource_configuration += [
        'bundle_weights' => [],
      ];
      $bundle_weights = $datasource_configuration['bundle_weights'];

      // Add a weight for every available bundle. Drop the "pseudo-bundle" that
      // is added when the datasource does not contain any bundles.
      $bundles = $datasource->getBundles();
      if (count($bundles) === 1) {
        // Depending on the datasource, the pseudo-bundle might use the
        // datasource ID or the entity type ID.
        unset($bundles[$datasource_id], $bundles[$datasource->getEntityTypeId()]);
      }

      foreach ($bundles as $bundle => $bundle_label) {
        $has_value = isset($bundle_weights[$bundle]);
        $bundle_weight = $has_value ? $bundle_weights[$bundle] : '';
        $form['bundle_weights'][$datasource_id]['bundle_weights'][$bundle] = [
          '#type' => 'select',
          '#title' => $this->t('Weight for the %bundle bundle', ['%bundle' => $bundle_label]),
          '#options' => range(1, count($bundles), 1),
          '#default_value' => $bundle_weight,
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    foreach ($this->index->getDatasourceIds() as $datasource_id) {
      foreach ($values['bundle_weights'][$datasource_id]['bundle_weights'] ?? [] as $bundle => $weight) {
        if ($weight === '') {
          unset($values['bundle_weights'][$datasource_id]['bundle_weights'][$bundle]);
        }
      }
      if (empty($values['bundle_weights'][$datasource_id]['bundle_weights'])) {
        unset($values['bundle_weights'][$datasource_id]['bundle_weights']);
      }
    }
    $form_state->setValues($values);
    $this->setConfiguration($values);
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];
    if (!$datasource) {
      $definition = [
        'label' => $this->t('Content type weight'),
        'description' => $this->t('Content type weight'),
        'type' => 'integer',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['bundle_weight'] = new ProcessorProperty($definition);
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $weights = $this->configuration['bundle_weights'];

    $datasource_id = $item->getDatasourceId();
    $bundle = $item->getDatasource()->getItemBundle($item->getOriginalObject());

    $item_weight = 1;
    if ($bundle && isset($weights[$datasource_id]['bundle_weights'][$bundle])) {
      $item_weight = (int) $weights[$datasource_id]['bundle_weights'][$bundle];
    }

    $fields = $item->getFields(FALSE);
    $fields = $this->getFieldsHelper()
      ->filterForPropertyPath($fields, NULL, 'bundle_weight');
    foreach ($fields as $field) {
      $field->addValue($item_weight);
    }
  }

}
