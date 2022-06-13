<?php

namespace Drupal\salsify_integration;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\field\Entity\FieldConfig;

/**
 * Class SalsifyImportField.
 *
 * The class used to perform content imports in to individual fields. Imports
 * are triggered either through queues during a cron run or via the
 * configuration page.
 *
 * @package Drupal\salsify_integration
 */
class SalsifyImportField extends SalsifyImport {

  /**
   * The Salsify product repository.
   *
   * @var \Drupal\salsify_integration\SalsifyProductRepository
   */
  private $salsifyProductRepository;

  /**
   * Product data helper.
   *
   * @var \Drupal\salsify_integration\ProductHelper
   */
  private $productDataHelper;

  /**
   * Salsify import media.
   *
   * @var \Drupal\salsify_integration\SalsifyImportMedia
   */
  private $salsifyImportMedia;

  /**
   * Salsify import taxonomy.
   *
   * @var \Drupal\salsify_integration\SalsifyImportTaxonomyTerm
   */
  private $salsifyImportTaxonomy;

  /**
   * SalsifyImportField constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager interface.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_salsify
   *   The Salsify cache interface.
   * @param \Drupal\salsify_integration\Salsify $salsify
   *   The Salsify cache interface.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler interface.
   * @param \Drupal\salsify_integration\SalsifyProductRepository $salsify_product_repository
   *   The salsify product repository.
   * @param \Drupal\salsify_integration\ProductHelper $product_data_helper
   *   The product data helper.
   * @param \Drupal\salsify_integration\SalsifyImportMedia $salsify_import_media
   *   The Salsify import media service.
   * @param \Drupal\salsify_integration\SalsifyImportTaxonomyTerm $salsify_import_taxonomy
   *   The Salsify import taxonomy service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    CacheBackendInterface $cache_salsify,
    Salsify $salsify,
    ModuleHandlerInterface $module_handler,
    SalsifyProductRepository $salsify_product_repository,
    ProductHelper $product_data_helper,
    SalsifyImportMedia $salsify_import_media,
    SalsifyImportTaxonomyTerm $salsify_import_taxonomy
  ) {
    parent::__construct(
      $config_factory,
      $entity_type_manager,
      $cache_salsify,
      $salsify,
      $module_handler
    );
    $this->salsifyProductRepository = $salsify_product_repository;
    $this->productDataHelper = $product_data_helper;
    $this->salsifyImportMedia = $salsify_import_media;
    $this->salsifyImportTaxonomy = $salsify_import_taxonomy;
  }

  /**
   * A function to import Salsify data as nodes in Drupal.
   *
   * @param array $product_data
   *   The Salsify individual product data to process.
   * @param bool $force_update
   *   If set to TRUE, the updated date highwater mark will be ignored.
   * @param string $content_type
   *   Content type.
   *
   * @return array
   *   Result status of processing (not updated, updated, or created)
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function processSalsifyItem(
    array $product_data,
    $force_update = FALSE,
    $content_type = ProductHelper::PRODUCT_CONTENT_TYPE
  ) {
    $process_result = [
      'import_result' => static::PROCESS_RESULT_NOT_UPDATED,
      'validation_errors' => [],
    ];
    $entity_type = \Drupal::config('salsify_integration.settings')
      ->get('entity_type');
    $entity_bundle = $content_type;

    // Store this to send through to hook_salsify_node_presave_alter().
    $original_product_data = $product_data;

    $entity = static::getEntity(
      $entity_type,
      $entity_bundle,
      $product_data,
      $force_update,
    $process_result
    );
    if (!$entity) {
      return $process_result;
    }

    static::populateEntityByData(
      $entity,
      $product_data,
      $process_result
    );

    // Update parent entities if product or multipack was created earlier.
    \Drupal::service('salsify_integration.salsify_product_repository')
      ->updateParentEntities($product_data, $entity);

    // Allow users to alter the node just before it's saved.
    \Drupal::service('module_handler')
      ->alter(['salsify_entity_presave'], $entity, $original_product_data);

    // Set status to draft for generated product based on nutrition fields.
    if (isset($product_data['CMS: not publish']) && $product_data['CMS: not publish']) {
      // Do not index products generated for Multipack.
      static::updateSitemapIndexPropertiesForEntity($entity);
      $entity->set('rh_action', 'page_not_found');
      $entity->set('field_product_generated', TRUE);
    }
    else {
      $entity->set('rh_action', 'bundle_default');
      static::updateSitemapIndexPropertiesForEntity($entity, TRUE);
    }
    $entity->save();

    return $process_result;
  }

  /**
   * Update sitemap index for the entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The content entity to update.
   * @param bool $enable
   *   Index enabling status flag.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private static function updateSitemapIndexPropertiesForEntity(EntityInterface &$entity, bool $enable = FALSE) {
    if (\Drupal::service('module_handler')->moduleExists('simple_sitemap')) {
      /** @var \Drupal\simple_sitemap\Simplesitemap $generator */
      $generator = \Drupal::service('simple_sitemap.generator');
      $settings = [
        'index' => $enable,
        'priority' => '0.5',
        'changefreq' => 'daily',
        'include_images' => FALSE,
      ];
      $generator->setVariants('default');
      $generator->setEntityInstanceSettings($entity->getEntityTypeId(), $entity->id(), $settings);
    }
  }

  /**
   * Get product entity or generate new one.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $entity_bundle
   *   Entity bundle.
   * @param array $product_data
   *   Product data.
   * @param bool $force_update
   *   Force update flag.
   * @param array $process_result
   *   Result array.
   *
   * @return \Drupal\Core\Entity\EntityInterface|false
   *   Entity or false.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function getEntity(
    string $entity_type,
    string $entity_bundle,
    array $product_data,
    bool $force_update,
    array &$process_result
  ) {

    $entityTypeManager = \Drupal::entityTypeManager();
    // Lookup any existing entities in order to overwrite their contents.
    $results = $entityTypeManager->getStorage($entity_type)
      ->getQuery()
      ->condition('salsify_id', $product_data['salsify:id'])
      ->execute();

    // Load the existing entity or generate a new one.
    $title = $product_data['CMS: Product Name'] ?? $product_data['salsify:id'];
    $moderation_state = 'published';
    if ($results) {
      $entity_id = array_values($results)[0];
      $entity = $entityTypeManager->getStorage($entity_type)->load($entity_id);
      // If the model in Salsify hasn't been updated since the last time it was
      // imported, then skip it. If it was, or if an update is being forced,
      // then update salsify_updated and pass it along for further processing.
      $salsify_updated = strtotime($product_data['salsify:updated_at']);
      if ($force_update || $entity->salsify_updated->isEmpty() || $salsify_updated > $entity->salsify_updated->value) {
        $title = static::getTitle($product_data, $entity);
        $entity->set('salsify_updated', $salsify_updated);
        $process_result['import_result'] = static::PROCESS_RESULT_UPDATED;
        $entity->set('title', $title);
        $entity->set('moderation_state', $moderation_state);
      }
      else {
        return FALSE;
      }
    }
    else {
      // Allow users to alter the title set when a node is created by invoking
      // hook_salsify_process_node_title_alter().
      \Drupal::service('module_handler')
        ->alter('salsify_process_node_title', $title, $product_data);
      $entity_definition = $entityTypeManager->getDefinition($entity_type);
      $entity_keys = $entity_definition->getKeys();
      $entity_values = [
        $entity_keys['label'] => $title,
        $entity_keys['bundle'] => $entity_bundle,
        'salsify_updated' => strtotime($product_data['salsify:updated_at']),
        'salsify_id' => $product_data['salsify:id'],
      ];
      if (isset($entity_keys['created'])) {
        $entity_values['created'] = strtotime($product_data['salsify:created_at']);
      }
      if (isset($entity_keys['changed'])) {
        $entity_values['changed'] = strtotime($product_data['salsify:updated_at']);
      }
      if (isset($entity_keys['status'])) {
        $entity_values['status'] = 1;
      }
      $entity = $entityTypeManager->getStorage($entity_type)->create($entity_values);
      $entity->getTypedData();
      $entity->save();
      $entity->set('moderation_state', $moderation_state);
      $process_result['import_result'] = static::PROCESS_RESULT_CREATED;
    }

    return $entity;
  }

  /**
   * Get title for update.
   *
   * @param array $product_data
   *   Product data array.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Product entity.
   *
   * @return string
   *   Title for the entity.
   */
  public static function getTitle(array $product_data, EntityInterface $entity) {
    $original_title = $entity->label();
    $salsify_title = $product_data['CMS: Product Name'] ?? $product_data['salsify:id'];

    return ($original_title != $salsify_title &&
      isset($product_data['CMS: multipack generated']) &&
      $product_data['CMS: multipack generated'])
      ? $original_title
      : $salsify_title;
  }

  /**
   * Populate entity by salsify data.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity object.
   * @param array $product_data
   *   Salsify data.
   * @param array $process_result
   *   Process result data.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function populateEntityByData(
    EntityInterface &$entity,
    array $product_data,
    array &$process_result
  ) {
    $entity_type = $entity->getEntityType()->id();
    $entity_bundle = $entity->bundle();
    // Load field mappings keyed by Salsify ID.
    $salsify_field_mapping = SalsifyFields::getFieldMappings(
      [
        'entity_type' => $entity_type,
        'bundle' => $entity_bundle,
      ],
      'salsify_id'
    );

    // Load the configurable fields for this content type.
    $filtered_fields = Salsify::getContentTypeFields($entity_type, $entity_bundle);
    // Unset the system values since they've already been processed.
    unset($salsify_field_mapping['salsify_updated']);
    unset($salsify_field_mapping['salsify_id']);

    static::clearEntity($entity, $product_data);

    foreach ($salsify_field_mapping as $field) {
      if (isset($product_data[$field['salsify_id']]) &&
        \Drupal::service('salsify_integration.product_data_helper')
          ->validateDataRecord($product_data, $field)) {

        $options = SalsifyImport::getFieldOptions((array) $field, $product_data[$field['salsify_id']]);
        /** @var \Drupal\field\Entity\FieldConfig $field_config */
        $field_config = $filtered_fields[$field['field_name']];

        // Run all digital assets through additional processing as media
        // entities if the Media entity module is enabled.
        static::populateMediaOption(
          $field,
          $product_data,
          $options
        );

        static::populateDataOptions(
          $field,
          $product_data,
          $field_config,
          $options,
          $entity
        );
      }
      elseif (!\Drupal::service('salsify_integration.product_data_helper')
        ->validateDataRecord($product_data, $field)) {
        $process_result['validation_errors'][] = $product_data['GTIN'] .
          ', ' . $field['salsify_id'] . ', ' . $field['salsify_data_type'];
      }
    }
  }

  /**
   * Clear all fields data related to salsify mapping before populating.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   * @param array $product_data
   *   Product data.
   */
  public static function clearEntity(EntityInterface &$entity, array $product_data) {
    if ($entity->bundle() == ProductHelper::PRODUCT_VARIANT_CONTENT_TYPE) {
      $fields = array_keys(SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT_VARIANT);
    }
    else {
      $fields = array_keys(SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT);
    }

    foreach ($fields as $field_name) {
      if (!isset($product_data['CMS: multipack generated']) ||
        !$product_data['CMS: multipack generated'] ||
        ($product_data['CMS: multipack generated'] && !in_array($field_name, self::CONSTANT_MULRIPACK_GENERATED_FIELDS))
      ) {
        if ($entity->bundle() !== 'product' && $field_name === 'field_product_generated') {
          continue;
        }
        $entity->set($field_name, NULL);
      }
    }
  }

  /**
   * Populate options for media fields.
   *
   * @param array $field
   *   Mapping field data.
   * @param array $product_data
   *   Product data.
   * @param mixed $options
   *   Data of option.
   */
  public static function populateMediaOption(
    array $field,
    array $product_data,
    &$options
  ) {
    if (\Drupal::service('module_handler')->moduleExists('media')) {
      if ($field['salsify_data_type'] == 'digital_asset') {
        /** @var \Drupal\media_entity\Entity\Media $media */
        $media_entities = \Drupal::service('salsify_integration.salsify_import_media')
          ->processSalsifyMediaItem($field, $product_data);
        $options = [];
        if ($media_entities) {
          foreach ($media_entities as $media) {
            $options[] = [
              'target_id' => $media->id(),
            ];
          }
        }
      }
    }
  }

  /**
   * Populate options by data.
   *
   * @param array $field
   *   Field data array.
   * @param array $product_data
   *   Product data array.
   * @param \Drupal\field\Entity\FieldConfig $field_config
   *   Field config object.
   * @param mixed $options
   *   Option's data.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function populateDataOptions(
    array $field,
    array $product_data,
    FieldConfig $field_config,
    &$options,
    EntityInterface &$entity
  ) {
    if ($field_config) {
      // Invoke hook_salsify_process_field_alter() and
      // hook_salsify_process_field_FIELD_TYPE_alter() implementations.
      $hooks = [
        'salsify_process_field',
        'salsify_process_field_' . $field_config->getType(),
      ];
      $context = [
        'field_config' => $field_config,
        'product_data' => $product_data,
        'field_map' => $field,
      ];

      \Drupal::service('module_handler')
        ->alter($hooks, $options, $context);

      // Truncate strings if they are too long for the string field they
      // are mapped against.
      if ($field_config->getType() == 'string') {
        static::populateStringOption(
          $field_config,
          $options
        );
      }
      elseif ($field_config->getType() == 'list_string' && $field['salsify_data_type'] == 'string') {
        static::populateListStringOption(
          $field_config,
          $options
        );
      }
      // For taxonomy term mapping, add processing for the terms coming in
      // from Salsify.
      elseif ($field_config->getType() == 'entity_reference' && $field['salsify_data_type'] == 'enumerated') {
        $settings = $field_config->getSetting('handler_settings');
        $vid = (!empty($settings['target_bundles']))
          ? reset($settings['target_bundles'])
          : NULL;

        static::populateTermOption(
          $product_data,
          $field,
          $options,
          $vid
        );
      }
      elseif ($field_config->getType() == 'entity_reference' && $field['salsify_data_type'] == 'entity_ref') {
        static::populateEntityRefOption(
          $product_data,
          $field,
          $field_config,
          $options
        );
      }
      elseif ($field_config->getType() == 'metatag' && $field['salsify_data_type'] == 'complex') {
        static::populateMetaDataOption(
          $product_data,
          $field,
          $entity,
          $options
        );
      }

      $entity->set($field['field_name'], $options);
    }
  }

  /**
   * Populate option for string field.
   *
   * @param \Drupal\field\Entity\FieldConfig $field_config
   *   Field config object.
   * @param mixed $options
   *   Option's array.
   */
  public static function populateStringOption(
    FieldConfig $field_config,
    &$options
  ) {
    $field_storage = $field_config->getFieldStorageDefinition();
    $max_length = $field_storage->getSetting('max_length');
    $options = (is_array($options)) ? reset($options) : $options;

    if (strlen($options) > $max_length) {
      $options = substr($options, 0, $max_length);
    }
  }

  /**
   * Populate option for list-string field.
   *
   * @param \Drupal\field\Entity\FieldConfig $field_config
   *   Field config object.
   * @param mixed $options
   *   Option's array.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function populateListStringOption(
    FieldConfig $field_config,
    &$options
  ) {
    $field_storage = $field_config->getFieldStorageDefinition();
    $allowed_values = $field_storage->getSetting('allowed_values');
    if (!isset($allowed_values[$options])) {
      $allowed_values[$options] = $options;
      $field_storage->setSetting('allowed_values', $allowed_values);
      $field_storage->save();
    }
  }

  /**
   * Populate option for term related fields.
   *
   * @param array $product_data
   *   Product data array.
   * @param array $field
   *   Field data array.
   * @param mixed $options
   *   Option's array.
   * @param mixed $vid
   *   Vocabulary id.
   */
  public static function populateTermOption(
    array $product_data,
    array $field,
    &$options,
    $vid
  ) {
    $salsify_values = is_array($product_data[$field['salsify_id']]) ? $product_data[$field['salsify_id']] : [$product_data[$field['salsify_id']]];
    $term_entities = \Drupal::service('salsify_integration.salsify_import_taxonomy')
      ->getTaxonomyTerms('salsify_id', $salsify_values, $vid);
    if ($term_entities) {
      $options = [];
      /** @var \Drupal\taxonomy\Entity\Term $term_entity */
      foreach ($term_entities as $term_entity) {
        $options[] = ['target_id' => $term_entity->id()];
      }
    }
  }

  /**
   * Populate option for entity ref field.
   *
   * @param array $product_data
   *   Product data array.
   * @param array $field
   *   Field data array.
   * @param \Drupal\field\Entity\FieldConfig $field_config
   *   Field config object.
   * @param mixed $options
   *   Option's data.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function populateEntityRefOption(
    array $product_data,
    array $field,
    FieldConfig $field_config,
    &$options
  ) {
    $handler_settings = $field_config->getSetting('handler_settings');
    $target_bundles = $handler_settings['target_bundles'] ?? [];

    $entityTypeManager = \Drupal::entityTypeManager();
    $entity_query = $entityTypeManager->getStorage('node')
      ->getQuery();
    $child_entities = $entity_query->condition(
      'salsify_id',
      $product_data[$field['salsify_id']],
      'IN'
    )
      ->condition('type', $target_bundles, 'IN')
      ->execute();

    if ($child_entities) {
      $options = [];
      foreach ($child_entities as $child_entity) {
        $options[] = ['target_id' => $child_entity];
      }
    }
  }

  /**
   * Populate option for meta data field.
   *
   * @param array $product_data
   *   Product data array.
   * @param array $field
   *   Field data array.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Field config object.
   * @param mixed $options
   *   Option's data.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function populateMetaDataOption(
    array $product_data,
    array $field,
    EntityInterface $entity,
    &$options
  ) {

    /** @var \Drupal\Core\Entity\FieldableEntityInterface $entity */
    $meta_tags = $entity->get($field['field_name'])->value;
    $meta_tags_value = [];
    if (isset($meta_tags)) {
      $meta_tags_value = unserialize($meta_tags, ["allowed_classes" => FALSE]);
    }
    $meta_tags_value['description'] = $product_data['CMS: Meta Description'] ?? NULL;
    $meta_tags_value['keywords'] = $product_data['CMS: Keywords'] ?? NULL;
    $options = serialize($meta_tags_value);
  }

}
