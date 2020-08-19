<?php

namespace Drupal\mars_common\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'carousel item' field type.
 *
 * @FieldType(
 *   id = "carousel_item",
 *   label = @Translation("Carousel item"),
 *   description = @Translation("Carousel item."),
 *   default_widget = "carousel_item_default_widget",
 *   default_formatter = "carousel_item_formatter",
 *   column_groups = {
 *     "file" = {
 *       "label" = @Translation("Carousel item"),
 *       "columns" = {
 *         "target_id", "width", "height"
 *       },
 *       "require_all_groups_for_translation" = TRUE
 *     },
 *     "desc" = {
 *       "label" = @Translation("Desc"),
 *       "translatable" = TRUE
 *     },
 *   },
 *   list_class = "\Drupal\file\Plugin\Field\FieldType\FileFieldItemList",
 *   constraints = {"ReferenceAccess" = {}, "FileValidation" = {}}
 * )
 */
class CarouselItem extends FileTextItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'target_id' => [
          'description' => 'The ID of the file entity.',
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'desc' => [
          'description' => "Description of the item.",
          'type' => 'varchar',
          'length' => 512,
        ],
        'width' => [
          'description' => 'The width of the image in pixels.',
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'height' => [
          'description' => 'The height of the image in pixels.',
          'type' => 'int',
          'unsigned' => TRUE,
        ],
      ],
      'indexes' => [
        'target_id' => ['target_id'],
      ],
      'foreign keys' => [
        'target_id' => [
          'table' => 'file_managed',
          'columns' => ['target_id' => 'fid'],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    unset($properties['display']);
    unset($properties['description']);

    $properties['desc'] = DataDefinition::create('string')
      ->setLabel(t('Item description'))
      ->setDescription(t("Description text, for the carousel item."));
    $properties['width'] = DataDefinition::create('integer')
      ->setLabel(t('Width'))
      ->setDescription(t('The width of the image in pixels.'));

    $properties['height'] = DataDefinition::create('integer')
      ->setLabel(t('Height'))
      ->setDescription(t('The height of the image in pixels.'));

    return $properties;
  }

}
