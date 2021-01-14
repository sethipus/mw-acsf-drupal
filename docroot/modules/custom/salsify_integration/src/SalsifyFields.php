<?php

namespace Drupal\salsify_integration;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use GuzzleHttp\ClientInterface;

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
   * The Mail manager service.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  private $mailManager;

  /**
   * The Language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * The Product helper service service.
   *
   * @var \Drupal\salsify_integration\ProductHelper
   */
  private $productHelper;

  /**
   * The Salsify email report service.
   *
   * @var \Drupal\salsify_integration\SalsifyEmailReport
   */
  private $salsifyEmailReport;

  /**
   * The Product fields mapper service.
   *
   * @var \Drupal\salsify_integration\ProductFieldsMapper
   */
  private $productFieldsMappger;

  /**
   * The Salsify import field service.
   *
   * @var \Drupal\salsify_integration\SalsifyImportField
   */
  private $salsifyImportField;

  /**
   * The Salsify import taxonomy service.
   *
   * @var \Drupal\salsify_integration\SalsifyImportTaxonomyTerm
   */
  private $salsifyImportTaxonomy;

  /**
   * Batch Builder.
   *
   * @var \Drupal\Core\Batch\BatchBuilder
   */
  protected $batchBuilder;

  /**
   * Salsify import taxonomy.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  private $messenger;

  /**
   * Constructs a \Drupal\salsify_integration\Salsify object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger interface.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory interface.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_salsify
   *   The cache object associated with the Salsify bin.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The Queue factory service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The Module handler service.
   * @param \Drupal\salsify_integration\MulesoftConnector $mulesoft_connector
   *   The Mulesoft connector.
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
   * @param \Drupal\salsify_integration\SalsifyProductRepository $salsify_product_repository
   *   Salsify product repository service.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   Mail manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager service.
   * @param \Drupal\salsify_integration\ProductHelper $product_helper
   *   Language manager service.
   * @param \Drupal\salsify_integration\SalsifyEmailReport $email_report
   *   Salsify email report service service.
   * @param \Drupal\salsify_integration\ProductFieldsMapper $product_fields_mapper
   *   Salsify products field mapper.
   * @param \Drupal\salsify_integration\SalsifyImportField $import_field
   *   Salsify import field service.
   * @param \Drupal\salsify_integration\SalsifyImportTaxonomyTerm $import_taxonomy
   *   Salsify import taxonomy service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   */
  public function __construct(
    LoggerChannelFactoryInterface $logger,
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    EntityFieldManagerInterface $entity_field_manager,
    CacheBackendInterface $cache_salsify,
    QueueFactory $queue_factory,
    ModuleHandlerInterface $module_handler,
    MulesoftConnector $mulesoft_connector,
    ClientInterface $client,
    SalsifyProductRepository $salsify_product_repository,
    MailManagerInterface $mail_manager,
    LanguageManagerInterface $language_manager,
    ProductHelper $product_helper,
    SalsifyEmailReport $email_report,
    ProductFieldsMapper $product_fields_mapper,
    SalsifyImportField $import_field,
    SalsifyImportTaxonomyTerm $import_taxonomy,
    MessengerInterface $messenger
  ) {
    parent::__construct(
      $logger,
      $config_factory,
      $entity_type_manager,
      $entity_field_manager,
      $cache_salsify,
      $queue_factory,
      $module_handler,
      $mulesoft_connector,
      $client
    );
    $this->salsifyProductRepository = $salsify_product_repository;
    $this->mailManager = $mail_manager;
    $this->languageManager = $language_manager;
    $this->productHelper = $product_helper;
    $this->salsifyEmailReport = $email_report;
    $this->productFieldsMappger = $product_fields_mapper;
    $this->salsifyImportField = $import_field;
    $this->salsifyImportTaxonomy = $import_taxonomy;
    $this->batchBuilder = new BatchBuilder();
    $this->messenger = $messenger;
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
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function importProductFields() {
    try {
      $entity_type = $this->getEntityType();
      $entity_bundle = $this->getEntityBundle();

      // Sync the fields in Drupal with the fields in the Salsify feed.
      $product_data = $this->syncDrupalAndSalsifyFields($entity_type, $entity_bundle);

      // Add custom mapping for product related content types.
      $this->productFieldsMappger->addProductFieldsMapping($entity_type);

      return $product_data;
    }
    catch (MissingDataException $e) {
      $message = $this->t('Could not complete Salsify field data import. A error occurred connecting with Salsify. @error', ['@error' => $e->getMessage()])->render();
      $this->logger->error($message);
      throw new MissingDataException($message);
    }

  }

  /**
   * Sync the fields in Drupal with the fields in the Salsify feed.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $entity_bundle
   *   Entity bundle.
   *
   * @return array
   *   Product data.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  private function syncDrupalAndSalsifyFields(string $entity_type, string $entity_bundle) {
    $import_method = $this->config->get('import_method');
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
      $salsify_fields = ($import_method == 'dynamic') ?
        array_diff_key($product_data['fields'], $manual_field_mapping) :
        array_intersect_key($product_data['fields'], $salsify_id_fields);

      // Setup the list of Drupal fields and machine names that belong to the
      // targeted entity and entity bundle.
      $filtered_fields = $this->getContentTypeFields($entity_type, $entity_bundle);

      // Find all of the fields from Salsify that are already in the system.
      // Check if they need to be updated using the "updated_at" field.
      $this->updateSalsifyFields(
        $salsify_fields,
        $field_mapping,
        $filtered_fields
      );

      // Create any fields that don't yet exist in the system.
      $this->createNonExistentFields(
        $filtered_fields,
        $salsify_fields,
        $field_mapping,
        $entity_type,
        $entity_bundle
      );

      // Find any fields that are already in the system that weren't in the
      // Salsify feed. This means they were deleted from Salsify, or the
      // import method has been changed from dynamic to manual. They need to
      // be deleted on the Drupal side.
      $this->removeDeletedSalsifyFields(
        $salsify_fields,
        $filtered_fields,
        $field_mapping,
        $entity_type,
        $entity_bundle
      );

    }
    else {
      $message = $this->t('Could not complete Salsify field data import. No content type configured.')->render();
      $this->logger->error($message);
      throw new MissingDataException($message);
    }

    return $product_data;
  }

  /**
   * Find all of the fields from Salsify that are already in the system.
   *
   * Check if they need to be updated using the "updated_at" field.
   *
   * @param array $salsify_fields
   *   Salsify fields.
   * @param mixed $field_mapping
   *   Field mapping.
   * @param array $filtered_fields
   *   Filtered fields.
   */
  private function updateSalsifyFields(
    array $salsify_fields,
    $field_mapping,
    array $filtered_fields
  ) {
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
  }

  /**
   * Create any fields that don't yet exist in the system.
   *
   * @param array $filtered_fields
   *   Filtered fields.
   * @param array $salsify_fields
   *   Salsify fields.
   * @param mixed $field_mapping
   *   Feild mapping array.
   * @param string $entity_type
   *   Entity type.
   * @param string $entity_bundle
   *   Entiry bundle.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function createNonExistentFields(
    array $filtered_fields,
    array $salsify_fields,
    $field_mapping,
    string $entity_type,
    string $entity_bundle
  ) {
    $salsify_diff = array_diff_key($salsify_fields, $field_mapping);
    $field_machine_names = array_keys($filtered_fields);
    foreach ($salsify_diff as $salsify_field) {
      $field_name = static::createFieldMachineName($salsify_field['salsify:id'], $field_machine_names);

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
  }

  /**
   * Remove Drupal fields deleted at Salsify side.
   *
   * Find any fields that are already in the system that weren't in the
   * Salsify feed. This means they were deleted from Salsify, or the
   * import method has been changed from dynamic to manual. They need to
   * be deleted on the Drupal side.
   *
   * @param array $salsify_fields
   *   Salsify fields.
   * @param array $filtered_fields
   *   Array of filtered fields.
   * @param mixed $field_mapping
   *   Field mapping.
   * @param string $entity_type
   *   Entity type.
   * @param string $entity_bundle
   *   Entity bundle.
   */
  private function removeDeletedSalsifyFields(
    array $salsify_fields,
    array $filtered_fields,
    $field_mapping,
    string $entity_type,
    string $entity_bundle
  ) {
    if ($filtered_fields) {
      // Determine the dynamically mapped fields that are in the field mapping
      // that aren't in the list of fields from Salsify.
      $field_diff = array_diff_key($field_mapping, $salsify_fields);
      $field_diff = $this->rekeyArray($field_diff ?? [], 'field_name');
      $remove_fields = array_intersect_key($filtered_fields, $field_diff);
      foreach ($remove_fields as $key => $field) {
        if (strpos($key, 'salsify') == 0) {
          $field->delete();
        }
      }

      $import_method = $this->config->get('import_method');
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

  /**
   * The main product import function.
   *
   * This is the main function of this class. Running this function will
   * initiate a field data sync prior to importing product data. Once the field
   * data is ready, the product data is imported using Drupal's queue system.
   *
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
  public function importProductData($force_update = FALSE) {
    try {
      // Refresh the product field settings from Salsify.
      $product_data = $this->importProductFields();

      // Import the taxonomy term data if needed and if any mappings are using
      // entity reference fields that point to taxonomy fields.
      $this->prepareTermData($product_data);

      // Sort product array in order to process product variant firstly,
      // then product and finally product multipack.
      $this->productHelper
        ->sortProducts($product_data['products']);

      // Import the actual product data.
      if (!empty($product_data['products'])) {

        $message = $this->t('The Salsify data import was initialized.');
        // Add items only to empty query in order to avoid
        // infinite queue.
        $number_of_items = $this->queueFactory
          ->get('salsify_integration_content_import')
          ->numberOfItems();
        if ($number_of_items == 0) {
          $this->addItemsToQueue($product_data, $force_update);
          $message = $this->t('The Salsify data import queue was created.');
        }

        $deleted_items = $this->salsifyProductRepository
          ->unpublishProducts(array_column($product_data['products'], 'salsify:id'));

        // Send report with deleted items.
        if (!empty($deleted_items)) {
          $this->salsifyEmailReport
            ->sendReport([], $deleted_items);
        }

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
  public function addChildLinks(array $mapping, array &$product) {
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
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function prepareTermData(array $product_data) {
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

        $this->processTermDataByFieldName(
          $field_mapping,
          $field_configs,
          $salsify_fields,
          $salsify_field_name
        );
      }
    }
  }

  /**
   * Process Term data based on field mapping data by salsify field name.
   *
   * @param array $field_mapping
   *   Field mapping.
   * @param array $field_configs
   *   Field configs.
   * @param array $salsify_fields
   *   Salsify fields.
   * @param mixed $salsify_field_name
   *   Salsify field name.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  private function processTermDataByFieldName(
    array $field_mapping,
    array $field_configs,
    array $salsify_fields,
    $salsify_field_name
  ) {

    $field_name = $field_mapping['field_name'];
    if (isset($field_configs[$field_name])) {
      $field_config = $field_configs[$field_name];
      /* @var \Drupal\field\Entity\FieldConfig $field_config */
      if ($field_config->getType() == 'entity_reference' && isset($salsify_fields[$salsify_field_name]['values'])) {
        $salsify_values = $salsify_fields[$salsify_field_name]['values'];
        $field_handler = $field_config->getSetting('handler');
        $field_handler_settings = $field_config->getSetting('handler_settings');
        if ($field_handler == 'default:taxonomy_term' && !empty($field_handler_settings['target_bundles'])) {
          // Only use the first taxonomy in the list.
          $vid = current($field_handler_settings['target_bundles']);
          $salsify_ids = array_keys($salsify_values);
          $salsify_ids_array = array_chunk($salsify_ids, 50);
          foreach ($salsify_ids_array as $salsify_ids_chunk) {
            $this->salsifyImportTaxonomy
              ->processSalsifyTaxonomyTermItems(
                $vid,
                $field_mapping,
                $salsify_ids_chunk,
                $salsify_fields[$field_mapping['salsify_id']]
              );
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
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function createDynamicField(
    array $salsify_data,
    $field_name,
    $entity_type = '',
    $entity_bundle = ''
  ) {
    $config = \Drupal::service('config.factory')
      ->get('salsify_integration.settings');
    /* @var \Drupal\Core\Config\ImmutableConfig $config */
    if (!$entity_type) {
      $entity_type = $config->get('entity_type');
    }
    if (!$entity_bundle) {
      $entity_bundle = $config->get('bundle');
    }
    $field_storage = FieldStorageConfig::loadByName($entity_type, $field_name);
    $field_settings = static::getFieldSettingsByType($salsify_data, $entity_type, $entity_bundle, $field_name);
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
        static::createFieldViewDisplay($entity_type, $entity_bundle, $field_name, 'default');
        static::createFieldFormDisplay($entity_type, $entity_bundle, $field_name, $salsify_data['salsify:data_type']);
      }
    }

    // Add a record to track the Salsify field and the new Drupal field map.
    static::createFieldMapping([
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
  protected static function getFieldSettingsByType(array $salsify_data, $entity_type, $entity_bundle, $field_name) {
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
        static::setFieldOptions($salsify_data);
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
    $view_storage = \Drupal::entityTypeManager()
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
      $view_storage = \Drupal::entityTypeManager()
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
    $form_storage = \Drupal::entityTypeManager()
      ->getStorage('entity_form_display')
      ->load($form_storage_id);
    if (!is_object($form_storage)) {
      $form_storage = \Drupal::entityTypeManager()->getStorage('entity_form_display')
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
