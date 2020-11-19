<?php

namespace Drupal\mars_lighthouse\Traits;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\entity_browser\Element\EntityBrowserElement;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;

/**
 * Provides helpers for adding an entity browser element to a form.
 */
trait EntityBrowserFormTrait {

  use StringTranslationTrait;

  /**
   * File type key.
   *
   * @var string
   */
  protected static $file = 'file';

  /**
   * Adds the Entity Browser element to a form.
   *
   * @param string $entity_browser_id
   *   The ID of the entity browser to use.
   * @param string $default_value
   *   The default value for the entity browser.
   * @param int $cardinality
   *   The cardinality of the entity browser.
   * @param string $view_mode
   *   The view mode to use when displaying the selected entity in the table.
   *
   * @return array
   *   The form element containing the entity browser.
   */
  public function getEntityBrowserForm($entity_browser_id, $default_value, $cardinality = EntityBrowserElement::CARDINALITY_UNLIMITED, $view_mode = 'default') {
    // We need a wrapping container for AJAX operations.
    $element = [
      '#type' => 'container',
      '#attributes' => [
        'id' => Html::getUniqueId('entity-browser-' . $entity_browser_id . '-wrapper'),
      ],
    ];

    $element['browser'] = [
      '#type' => 'entity_browser',
      '#entity_browser' => $entity_browser_id,
      '#process' => [
        [self::class, 'processEntityBrowser'],
      ],
      '#cardinality' => $cardinality,
      '#selection_mode' => $cardinality === 1 ? EntityBrowserElement::SELECTION_MODE_PREPEND : EntityBrowserElement::SELECTION_MODE_APPEND,
      '#default_value' => $default_value,
      '#wrapper_id' => &$element['#attributes']['id'],
      '#widget_context' => [
        'cardinality' => $cardinality,
      ],
    ];
    $element['selected'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Item'),
        $this->t('Operations'),
      ],
      '#empty' => $this->t('No items selected yet'),
      '#process' => [
        [self::class, 'processEntityBrowserSelected'],
      ],
      '#view_mode' => $view_mode,
      '#wrapper_id' => &$element['#attributes']['id'],
    ];

    return $element;
  }

  /**
   * Loads entity based on an ID in the format entity_type:entity_id.
   *
   * @param string $id
   *   An ID.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   A loaded entity.
   */
  public static function loadEntityBrowserEntity($id) {
    $entities = static::loadEntityBrowserEntitiesByIds($id);
    return reset($entities);
  }

  /**
   * Loads entities based on an ID in the format entity_type:entity_id.
   *
   * @param array|string $ids
   *   An array of IDs.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of loaded entities, keyed by an ID.
   */
  public static function loadEntityBrowserEntitiesByIds($ids) {
    if (!is_array($ids)) {
      $ids = explode(' ', $ids);
    }
    $ids = array_filter($ids);

    $storage = [];
    $entities = [];
    foreach ($ids as $id) {
      list($entity_type_id, $entity_id) = explode(':', $id);
      if (!isset($storage[$entity_type_id])) {
        $storage[$entity_type_id] = \Drupal::entityTypeManager()->getStorage($entity_type_id);
      }
      $entities[$entity_type_id . ':' . $entity_id] = $storage[$entity_type_id]->load($entity_id);
    }
    return $entities;
  }

  /**
   * Gets the entity browser form value.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array|string $parents
   *   The parents of the containing form element.
   *
   * @return array
   *   The entity browser value.
   */
  public function getEntityBrowserValue(FormStateInterface $form_state, $parents) {
    $parents = is_array($parents) ? $parents : [$parents];
    if ($this instanceof FormInterface) {
      $value = $form_state->getValue(array_merge($parents, ['entity_ids']));
    }
    else {
      $value = $form_state->getValue(array_merge($parents, ['browser', 'entity_ids']));
    }

    if (strpos($value, self::$file) !== FALSE) {
      $file_id = str_replace('file:', '', $value);
      $file = File::load($file_id);
      $list_of_usage = \Drupal::service('file.usage')->listUsage($file);
      $mid = key($list_of_usage['file']['media']);
      $media = Media::load($mid);
      $entity_type = $media->getEntityTypeId();
      $result = $entity_type . ':' . $media->id();
    }
    else {
      $result = $value;
    }
    return $result;
  }

  /**
   * Render API callback: Processes the entity browser element.
   */
  public static function processEntityBrowser(&$element, FormStateInterface $form_state, &$complete_form) {
    if (!is_array($element['#default_value'])) {
      $element['#default_value'] = static::loadEntityBrowserEntitiesByIds($element['#default_value']);
    }
    $element = EntityBrowserElement::processEntityBrowser($element, $form_state, $complete_form);
    $element['entity_ids']['#ajax'] = [
      'callback' => [self::class, 'updateEntityBrowserSelected'],
      'wrapper' => $element['#wrapper_id'],
      'event' => 'entity_browser_value_updated',
    ];
    $element['entity_ids']['#default_value'] = implode(' ', array_keys($element['#default_value']));

    return $element;
  }

  /**
   * Render API callback: Processes the table element.
   */
  public static function processEntityBrowserSelected(&$element, FormStateInterface $form_state, &$complete_form) {
    // For deep form elements.
    $parents = $element['#array_parents'];
    array_pop($parents);

    // Added check on config form.
    if (isset($complete_form['#theme']) && $complete_form['#theme'] == 'system_config_form') {
      $entity_ids = $form_state->getValue(['browser', 'entity_ids']);
    }
    else {
      $entity_ids = $form_state->getValue(array_merge($parents, ['browser', 'entity_ids']), '');
    }

    $entities = empty($entity_ids) ? [] : self::loadEntityBrowserEntitiesByIds($entity_ids);
    $entity_type_manager = \Drupal::entityTypeManager();

    foreach ($entities as $id => $entity) {
      $entity_type_id = $entity->getEntityTypeId();
      if ($entity_type_manager->hasHandler($entity_type_id, 'view_builder')) {
        if ($entity_type_id == self::$file) {
          $list_of_usage = \Drupal::service('file.usage')->listUsage($entity);
          $mid = key($list_of_usage['file']['media']);
          $media = Media::load($mid);
          $entity_type_media = $media->getEntityTypeId();
          $preview = $entity_type_manager->getViewBuilder($entity_type_media)->view($media, $element['#view_mode']);
        }
        else {
          $preview = $entity_type_manager->getViewBuilder($entity_type_id)->view($entity, $element['#view_mode']);
        }
      }
      else {
        $preview = ['#markup' => $entity->label()];
      }
      $element[$id] = [
        '#attributes' => [
          'data-entity-id' => $id,
        ],
        'item' => $preview,
        'operations' => [
          'remove' => [
            '#type' => 'button',
            '#value' => t('Remove'),
            '#op' => 'remove',
            '#name' => 'remove_' . $id,
            '#ajax' => [
              'callback' => [self::class, 'updateEntityBrowserSelected'],
              'wrapper' => $element['#wrapper_id'],
            ],
          ],
        ],
      ];
    }
    return $element;
  }

  /**
   * AJAX callback: Re-renders the Entity Browser button/table.
   */
  public static function updateEntityBrowserSelected(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    if (isset($trigger['#op']) && $trigger['#op'] === 'remove') {
      $parents = array_slice($trigger['#array_parents'], 0, -4);
      $selection = NestedArray::getValue($form, $parents);
      $id = str_replace('remove_', '', $trigger['#name']);
      unset($selection['selected'][$id]);
      $value = explode(' ', $selection['browser']['entity_ids']['#value']);
      $selection['browser']['entity_ids']['#value'] = array_diff($value, [$id]);
    }
    else {
      $parents = array_slice($trigger['#array_parents'], 0, -2);
      $selection = NestedArray::getValue($form, $parents);
    }
    return $selection;
  }

}
