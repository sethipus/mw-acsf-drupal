<?php

namespace Drupal\salsify_integration;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class SalsifyImport.
 *
 * The main class used to perform content imports. Imports are trigger either
 * through queues during a cron run or via the configuration page.
 *
 * @package Drupal\salsify_integration
 */
class SalsifyImport {

  public const PROCESS_RESULT_NOT_UPDATED = 'not_updated';

  public const PROCESS_RESULT_UPDATED = 'updated';

  public const PROCESS_RESULT_CREATED = 'created';

  /**
   * The cache object associated with the specified bin.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The configFactory interface.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Salsify config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Salsify core service.
   *
   * @var \Drupal\salsify_integration\Salsify
   */
  protected $salsify;

  /**
   * The module handler interface.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a \Drupal\salsify_integration\Salsify object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory interface.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_salsify
   *   The cache object associated with the Salsify bin.
   * @param \Drupal\salsify_integration\Salsify $salsify
   *   The Salsify core service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler interface.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    CacheBackendInterface $cache_salsify,
    Salsify $salsify,
    ModuleHandlerInterface $module_handler
  ) {
    $this->cache = $cache_salsify;
    $this->configFactory = $config_factory;
    $this->config = $this->configFactory->get('salsify_integration.settings');
    $this->entityTypeManager = $entity_type_manager;
    $this->salsify = $salsify;
    $this->moduleHandler = $module_handler;
  }

  /**
   * A function to import Salsify data as entities in Drupal.
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
   */
  public static function processSalsifyItem(
    array $product_data,
    $force_update = FALSE,
    $content_type = ProductHelper::PRODUCT_CONTENT_TYPE
  ) {
    return [
      'import_result' => static::PROCESS_RESULT_NOT_UPDATED,
      'validation_errors' => [],
    ];
  }

  /**
   * Helper function to return a properly formatting set of field options.
   *
   * @param array $field
   *   The field mapping array.
   * @param array|string $field_data
   *   The Salsify field data from the queue.
   *
   * @return array|string
   *   The options array or string values.
   */
  public static function getFieldOptions(array $field, $field_data) {
    $options = $field_data;
    switch ($field['salsify_data_type']) {
      case 'link':
        $options = [
          'uri' => $field_data,
          'title' => '',
          'options' => [],
        ];
        break;

      case 'date':
        $options = [
          'value' => $field_data,
        ];
        break;

      case 'enumerated':
        if (!is_array($field_data)) {
          $options = [$field_data];
        }
        break;

      case 'rich_text':
        $options = [
          'value' => $field_data,
          'format' => 'full_html',
        ];
        break;

      default:
        break;
    }
    return $options;
  }

}
