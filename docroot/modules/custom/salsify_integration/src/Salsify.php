<?php

namespace Drupal\salsify_integration;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Class Salsify - salsify core logic of import process.
 *
 * @package Drupal\salsify_integration
 */
class Salsify {

  use StringTranslationTrait;

  /**
   * Static value for 'updatad_at' attribute (01.01.2019).
   */
  public const ATTRIBUTE_UPDATED_AT = 1546300800;

  /**
   * Static value for 'created' attribute (01.01.2019).
   */
  public const FIELD_MAP_CREATED = 1546300800;

  /**
   * Static value for 'changed' attribute (01.01.2019).
   */
  public const FIELD_MAP_CHANGED = self::FIELD_MAP_CREATED + 1;

  public const AUTH_METHOD_TOKEN = 'token';

  public const AUTH_METHOD_SECRET = 'client_secret';

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
   * The Entity Field Manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The logger interface.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The logger interface.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * The Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The Mulesoft connector service.
   *
   * @var \Drupal\salsify_integration\MulesoftConnector
   */
  protected $mulesoftConnector;

  /**
   * The HTTP client service.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * Constructs a \Drupal\salsify_integration\Salsify object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger factory interface.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory interface.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_salsify
   *   The cache object associated with the Salsify bin.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   Queue factory service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The Queue factory service.
   * @param \Drupal\salsify_integration\MulesoftConnector $mulesoft_connector
   *   The Mulesoft connector.
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
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
    ClientInterface $client
  ) {
    $this->logger = $logger->get('salsify_integration');
    $this->configFactory = $config_factory;
    $this->config = $this->configFactory->get('salsify_integration.settings');
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->cache = $cache_salsify;
    $this->queueFactory = $queue_factory;
    $this->moduleHandler = $module_handler;
    $this->mulesoftConnector = $mulesoft_connector;
    $this->client = $client;
  }

  /**
   * Get the URL to the Salsify product channel.
   *
   * @return string
   *   A fully-qualified URL string.
   */
  protected function getUrl() {
    return $this->config->get('product_feed_url');
  }

  /**
   * Get the Salsify user account access token to use with this integration.
   *
   * @return string
   *   The access token string.
   */
  protected function getAccessToken() {
    return $this->config->get('access_token');
  }

  /**
   * Get the Salsify user account client id to use with this integration.
   *
   * @return string
   *   The client id string.
   */
  protected function getClientId() {
    return $this->config->get('client_id');
  }

  /**
   * Get the Salsify user account client secret to use with this integration.
   *
   * @return string
   *   The client secret string.
   */
  protected function getClientSecret() {
    return $this->config->get('client_secret');
  }

  /**
   * Get auth headers depending on current auth method.
   *
   * @return array
   *   Headers.
   */
  protected function getAuthHeaders() {
    $auth_method = $this->config->get('auth_method');
    $headers = [];

    if ($auth_method == static::AUTH_METHOD_TOKEN) {
      $headers = [
        'Authorization' => 'Bearer ' . $this->getAccessToken(),
      ];
    }
    if ($auth_method == static::AUTH_METHOD_SECRET) {
      $headers = [
        'client_id' => $this->getClientId(),
        'client_secret' => $this->getClientSecret(),
      ];
    }
    return $headers;
  }

  /**
   * Utility function to load product data from Salsify for further processing.
   *
   * @return array
   *   An array of raw, unprocessed product data. Empty if an error was found.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getRawData() {
    $endpoint = $this->getUrl();
    try {
      // Access the channel URL to fetch the newest product feed URL.
      $generate_product_feed = $this->client->get($endpoint, [
        'headers' => $this->getAuthHeaders(),
        'timeout' => 60000,
      ]);

      /** @var \GuzzleHttp\Psr7\Response $generate_product_feed */
      $response = $generate_product_feed->getBody()->__toString();
      return $this->mulesoftConnector->transformData($response);
    }
    catch (RequestException $e) {
      $this->logger->notice(
        'Could not make GET request to %endpoint because of error "%error".',
        ['%endpoint' => $endpoint, '%error' => $e->getMessage()]
      );
      throw new MissingDataException(__CLASS__ . ': Could not make GET request to ' . $endpoint . ' because of error "' . $e->getMessage() . '".');
    }
  }

  /**
   * Utility function to load and process product data from Salsify.
   *
   * @return array
   *   An array of product data.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getProductData() {
    try {
      $raw_data = $this->getRawData();
      $product_data = [];

      if (isset($raw_data['digital_assets'])) {
        // Rekey the Digital Assets by their Salsify ID to make looking them
        // up in later calls easier.
        $raw_data['digital_assets'] = $this->rekeyArray($raw_data['digital_assets'], 'salsify:id');
      }

      // Organize the fields and options (for enumerated fields) by salsify:id.
      foreach ($raw_data['attributes'] as $attribute) {
        $product_data['fields'][$attribute['salsify:id']] = $attribute;
        $product_data['fields'][$attribute['salsify:id']]['date_updated'] = $attribute['salsify:updated_at'];
        foreach ($product_data['fields'][$attribute['salsify:id']]['salsify:entity_types'] as $entity_types) {
          // $product_data['entity_field_mapping'][$entity_types][] =
          // $attribute['salsify:system_id'];
          $product_data['entity_field_mapping'][$entity_types][] = NULL;
        }
      }
      foreach ($raw_data['attribute_values'] as $value) {
        $product_data['fields'][$value['salsify:attribute_id']]['values'][$value['salsify:id']] = $value;
        $date_updated = $value['salsify:updated_at'];
        if ($date_updated > $product_data['fields'][$value['salsify:attribute_id']]['date_updated']) {
          $product_data['fields'][$value['salsify:attribute_id']]['date_updated'] = $date_updated;
        }
      }

      // Add in the Salsify id from the imported content as a special field.
      // This will allow for tracking data that has already been imported into
      // the system without making the user manage the ID field.
      $salsify_internal_fields = [];
      $salsify_internal_fields['salsify:id'] = [
        'salsify:id' => 'salsify:id',
        'salsify:system_id' => 'salsify:system_id',
        'salsify:name' => $this->t('Salsify Sync ID'),
        'salsify:data_type' => 'string',
        'salsify:created_at' => date('Y-m-d', time()),
        'date_updated' => time(),
      ];
      $salsify_internal_fields['salsify:updated_at'] = [
        'salsify:id' => 'salsify:updated_at',
        'salsify:system_id' => 'salsify:system_id',
        'salsify:name' => $this->t('Salsify Updated Date'),
        'salsify:data_type' => 'number',
        'salsify:created_at' => date('Y-m-d', time()),
        'date_updated' => time(),
      ];
      $product_data['fields'] = array_merge($product_data['fields'], $salsify_internal_fields);

      $new_product_data = $product_data + $raw_data;

      // Allow users to alter the product data from Salsify by invoking
      // hook_salsify_product_data_alter().
      $this->moduleHandler
        ->alter('salsify_product_data', $new_product_data);

      $output = '';
      foreach ($new_product_data['products'] as $product) {
        $output .= '---';
        $output .= "\n";
        foreach ($product as $key => $value) {
          $output .= "$key: " . (string) $value;
          $output .= "\n";
        }
        $output .= "\n";
      }

      $this->logger->notice('Salsify product data for product import: <pre>@fetched_data</pre>', ['@fetched_data' => $output]);

      // Add the newly updated product data into the site cache.
      $this->cache->set('salsify_import_product_data', $new_product_data);

      return $new_product_data;

    }
    catch (MissingDataException $e) {
      throw new MissingDataException(__CLASS__ . ': Unable to load Salsify product data. ' . $e->getMessage());
    }
  }

  /**
   * Utility function that retrieves the configured entity bundle value.
   *
   * @return string
   *   The content type to use for Salsify data.
   */
  protected function getEntityType() {
    return $this->config->get('entity_type');
  }

  /**
   * Utility function that retrieves the configured entity bundle value.
   *
   * @return string
   *   The content type to use for Salsify data.
   */
  protected function getEntityBundle() {
    return $this->config->get('bundle');
  }

  /**
   * Utility function to load a content types configurable fields.
   *
   * @param string $entity_type
   *   The type of entity to use to lookup fields.
   * @param string $entity_bundle
   *   The entity bundle to use for the Salsify integration.
   *
   * @return array
   *   An array of field objects.
   */
  public static function getContentTypeFields($entity_type, $entity_bundle) {
    $fields = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type, $entity_bundle);
    $filtered_fields = array_filter(
      $fields, function ($field_definition) {
        return $field_definition instanceof FieldConfig;
      }
    );
    return $filtered_fields;
  }

  /**
   * Utility function to return the list of Salsify field mappings.
   *
   * @param array $keys
   *   The keys in the mapping table to use for the returned associative array.
   * @param string $key_by
   *   The value to use when keying the associative array of results.
   *
   * @return mixed
   *   An array of configuration arrays.
   */
  public static function getFieldMappings(array $keys, $key_by = 'field_name') {
    if (isset($keys['method'])) {
      $methods = [
        $keys['method'],
      ];
    }
    else {
      $methods = [
        'manual',
        'dynamic',
      ];
    }
    $configs = [];
    foreach ($methods as $method) {
      $keys['method'] = $method;
      $config_prefix = static::getConfigName($keys);
      $configs += \Drupal::configFactory()->listAll($config_prefix);
    }
    $results = [];
    foreach ($configs as $config_name) {
      $config = \Drupal::config($config_name);

      $raw_data = $config->getRawData();
      if (
        isset($keys['bundle']) &&
        isset($raw_data['bundle']) &&
        $keys['bundle'] == $raw_data['bundle']
      ) {
        $results[$config->get($key_by)] = $config->getRawData();
      }
    }
    return $results;
  }

  /**
   * Utility function to create a new field mapping.
   *
   * @param array $values
   *   An array of field mapping values to insert into the database.
   */
  public static function createFieldMapping(array $values) {
    // Allow users to alter the field mapping data by invoking
    // hook_salsify_field_mapping_alter().
    \Drupal::moduleHandler()->alter('salsify_field_mapping_create', $values);

    if ($values) {
      static::setConfig($values);
    }
  }

  /**
   * Utility function to update a field mapping.
   *
   * @param array $values
   *   The values to update in the matched row.
   */
  public static function updateFieldMapping(array $values) {
    // Allow users to alter the field mapping data by invoking
    // hook_salsify_field_mapping_alter().
    \Drupal::moduleHandler()->alter('salsify_field_mapping_update', $values);

    if ($values) {
      static::setConfig($values);
    }
  }

  /**
   * Utility function to remove a field mapping.
   *
   * @param array $keys
   *   The array of column name => value settings to use when matching the row.
   */
  public static function deleteFieldMapping(array $keys) {
    static::deleteConfig($keys);
  }

  /**
   * Utility function to create a config name string.
   *
   * @param array $values
   *   The array of keys to use to create the config name.
   *
   * @return string
   *   The config name to lookup.
   */
  public static function getConfigName(array $values) {
    $field_name = '';
    if (isset($values['field_name'])) {
      $field_name = '.' . $values['field_name'];
    }
    return 'salsify_integration.' . $values['method'] . '.' . $values['entity_type'] . '.' . $values['bundle'] . $field_name;
  }

  /**
   * Utility function to write configuration values for field mappings.
   *
   * @param array $values
   *   The values to write into the configuration element.
   */
  public static function setConfig(array $values) {
    $config_name = static::getConfigName($values);
    /** @var \Drupal\Core\Config\Config $config */
    $config = \Drupal::service('config.factory')->getEditable($config_name);
    foreach ($values as $label => $value) {
      $config->set($label, $value);
    }
    $config->save();
  }

  /**
   * Utility function to delete configuration values for field mappings.
   *
   * @param array $values
   *   The values used to lookup the  configuration element.
   */
  public static function deleteConfig(array $values) {
    $config_name = static::getConfigName($values);
    /** @var \Drupal\Core\Config\Config $config */
    $config = \Drupal::service('config.factory')->getEditable($config_name);
    $config->delete();
  }

  /**
   * Utility function to update a dynamic field's settings.
   *
   * @param array $salsify_field
   *   The array of field data from Salsify.
   * @param \Drupal\field\Entity\FieldConfig $field
   *   The field configuration object from the content type.
   */
  protected function updateDynamicField(array $salsify_field, FieldConfig $field) {}

  /**
   * Utility function to remove a Drupal field.
   *
   * @param \Drupal\field\Entity\FieldConfig $field
   *   The field configuration object from the content type.
   */
  protected function deleteDynamicField(FieldConfig $field) {
    try {
      // Delete the field from Drupal since it is no longer in use by Salisfy.
      $field->delete();
    }
    catch (DatabaseExceptionWrapper $e) {
      $this->logger->notice('Could not delete field. Error: "%error".', ['%error' => $e->getMessage()]);
    }
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
  public static function createFieldViewDisplay($entity_type, $entity_bundle, $field_name, $view_mode) {}

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
  public static function createFieldFormDisplay($entity_type, $entity_bundle, $field_name, $salsify_type) {}

  /**
   * Utility function to set the allowed values list from Salsify for a field.
   *
   * @param array $salsify_data
   *   The field level data from Salsify augmented with allowed values.
   */
  protected static function setFieldOptions(array $salsify_data) {
    $config = \Drupal::service('config.factory')
      ->getEditable('salsify_integration.field_options');
    $options = [];
    if (isset($salsify_data['values'])) {
      foreach ($salsify_data['values'] as $value) {
        // Filter out everything but alphanumeric characters, dashes, and spaces
        // to prevent errors when setting the field options.
        $salsify_id = preg_replace('/[^[A-Za-z0-9_]-\s]/', '', $value['salsify:id']);
        $options[$salsify_id] = $value['salsify:name'];
      }
      $config->set($salsify_data['salsify:system_id'], $options);
      $config->save();
    }
  }

  /**
   * Utility function to set the allowed values list from Salsify for a field.
   *
   * @param string $salsify_system_id
   *   The Salsify system id to remove from the options configuration.
   */
  public function removeFieldOptions($salsify_system_id) {
    $config = $this->configFactory->getEditable('salsify_integration.field_options');
    if ($config->get($salsify_system_id)) {
      $config->clear($salsify_system_id);
      $config->save();
    }
  }

  /**
   * Utility function to return an array of Salsify and Drupal system values.
   *
   * @return array
   *   The array of Salsify => Drupal field names.
   */
  protected static function getSystemFieldNames() {
    return [
      'salsify:id' => 'salsify_id',
      'salsify:updated_at' => 'salsify_updated',
    ];
  }

  /**
   * Utility function to rekey a nested array using one of its subvalues.
   *
   * @param array $array
   *   An array of arrays.
   * @param string $key
   *   The key in the subarray to use as the key on $array.
   *
   * @return array|bool
   *   The newly keyed array or FALSE if the key wasn't found.
   */
  public static function rekeyArray(array $array, $key) {
    $new_array = [];
    foreach ($array as $entry) {
      if (is_array($entry) && isset($entry[$key])) {
        $new_array[$entry[$key]] = $entry;
      }
      else {
        break;
      }
    }

    return $new_array;

  }

}
