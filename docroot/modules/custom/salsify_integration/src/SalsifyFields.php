<?php

namespace Drupal\salsify_integration;

use Drupal;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Salsify.
 *
 * @package Drupal\salsify_integration
 */
class SalsifyFields extends Salsify {

  /**
   * The salsify product repository.
   *
   * @var \Drupal\salsify_integration\SalsifyProductRepository
   */
  private $salsifyProductRepository;

  /**
   * Constructs a \Drupal\salsify_integration\Salsify object.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger interface.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory interface.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The query factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_salsify
   *   The cache object associated with the Salsify bin.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   Queue factory service.
   * @param \Drupal\salsify_integration\SalsifyProductRepository $salsify_product_repository
   *   Salsify product repository service.
   */
  public function __construct(
    LoggerInterface $logger,
    ConfigFactoryInterface $config_factory,
    QueryFactory $entity_query,
    EntityTypeManagerInterface $entity_type_manager,
    EntityFieldManagerInterface $entity_field_manager,
    CacheBackendInterface $cache_salsify,
    QueueFactory $queue_factory,
    SalsifyProductRepository $salsify_product_repository
  ) {
    parent::__construct(
      $logger,
      $config_factory,
      $entity_query,
      $entity_type_manager,
      $entity_field_manager,
      $cache_salsify,
      $queue_factory
    );
    $this->salsifyProductRepository = $salsify_product_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory')->get('salsify_integration'),
      $container->get('config.factory'),
      $container->get('entity.query'),
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('cache.default'),
      $container->get('queue'),
      $container->get('salsify_integration.salsify_product_repository')
    );
  }

  /**
   * The main Salsify product field import function.
   *
   * This function syncs field configuration data from Salsify and ensures that
   * Drupal is ready to receive the fields that were passed in the product
   * feed from Salsify.
   *
   * @return mixed
   *   Returns an array of product and field data or a failure message.
   */
  public function importProductFields() {
    try {
      $import_method = $this->config->get('import_method');
      $entity_type = $this->getEntityType();
      $entity_bundle = $this->getEntityBundle();

      // Sync the fields in Drupal with the fields in the Salsify feed.
      // TODO: Put this logic into a queue since it can get resource intensive.
      if ($entity_type && $entity_bundle) {
        // Load the product and field data from Salsify.
        $product_data = $this->getProductData();

        $field_mapping = $this->getFieldMappings(
          [
            'entity_type' => $entity_type,
            'bundle' => $entity_bundle,
            'method' => 'dynamic',
          ],
          'salsify_id'
        );

        // Remove the manually mapped Salsify Fields from the product data so
        // they aren't added back into the system.
        $manual_field_mapping = $this->getFieldMappings(
          [
            'entity_type' => $entity_type,
            'bundle' => $entity_bundle,
            'method' => 'manual',
          ],
          'salsify_id'
        );
        $salsify_id_fields = parent::getSystemFieldNames();

        // Only generate new fields if the import method is dynamic. Otherwise
        // only generate the required system tracking fields.
        if ($import_method == 'dynamic') {
          $salsify_fields = array_diff_key($product_data['fields'], $manual_field_mapping);
        }
        else {
          $salsify_fields = array_intersect_key($product_data['fields'], $salsify_id_fields);
        }

        // Determine the dynamically mapped fields that are in the field mapping
        // that aren't in the list of fields from Salsify.
        $field_diff = array_diff_key($field_mapping, $salsify_fields);

        // Setup the list of Drupal fields and machine names that belong to the
        // targeted entity and entity bundle.
        $filtered_fields = $this->getContentTypeFields($entity_type, $entity_bundle);
        $field_machine_names = array_keys($filtered_fields);

        // Find all of the fields from Salsify that are already in the system.
        // Check if they need to be updated using the "updated_at" field.
        $salsify_intersect = array_intersect_key($salsify_fields, $field_mapping);
        foreach ($salsify_intersect as $key => $salsify_field) {
          $updated = $salsify_field['date_updated'];
          if ($updated <> $field_mapping[$key]['changed']) {
            $updated_mapping = $field_mapping[$key];
            $updated_mapping['changed'] = $updated;
            $this->updateFieldMapping($updated_mapping);
            $this->updateDynamicField($salsify_field, $filtered_fields[$field_mapping[$key]['field_name']]);
          }
        }

        // $field_mapping['field_product_brand'] = [
        // 'salsify:id' => 'field_product_brand'
        // ];
        // Create any fields that don't yet exist in the system.
        $salsify_diff = array_diff_key($salsify_fields, $field_mapping);
        foreach ($salsify_diff as $salsify_field) {
          $field_name = self::createFieldMachineName($salsify_field['salsify:id'], $field_machine_names);

          // If the field exists on the system, but isn't in the map, just add
          // it to the map instead of trying to create a new field. This
          // should cover if fields were left over from an uninstall.
          if (isset($filtered_fields[$field_name])) {
            $this->createFieldMapping([
              'field_id' => $salsify_field['salsify:system_id'],
              'salsify_id' => $salsify_field['salsify:id'],
              'salsify_data_type' => $salsify_field['salsify:data_type'],
              'entity_type' => $entity_type,
              'bundle' => $entity_bundle,
              'field_name' => $field_name,
              'method' => 'dynamic',
              'created' => strtotime($salsify_field['salsify:created_at']),
              'changed' => $salsify_field['date_updated'],
            ]);
          }
          // Add a record to track the Salsify field and the new Drupal field
          // map.
          else {
            $this->createDynamicField($salsify_field, $field_name);
          }

        }

        // Find any fields that are already in the system that weren't in the
        // Salsify feed. This means they were deleted from Salsify, or the
        // import method has been changed from dynamic to manual. They need to
        // be deleted on the Drupal side.
        if ($filtered_fields) {
          $field_diff = $this->rekeyArray($field_diff, 'field_name');
          $remove_fields = array_intersect_key($filtered_fields, $field_diff);
          foreach ($remove_fields as $key => $field) {
            if (strpos($key, 'salsify') == 0) {
              $field->delete();
            }
          }
          // If the import method is manual, remove any dynamically generated
          // field based on the prefix "salsifysync". This will preserve the
          // "salsify_" prefixed fields that are required for this integration
          // to function properly.
          if ($import_method == 'manual') {
            foreach ($filtered_fields as $field_name => $filtered_field) {
              if (strpos($field_name, 'salsifysync_') !== FALSE) {
                $field_diff[$field_name] = $filtered_field;
              }
            }
          }
          foreach ($field_diff as $salsify_field_id => $field) {
            if (isset($field_mapping[$salsify_field_id])) {
              $diff_field_name = $field_mapping[$salsify_field_id]['field_name'];
              if (isset($filtered_fields[$diff_field_name])) {
                $this->deleteDynamicField($filtered_fields[$diff_field_name]);
              }
              // Remove the options listing for this field.
              $this->removeFieldOptions($salsify_field_id);
              // Delete the field mapping from the database.
              $this->deleteFieldMapping(
                [
                  'entity_type' => $entity_type,
                  'bundle' => $entity_bundle,
                  'salsify_id' => $salsify_field_id,
                ]
              );
            }
          }
        }

      }
      else {
        $message = $this->t('Could not complete Salsify field data import. No content type configured.')->render();
        $this->logger->error($message);
        throw new MissingDataException($message);
      }

      $product_variant_mapping = $this->getFieldMappings(
        [
          'entity_type' => $entity_type,
          'bundle' => 'product_variant',
          'method' => 'manual',
        ],
        'salsify_id'
      );

      $product_mapping = $this->getFieldMappings(
        [
          'entity_type' => $entity_type,
          'bundle' => 'product',
          'method' => 'manual',
        ],
        'salsify_id'
      );

      $product_multipack_mapping = $this->getFieldMappings(
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

          $this->createFieldMapping([
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

          $this->createFieldMapping([
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

          $this->createFieldMapping([
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

      $media_mapping = $this->getFieldMappings(
        [
          'entity_type' => 'media',
          'bundle' => 'image',
          'method' => 'manual',
        ],
        'salsify_id'
      );

      if (!isset($media_mapping['salsify:url'])) {
        $this->createFieldMapping([
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

      return $product_data;
    }
    catch (MissingDataException $e) {
      $message = $this->t('Could not complete Salsify field data import. A error occurred connecting with Salsify. @error', ['@error' => $e->getMessage()])->render();
      $this->logger->error($message);
      throw new MissingDataException($message);
    }

  }

  /**
   * The main product import function.
   *
   * This is the main function of this class. Running this function will
   * initiate a field data sync prior to importing product data. Once the field
   * data is ready, the product data is imported using Drupal's queue system.
   *
   * @param bool $process_immediately
   *   If set to TRUE, the product import will bypass the queue system.
   * @param bool $force_update
   *   If set to TRUE, the updated date highwater mark will be ignored.
   *
   * @return array
   *   Result message.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function importProductData($process_immediately = FALSE, $force_update = FALSE) {
    try {
      // Refresh the product field settings from Salsify.
      $product_data = $this->importProductFields();

      // Import the taxonomy term data if needed and if any mappings are using
      // entity reference fields that point to taxonomy fields.
      $this->prepareTermData($product_data);

      // Import the actual product data.
      if (!empty($product_data['products'])) {
        // Handle cases where the user wants to perform all of the data
        // processing immediately instead of waiting for the queue to finish.
        if ($process_immediately) {
          $salsify_import = SalsifyImportField::create(Drupal::getContainer());

          // Product variant import.
          $variant_process_result = $this->processItems(
            $product_data,
            $salsify_import,
            $force_update,
            ProductHelper::PRODUCT_VARIANT_CONTENT_TYPE
          );

          // Product import.
          $product_process_result = $this->processItems(
            $product_data,
            $salsify_import,
            $force_update,
            ProductHelper::PRODUCT_CONTENT_TYPE
          );

          // Product multipack import.
          $multipack_process_result = $this->processItems(
            $product_data,
            $salsify_import,
            $force_update,
            ProductHelper::PRODUCT_MULTIPACK_CONTENT_TYPE
          );

          $process_result = array_merge_recursive(
            $variant_process_result,
            $product_process_result,
            $multipack_process_result
          );
          $this->logger->info($this->t(
            'The Salsify data import is complete. @created @updated', [
              '@created' => 'Created products: ' . implode(', ', $process_result['created_products']) . '.',
              '@updated' => 'Updated products: ' . implode(', ', $process_result['updated_products']) . '.',
            ]
          ));

          $message = $this->t('The Salsify data import is complete.');
        }
        // Add each product value into a queue for background processing.
        else {
          $this->addItemsToQueue($product_data, $force_update);
          $message = $this->t('The Salsify data import queue was created.');
        }

        // Unpublish products in case of deletion at Salsify side.
        $this->salsifyProductRepository
          ->unpublishProducts($product_data['products']);

        return [
          'status' => 'status',
          'message' => $message,
        ];

      }
      else {
        $message = $this->t('Could not complete Salsify data import. No product data is available')->render();
        $this->logger->error($message);
        return [
          'status' => 'error',
          'message' => $message,
        ];
      }
    }
    catch (MissingDataException $e) {
      $message = $this->t('A error occurred while making the request to Salsify. Check the API settings and try again.')->render();
      $this->logger->error($message);
      return [
        'status' => 'error',
        'message' => $message,
      ];
    }

  }

  /**
   * Process salsify items.
   *
   * @param mixed $product_data
   *   Array of salsify products.
   * @param \Drupal\salsify_integration\SalsifyImport $salsify_import
   *   Salsify import service.
   * @param bool $force_update
   *   Force update.
   * @param string $content_type
   *   Content type for import.
   *
   * @return array
   *   Array of updated and created GTINs.
   */
  private function processItems(
    &$product_data,
    SalsifyImport $salsify_import,
    bool $force_update,
    $content_type
  ) {
    $updated_products = [];
    $created_products = [];

    foreach ($product_data['products'] as $product) {
      // Add child entity references.
      $this->addChildLinks($product_data['mapping'], $product);
      $product['CMS: Market'] = $product_data['market'] ?? NULL;

      if (ProductHelper::getProductType($product) == $content_type) {
        $result = $salsify_import->processSalsifyItem(
          $product,
          $force_update,
          $content_type
        );

        if ($result == SalsifyImport::PROCESS_RESULT_UPDATED) {
          $updated_products[] = $product['GTIN'];
        }
        elseif ($result == SalsifyImport::PROCESS_RESULT_CREATED) {
          $created_products[] = $product['GTIN'];
        }
      }
    }

    return [
      'updated_products' => $updated_products,
      'created_products' => $created_products,
    ];
  }

  /**
   * Add queue items.
   *
   * @param array $product_data
   *   Salsify data.
   * @param bool $force_update
   *   Force update flag.
   */
  private function addItemsToQueue(array &$product_data, bool $force_update) {
    $queue = $this->queueFactory
      ->get('salsify_integration_content_import');
    foreach ($product_data['products'] as $product) {
      // Add child entity references.
      $this->addChildLinks($product_data['mapping'], $product);
      $product['CMS: Market'] = $product_data['market'] ?? NULL;

      $product['force_update'] = $force_update;
      $queue->createItem($product);
    }
  }

  /**
   * Add child items as a custom field.
   *
   * @param array $mapping
   *   Relationship of parent and child records.
   * @param array $product
   *   Product record.
   */
  private function addChildLinks(array $mapping, array &$product) {
    if (isset($mapping[$product['GTIN']])) {
      foreach ($mapping[$product['GTIN']] as $child_gtin => $child_type) {
        if ($child_type == ProductHelper::PRODUCT_VARIANT_CONTENT_TYPE) {
          $product['CMS: Child variants'][] = $child_gtin;
        }
        elseif ($child_type == ProductHelper::PRODUCT_CONTENT_TYPE) {
          $product['CMS: Child products'][] = $child_gtin;
        }
      }
    }
  }

  /**
   * Prepare term data.
   *
   * @param array $product_data
   *   Data of product.
   */
  protected function prepareTermData(array $product_data) {
    $salsify_fields = $product_data['fields'];
    $entity_type = $this->getEntityType();
    $entity_bundle = $this->getEntityBundle();
    $field_mappings = [
      'dynamic' => $this->getFieldMappings(
        [
          'entity_type' => $entity_type,
          'bundle' => $entity_bundle,
          'method' => 'dynamic',
        ],
        'salsify_id'
      ),
      'manual' => $this->getFieldMappings(
        [
          'entity_type' => $entity_type,
          'bundle' => $entity_bundle,
          'method' => 'manual',
        ],
        'salsify_id'
      ),
    ];
    $field_configs = $this->getContentTypeFields($entity_type, $entity_bundle);
    foreach ($field_mappings as $field_mappings_by_method) {
      foreach ($field_mappings_by_method as $salsify_field_name => $field_mapping) {
        $field_name = $field_mapping['field_name'];
        if (isset($field_configs[$field_name])) {
          $field_config = $field_configs[$field_name];
          if ($field_config->getType() == 'entity_reference' && isset($salsify_fields[$salsify_field_name]['values'])) {
            $salsify_values = $salsify_fields[$salsify_field_name]['values'];
            $field_handler = $field_config->getSetting('handler');
            $field_handler_settings = $field_config->getSetting('handler_settings');
            if ($field_handler == 'default:taxonomy_term' && !empty($field_handler_settings['target_bundles'])) {
              // Only use the first taxonomy in the list.
              $vid = current($field_handler_settings['target_bundles']);
              $term_import = SalsifyImportTaxonomyTerm::create(Drupal::getContainer());
              $salsify_ids = array_keys($salsify_values);
              $salsify_ids_array = array_chunk($salsify_ids, 50);
              foreach ($salsify_ids_array as $salsify_ids_chunk) {
                $term_import->processSalsifyTaxonomyTermItems($vid, $field_mapping, $salsify_ids_chunk, $salsify_fields[$field_mapping['salsify_id']]);
              }
            }
          }
        }
      }
    }
  }

  /**
   * Utility function that creates a Drupal-compatible field name.
   *
   * @param string $field_name
   *   Salsify string field name.
   * @param array $field_machine_names
   *   Array of Drupal configured fields to use to prevent duplication.
   *
   * @return string
   *   Drupal field machine name.
   */
  public static function createFieldMachineName($field_name, array &$field_machine_names) {
    // Differentiate between default and custom salsify fields.
    if (strpos($field_name, 'salsify:') !== FALSE) {
      $salsify_ids = parent::getSystemFieldNames();
      if (isset($salsify_ids[$field_name])) {
        return $salsify_ids[$field_name];
      }
    }

    // Set the default dynamic field prefix.
    $prefix = 'salsifysync_';

    // Clean the string to remove spaces.
    $field_name = str_replace('-', '_', $field_name);
    $field_name = preg_replace('/[^A-Za-z0-9\-]/', '', $field_name);
    $cleaned_string = strtolower($prefix . $field_name);
    $new_field_name = substr($cleaned_string, 0, 32);

    // Check for duplicate field names and append an integer value until a
    // unique field name is found.
    if (in_array($new_field_name, $field_machine_names)) {
      $count = 0;
      while (in_array($new_field_name, $field_machine_names)) {
        $length = 32 - strlen($count) - 1;
        $new_field_name = substr($new_field_name, 0, $length) . '_' . $count;
        $count++;
      }
    }
    // Add the new field name to the field array.
    $field_machine_names[] = $new_field_name;

    return $new_field_name;
  }

  /**
   * Utility function that creates a field on a node for Salsify data.
   *
   * NOTE: Normally the Entity Type and Content Type should be managed by
   * this module. These values currently exist to allow the creation of a field
   * against other entities when entity reference fields are utilized.
   *
   * @param array $salsify_data
   *   The Salsify entry for this field.
   * @param string $field_name
   *   The machine name for the Drupal field.
   * @param string $entity_type
   *   The entity type to create the field against. Defaults to node.
   * @param string $entity_bundle
   *   The entity bundle to set the field against.
   */
  public function createDynamicField(array $salsify_data, $field_name, $entity_type = '', $entity_bundle = '') {
    if (!$entity_type) {
      $entity_type = $this->getEntityType();
    }
    if (!$entity_bundle) {
      $entity_bundle = $this->getEntityBundle();
    }
    $field_storage = FieldStorageConfig::loadByName($entity_type, $field_name);
    $field_settings = $this->getFieldSettingsByType($salsify_data, $entity_type, $entity_bundle, $field_name);
    $field = FieldConfig::loadByName($entity_type, $entity_bundle, $field_name);
    $created = strtotime($salsify_data['salsify:created_at']);
    $changed = $salsify_data['date_updated'];
    if (empty($field_storage)) {
      $field_storage = FieldStorageConfig::create($field_settings['field_storage']);
      $field_storage->save();
    }
    if (empty($field)) {
      // Setup the field configuration options.
      $field_settings['field']['field_storage'] = $field_storage;
      // Create the field against the given content type.
      $field = FieldConfig::create($field_settings['field']);
      $field->save();

      // Only add user-facing fields onto the form and view displays. Otherwise
      // allow the fields to remain hidden (which is default).
      if (strpos($field_name, 'salsifysync_') !== FALSE) {
        // Add the field to the default displays.
        /* @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $view_storage */
        $this->createFieldViewDisplay($entity_type, $entity_bundle, $field_name, 'default');
        $this->createFieldFormDisplay($entity_type, $entity_bundle, $field_name, $salsify_data['salsify:data_type']);
      }
    }

    // Add a record to track the Salsify field and the new Drupal field map.
    $this->createFieldMapping([
      'field_id' => $salsify_data['salsify:system_id'],
      'salsify_id' => $salsify_data['salsify:id'],
      'salsify_data_type' => $salsify_data['salsify:data_type'],
      'entity_type' => $entity_type,
      'bundle' => $entity_bundle,
      'field_name' => $field_name,
      'method' => 'dynamic',
      'created' => $created,
      'changed' => $changed,
    ]);

  }

  /**
   * Utility function to update a dynamic field's settings.
   *
   * @param array $salsify_field
   *   The array of field data from Salsify.
   * @param \Drupal\field\Entity\FieldConfig $field
   *   The field configuration object from the content type.
   */
  protected function updateDynamicField(array $salsify_field, FieldConfig $field) {
    // Update the label on the field to pull in any changes from Salsify.
    $field->set('label', $salsify_field['salsify:name']);
    $field->save();
    // Update the options list on enumerated fields.
    if ($salsify_field['salsify:data_type'] == 'enumerated') {
      $this->setFieldOptions($salsify_field);
    }
  }

  /**
   * Helper function that returns Drupal field options based on a Salsify type.
   *
   * @param array $salsify_data
   *   The Salsify entry for this field.
   * @param string $entity_type
   *   The type of entity to use when pulling field definitions.
   * @param string $entity_bundle
   *   The content type to set the field against.
   * @param string $field_name
   *   The machine name for the Drupal field.
   *
   * @return array
   *   An array of field options for the generated field.
   */
  protected function getFieldSettingsByType(array $salsify_data, $entity_type, $entity_bundle, $field_name) {
    $field_settings = [
      'field' => [
        'field_name' => $field_name,
        'entity_type' => $entity_type,
        'bundle' => $entity_bundle,
        'label' => $salsify_data['salsify:name'],
      ],
      'field_storage' => [
        'id' => $entity_type . '.' . $field_name,
        'field_name' => $field_name,
        'entity_type' => $entity_type,
        'type' => 'string',
        'settings' => [],
        'module' => 'text',
        'locked' => FALSE,
        'cardinality' => -1,
        'translatable' => TRUE,
        'indexes' => [],
        'persist_with_no_fields' => FALSE,
        'custom_storage' => FALSE,
      ],
    ];

    // Map the Salsify data types to Drupal field types and set default options.
    switch ($salsify_data['salsify:data_type']) {
      case 'digital_asset':
        if ($salsify_data['salsify:attribute_group'] == 'Images') {
          $field_settings['field_storage']['type'] = 'image';
          $field_settings['field_storage']['cardinality'] = -1;
          $field_settings['field_storage']['settings']['allowed_values_function'] = 'salsify_integration_allowed_values_callback';
        }
        break;

      case 'enumerated':
        $field_settings['field_storage']['type'] = 'list_string';
        $field_settings['field_storage']['cardinality'] = -1;
        $field_settings['field_storage']['settings']['allowed_values_function'] = 'salsify_integration_allowed_values_callback';
        $this->setFieldOptions($salsify_data);
        break;

      case 'date':
        $field_settings['field_storage']['type'] = 'datetime';
        $field_settings['field_storage']['module'] = 'datetime';
        $field_settings['field_storage']['settings'] = [
          'datetime_type' => 'date',
        ];
        break;

      case 'boolean':
        $field_settings['field_storage']['type'] = 'boolean';
        $field_settings['field_storage']['module'] = 'core';
        $field_settings['field']['settings'] = [
          'on_label' => 'Yes',
          'off_label' => 'No',
        ];
        break;

      case 'rich_text':
        $field_settings['field_storage']['type'] = 'text_long';
        $field_settings['field_storage']['module'] = 'text';
        break;

      case 'html':
        $field_settings['field_storage']['type'] = 'string_long';
        $field_settings['field_storage']['module'] = 'core';
        break;

      case 'link':
        $field_settings['field_storage']['type'] = 'link';
        $field_settings['field_storage']['module'] = 'link';
        $field_settings['field']['settings'] = [
          'link_type' => 16,
          'title' => 0,
        ];
        break;

      case 'number':
        $field_settings['field_storage']['type'] = 'integer';
        $field_settings['field_storage']['module'] = 'core';
        $field_settings['field_storage']['settings'] = [
          'unsigned' => 0,
          'size' => 'normal',
        ];
        $field_settings['field']['field_type'] = 'integer';
        break;

    }

    return $field_settings;
  }

  /**
   * Utility function to add a field onto a node's display.
   *
   * @param string $entity_type
   *   The entity type to set the field against.
   * @param string $entity_bundle
   *   The entity bundle to set the field against.
   * @param string $field_name
   *   The machine name for the Drupal field.
   * @param string $view_mode
   *   The view mode on which to add the field.
   */
  public static function createFieldViewDisplay($entity_type, $entity_bundle, $field_name, $view_mode) {
    /* @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $view_storage */
    $view_storage = Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load($entity_type . '.' . $entity_bundle . '.' . $view_mode);

    // If the node display doesn't exist, create it in order to set the field.
    if (empty($view_storage)) {
      $values = [
        'targetEntityType' => $entity_type,
        'bundle' => $entity_bundle,
        'mode' => $view_mode,
        'status' => TRUE,
      ];
      $view_storage = Drupal::entityTypeManager()
        ->getStorage('entity_view_display')
        ->create($values);
    }

    $view_storage->setComponent($field_name, [
      'label' => 'above',
      'weight' => 0,
    ])->save();
  }

  /**
   * Utility function to add a field onto a node's form display.
   *
   * @param string $entity_type
   *   The entity type to set the field against.
   * @param string $entity_bundle
   *   The entity bundle to set the field against.
   * @param string $field_name
   *   The machine name for the Drupal field.
   * @param string $salsify_type
   *   The Salsify data type for this field.
   */
  public static function createFieldFormDisplay($entity_type, $entity_bundle, $field_name, $salsify_type) {
    /* @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $form_storage */
    $form_storage_id = $entity_type . '.' . $entity_bundle . '.default';
    $form_storage = Drupal::entityTypeManager()
      ->getStorage('entity_form_display')
      ->load($form_storage_id);
    if (!is_object($form_storage)) {
      $form_storage = Drupal::entityTypeManager()->getStorage('entity_form_display')
        ->create([
          'id' => $form_storage_id,
          'bundle' => $entity_bundle,
          'targetEntityType' => $entity_type,
          'displayContext' => 'form',
          'mode' => 'default',
          'status' => TRUE,
        ]);
      $form_storage->save();
    }
    $field_options = [
      'weight' => 0,
    ];

    // Set the default form widget for multi-value options to checkboxes.
    if ($salsify_type == 'enumerated') {
      $field_options['type'] = 'options_buttons';
    }

    $form_storage->setComponent($field_name, $field_options)->save();

  }

}
