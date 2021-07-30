<?php

namespace Drupal\salsify_integration;

/**
 * Class ProductFieldsMapper - mapping logic (Salsify - Drupal fields).
 *
 * @package Drupal\salsify_integration
 */
class ProductFieldsMapper {

  /**
   * Add salsify fields mapping for product related content-types.
   *
   * @param string $entity_type
   *   Entity type.
   */
  public function addProductFieldsMapping($entity_type = 'node') {
    $product_variant_mapping = Salsify::getFieldMappings(
      [
        'entity_type' => $entity_type,
        'bundle' => 'product_variant',
        'method' => 'manual',
      ],
      'salsify_id'
    );

    $product_mapping = Salsify::getFieldMappings(
      [
        'entity_type' => $entity_type,
        'bundle' => 'product',
        'method' => 'manual',
      ],
      'salsify_id'
    );

    $product_multipack_mapping = Salsify::getFieldMappings(
      [
        'entity_type' => $entity_type,
        'bundle' => 'product_multipack',
        'method' => 'manual',
      ],
      'salsify_id'
    );

    foreach (SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT as $drupal_field => $salsify_field_map) {
      if (isset($salsify_field_map['salsify:id']) &&
        !in_array($salsify_field_map['salsify:id'], array_keys($product_mapping))) {

        Salsify::createFieldMapping([
          'field_id' => $salsify_field_map['salsify:id'],
          'salsify_id' => $salsify_field_map['salsify:id'],
          'salsify_data_type' => $salsify_field_map['salsify:data_type'],
          'entity_type' => $entity_type,
          'bundle' => 'product',
          'field_name' => $drupal_field,
          'method' => 'manual',
          'created' => Salsify::FIELD_MAP_CREATED,
          'changed' => Salsify::FIELD_MAP_CHANGED,
        ]);
      }
    }

    foreach (SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT_MULTIPACK as $drupal_field => $salsify_field_map) {
      if (isset($salsify_field_map['salsify:id']) &&
        !in_array($salsify_field_map['salsify:id'], array_keys($product_multipack_mapping))) {

        Salsify::createFieldMapping([
          'field_id' => $salsify_field_map['salsify:id'],
          'salsify_id' => $salsify_field_map['salsify:id'],
          'salsify_data_type' => $salsify_field_map['salsify:data_type'],
          'entity_type' => $entity_type,
          'bundle' => 'product_multipack',
          'field_name' => $drupal_field,
          'method' => 'manual',
          'created' => Salsify::FIELD_MAP_CREATED,
          'changed' => Salsify::FIELD_MAP_CHANGED,
        ]);
      }
    }

    foreach (SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT_VARIANT as $drupal_field => $salsify_field_map) {
      if (isset($salsify_field_map['salsify:id']) &&
        !in_array($salsify_field_map['salsify:id'], array_keys($product_variant_mapping))) {

        Salsify::createFieldMapping([
          'field_id' => $salsify_field_map['salsify:id'],
          'salsify_id' => $salsify_field_map['salsify:id'],
          'salsify_data_type' => $salsify_field_map['salsify:data_type'],
          'entity_type' => $entity_type,
          'bundle' => 'product_variant',
          'field_name' => $drupal_field,
          'method' => 'manual',
          'created' => Salsify::FIELD_MAP_CREATED,
          'changed' => Salsify::FIELD_MAP_CHANGED,
        ]);
      }
    }

    $media_mapping = Salsify::getFieldMappings(
      [
        'entity_type' => 'media',
        'bundle' => 'image',
        'method' => 'manual',
      ],
      'salsify_id'
    );

    if (!isset($media_mapping['salsify:url'])) {
      Salsify::createFieldMapping([
        'field_id' => 'salsify:url',
        'salsify_id' => 'salsify:url',
        'salsify_data_type' => 'string',
        'entity_type' => 'media',
        'bundle' => 'image',
        'field_name' => 'image',
        'method' => 'manual',
        'created' => Salsify::FIELD_MAP_CREATED,
        'changed' => Salsify::FIELD_MAP_CHANGED,
      ]);
    }
  }

}
