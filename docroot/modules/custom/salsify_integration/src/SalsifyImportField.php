<?php

namespace Drupal\salsify_integration;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The module handler interface.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * The salsify product repository.
   *
   * @var \Drupal\salsify_integration\SalsifyProductRepository
   */
  private $salsifyProductRepository;

  /**
   * SalsifyImportField constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query interface.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager interface.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_salsify
   *   The Salsify cache interface.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler interface.
   * @param \Drupal\salsify_integration\SalsifyProductRepository $salsify_product_repository
   *   The salsify product repository.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    QueryFactory $entity_query,
    EntityTypeManagerInterface $entity_type_manager,
    CacheBackendInterface $cache_salsify,
    ModuleHandlerInterface $module_handler,
    SalsifyProductRepository $salsify_product_repository
  ) {
    parent::__construct($config_factory, $entity_query, $entity_type_manager, $cache_salsify);
    $this->moduleHandler = $module_handler;
    $this->salsifyProductRepository = $salsify_product_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity.query'),
      $container->get('entity_type.manager'),
      $container->get('cache.default'),
      $container->get('module_handler'),
      $container->get('salsify_integration.salsify_product_repository')
    );
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
   * @return string
   *   Result status of processing (not updated, updated, or created)
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processSalsifyItem(
    array $product_data,
    $force_update = FALSE,
    $content_type = ProductHelper::PRODUCT_CONTENT_TYPE
  ) {
    $process_result = self::PROCESS_RESULT_NOT_UPDATED;
    $entity_type = $this->config->get('entity_type');
    $entity_bundle = $content_type;
    // E$entity_bundle = $this->config->get('bundle');.
    // Store this to send through to hook_salsify_node_presave_alter().
    $original_product_data = $product_data;

    // Load field mappings keyed by Salsify ID.
    $salsify_field_mapping = SalsifyFields::getFieldMappings(
      [
        'entity_type' => $entity_type,
        'bundle' => $entity_bundle,
      ],
      'salsify_id'
    );

    // Lookup any existing entities in order to overwrite their contents.
    $results = $this->entityQuery->get($entity_type)
      ->condition('salsify_id', $product_data['salsify:id'])
      ->execute();

    // Load the existing entity or generate a new one.
    if ($results) {
      $entity_id = array_values($results)[0];
      $entity = $this->entityTypeManager->getStorage($entity_type)->load($entity_id);
      // If the model in Salsify hasn't been updated since the last time it was
      // imported, then skip it. If it was, or if an update is being forced,
      // then update salsify_updated and pass it along for further processing.
      $salsify_updated = strtotime($product_data['salsify:updated_at']);
      if ($force_update || $entity->salsify_updated->isEmpty() || $salsify_updated > $entity->salsify_updated->value) {
        $entity->set('salsify_updated', $salsify_updated);
        $process_result = self::PROCESS_RESULT_UPDATED;
      }
      else {
        return $process_result;
      }
    }
    else {
      $title = $product_data['salsify:id'];
      // Allow users to alter the title set when a node is created by invoking
      // hook_salsify_process_node_title_alter().
      $this->moduleHandler->alter('salsify_process_node_title', $title, $product_data);
      $entity_definition = $this->entityTypeManager->getDefinition($entity_type);
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
      $entity = $this->entityTypeManager->getStorage($entity_type)->create($entity_values);
      $entity->getTypedData();
      $entity->save();
      $process_result = self::PROCESS_RESULT_CREATED;
    }

    // Load the configurable fields for this content type.
    $filtered_fields = Salsify::getContentTypeFields($entity_type, $entity_bundle);
    // Unset the system values since they've already been processed.
    unset($salsify_field_mapping['salsify_updated']);
    unset($salsify_field_mapping['salsify_id']);

    // Set the field data against the Salsify node. Remove the data from the
    // serialized list to prevent redundancy.
    foreach ($salsify_field_mapping as $field) {
      if (isset($product_data[$field['salsify_id']])) {
        $options = $this->getFieldOptions((array) $field, $product_data[$field['salsify_id']]);
        /* @var \Drupal\field\Entity\FieldConfig $field_config */
        $field_config = $filtered_fields[$field['field_name']];

        // Run all digital assets through additional processing as media
        // entities if the Media entity module is enabled.
        if ($this->moduleHandler->moduleExists('media')) {
          if ($field['salsify_data_type'] == 'digital_asset') {
            $media_import = SalsifyImportMedia::create(\Drupal::getContainer());
            /* @var \Drupal\media_entity\Entity\Media $media */
            $media_entities = $media_import->processSalsifyMediaItem($field, $product_data);
            if ($media_entities) {
              $options = [];
              foreach ($media_entities as $media) {
                $options[] = [
                  'target_id' => $media->id(),
                ];
              }
            }
          }
        }

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

          $this->moduleHandler->alter($hooks, $options, $context);

          // Truncate strings if they are too long for the string field they
          // are mapped against.
          if ($field_config->getType() == 'string') {
            $field_storage = $field_config->getFieldStorageDefinition();
            $max_length = $field_storage->getSetting('max_length');
            if (strlen($options) > $max_length) {
              $options = substr($options, 0, $max_length);
            }
          }
          // For taxonomy term mapping, add processing for the terms coming in
          // from Salsify.
          elseif ($field_config->getType() == 'entity_reference' && $field['salsify_data_type'] == 'enumerated') {
            $term_import = SalsifyImportTaxonomyTerm::create(\Drupal::getContainer());
            $salsify_values = is_array($product_data[$field['salsify_id']]) ? $product_data[$field['salsify_id']] : [$product_data[$field['salsify_id']]];
            $term_entities = $term_import->getTaxonomyTerms('salsify_id', $salsify_values);
            if ($term_entities) {
              $options = [];
              /* @var \Drupal\taxonomy\Entity\Term $term_entity */
              foreach ($term_entities as $term_entity) {
                $options[] = ['target_id' => $term_entity->id()];
              }
            }
          }
          elseif ($field_config->getType() == 'entity_reference' && $field['salsify_data_type'] == 'entity_ref') {

            $handler_settings = $field_config->getSetting('handler_settings');
            $target_bundles = $handler_settings['target_bundles'] ?? [];

            $entity_query = $this->entityTypeManager->getStorage('node')
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

          $entity->set($field['field_name'], $options);
        }
      }
    }

    // Update parent entities if product or multipack was created earlier.
    $this->salsifyProductRepository
      ->updateParentEntities($product_data, $entity);

    // Allow users to alter the node just before it's saved.
    $this->moduleHandler->alter(['salsify_entity_presave'], $entity, $original_product_data);

    $entity->save();

    return $process_result;
  }

}
