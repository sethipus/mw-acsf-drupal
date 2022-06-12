<?php

namespace Drupal\salsify_integration;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\Token;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\file\FileRepositoryInterface;

/**
 * Class SalsifyImportMedia.
 *
 * The main class used to perform content imports. Imports are trigger either
 * through queues during a cron run or via the configuration page.
 *
 * @package Drupal\salsify_integration
 */
class SalsifyImportMedia extends SalsifyImport {

  use StringTranslationTrait;

  /**
   * The field storage config handler.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $fieldStorageConfig;

  /**
   * The field config handler.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $fieldConfig;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  private $token;

  /**
   * The File system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * The File repository service.
   *
   * @var Drupal\file\FileRepositoryInterface
   */
  private $fileRepository;

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
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The File system service.
   * @param \Drupal\Core\File\FileRepositoryInterface $file_repository
   *   The File system repository service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    CacheBackendInterface $cache_salsify,
    Salsify $salsify,
    ModuleHandlerInterface $module_handler,
    Token $token,
    FileSystemInterface $file_system,
    FileRepositoryInterface $file_repository
  ) {
    parent::__construct(
      $config_factory,
      $entity_type_manager,
      $cache_salsify,
      $salsify,
      $module_handler,
      $file_repository
    );
    $this->token = $token;
    $this->fileSystem = $file_system;
    $this->fileRepository = $file_repository;
  }

  /**
   * A function to import Salsify data as nodes in Drupal.
   *
   * @param array $field
   *   The Salsify to Drupal field mapping entry.
   * @param array $product_data
   *   The Salsify individual product data to process.
   *
   * @return array|bool
   *   An array of media entities or FALSE.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function processSalsifyMediaItem(array $field, array $product_data) {
    $salsify_media_ids = $this->getSalsifyMediaIds($product_data, $field);

    $media_entities = $this->getMediaEntitiesByAssetIds(
      $product_data,
      $salsify_media_ids
    );

    if ($media_entities) {
      return $media_entities;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Get salsify media ids.
   *
   * @param array $product_data
   *   Product data.
   * @param array $field
   *   Field data.
   *
   * @return array|mixed
   *   Salsify media ids.
   */
  private function getSalsifyMediaIds(array $product_data, array $field) {
    if (!is_array($product_data[$field['salsify_id']])) {
      $salsify_media_ids = [$product_data[$field['salsify_id']]];
    }
    else {
      $salsify_media_ids = $product_data[$field['salsify_id']];
    }
    return $salsify_media_ids;
  }

  /**
   * Get media entities by salsify asset ids.
   *
   * @param array $product_data
   *   Product data array.
   * @param mixed $salsify_media_ids
   *   Salsify media ids.
   *
   * @return array
   *   Media entities.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function getMediaEntitiesByAssetIds(
    array $product_data,
    $salsify_media_ids
  ) {
    // Set the default fields to use to lookup any existing media that was
    // previously imported.
    $field_name = 'salsify_id';
    $field_id_storage = FieldStorageConfig::loadByName('media', $field_name);

    $media_entities = [];

    foreach ($salsify_media_ids as $salsify_media_id) {
      $product_data['salsify:digital_assets'] = Salsify::rekeyArray($product_data['salsify:digital_assets'], 'salsify:id');
      $asset_data = $product_data['salsify:digital_assets'][$salsify_media_id];

      // Only update or create media in the website that has been uploaded
      // successfully into Salsify.
      if ($asset_data['salsify:status'] <> 'failed') {

        $media = $this->getMediaByAssetId(
          $asset_data,
          $field_id_storage,
          $field_name
        );

        $updated = strtotime($asset_data['salsify:updated_at']);
        // If the file hasn't been changed in Salsify, then stop processing.
        if ($media instanceof EntityInterface && $updated <= $media->getChangedTime()) {
          $media_entities[] = $media;
          unset($media);
          continue;
        }

        $file = $this->getFileByAssetData($asset_data);

        // If the file was successfully saved, use its mimetype to determine
        // which kind of media type it is.
        $media = $this->processMediaEntity($media, $file, $asset_data, $field_name);
        if ($media instanceof EntityInterface) {
          $media_entities[] = $media;
        }
        unset($media);
      }
    }

    return $media_entities;
  }

  /**
   * Get Media entity by asset data.
   *
   * @param array $asset_data
   *   Asset data.
   * @param mixed $field_id_storage
   *   Field id storage class.
   * @param string $field_name
   *   Field name.
   *
   * @return bool|\Drupal\media\Entity\Media|null
   *   Entity, null or false.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getMediaByAssetId(
    array $asset_data,
    $field_id_storage,
    $field_name
  ) {
    $media = NULL;
    if ($field_id_storage) {
      $results = $this->getMediaEntity($field_name, $asset_data['salsify:id']);

      if ($results) {
        $media_id = array_pop($results);

        $media_storage = $this->entityTypeManager->getStorage('media');
        /** @var \Drupal\media\Entity\Media $media */
        $media = $media_storage->load($media_id);
      }
    }
    return $media;
  }

  /**
   * Get file by asset data.
   *
   * @param array $asset_data
   *   Asset data.
   *
   * @return \Drupal\file\FileInterface|false|mixed
   *   File.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getFileByAssetData(array $asset_data) {
    $data_file = file_get_contents($asset_data['salsify:url']);
    $parsed_filename = $this->getParsedFileNameByAssetData($asset_data);
    return file_save_data($data_file, 'temporary://' . $parsed_filename, FileSystemInterface::EXISTS_REPLACE);
  }

  /**
   * Get parsed filename by asset data.
   *
   * @param array $asset_data
   *   Asset data.
   *
   * @return bool|string
   *   Parsed filename.
   */
  private function getParsedFileNameByAssetData(array $asset_data) {
    $parsed_filename = $asset_data['salsify:filename'];
    if ($position = strpos($parsed_filename, '?')) {
      $parsed_filename = substr($parsed_filename, 0, $position);
    }
    return $parsed_filename;
  }

  /**
   * Process media entity.
   *
   * @param mixed $media
   *   The media entity.
   * @param mixed $file
   *   The file entity.
   * @param array $asset_data
   *   Asset data.
   * @param string $field_name
   *   Field name.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\media\Entity\Media
   *   Media entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function processMediaEntity($media, $file, array $asset_data, string $field_name) {
    if ($file) {
      if (isset($media)) {
        $type = $media->bundle();
        $this->setMediaFields($type);
        $file_field_name = $this->fieldStorageConfig->getName();
        $current_file = $this->entityTypeManager->getStorage('file')
          ->load($media->{$file_field_name}->target_id);
        if ($current_file) {
          $current_file->delete();
        }
        $file = $this->moveFile($file);
        $media->set($file_field_name, $file->id());
        $media->save();
      }
      else {
        $bundle = (strpos($file->getMimeType(), 'image') !== FALSE) ?
          'image' : 'document';

        $this->setMediaFields($bundle);
        $file = $this->moveFile($file);

        // Verify the Salsify ID field is present on this media type.
        // If not, add it before proceeding.
        $this->verifySalsifyIdField(
          $field_name,
          $bundle
        );

        // Clean up file name to use as media name if Salsify is sending the
        // same values for both the name and filename fields.
        $media_name = $this->getMediaNameByAssetData($asset_data);

        // Create the new piece of media.
        $media = Media::create([
          'bundle' => $bundle,
          'name' => $media_name,
          'created' => strtotime($asset_data['salsify:created_at']),
          'changed' => strtotime($asset_data['salsify:updated_at']),
          'thumbnail__target_id' => $file->id(),
          $this->fieldStorageConfig->getName() => [
            'target_id' => $file->id(),
          ],
          $field_name => $asset_data['salsify:id'],
          'status' => 1,
        ]);
        $media->save();
      }
    }
    return $media;
  }

  /**
   * Get media name by asset data.
   *
   * Clean up file name to use as media name if Salsify is sending the
   * same values for both the name and filename fields.
   *
   * @param array $asset_data
   *   Asset data.
   *
   * @return string|null
   *   Media name.
   */
  private function getMediaNameByAssetData(array $asset_data) {
    $parsed_filename = $this->getParsedFileNameByAssetData($asset_data);
    $parsed_filename = urldecode($parsed_filename);
    if ($asset_data['salsify:name'] == $parsed_filename) {
      $media_name = preg_replace('/[^a-zA-Z0-9]/', " ", substr($asset_data['salsify:name'], 0, strripos($asset_data['salsify:name'], '.')));
    }
    else {
      $media_name = $asset_data['salsify:name'];
    }
    return $media_name;
  }

  /**
   * Verify the Salsify ID field is present on this media type.
   *
   * If not, add it before proceeding.
   *
   * @param string $field_name
   *   Field name.
   * @param string $bundle
   *   Bundle.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function verifySalsifyIdField($field_name, $bundle) {
    $media_salsify_id_field = FieldConfig::loadByName('media', $bundle, $field_name);
    if (!$media_salsify_id_field) {
      $salsify_id_field = [
        'salsify:id' => 'salsify:id',
        'salsify:system_id' => 'salsify:id',
        'salsify:name' => $this->t('Salsify Sync ID'),
        'salsify:data_type' => 'string',
        'salsify:created_at' => date('Y-m-d', time()),
        'date_updated' => time(),
      ];
      SalsifyFields::createDynamicField(
        $salsify_id_field,
        $field_name,
        'media',
        $bundle
      );
    }
  }

  /**
   * Query media entities based on a given field and its value.
   *
   * @param string $field_name
   *   The name of the field to search on.
   * @param string $field_value
   *   The value of the field to match.
   *
   * @return array|int
   *   An array of media entity ids that match the given options.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getMediaEntity($field_name, $field_value) {
    return $this->entityTypeManager->getStorage('media')
      ->getQuery()
      ->condition($field_name, $field_value)
      ->execute();
  }

  /**
   * Utility function to load field storage and configuration objects.
   *
   * @param string $type
   *   The media type (bundle) to load.
   */
  private function setMediaFields($type) {
    $field_mapping = Salsify::getFieldMappings(
      [
        'entity_type' => 'media',
        'bundle' => $type,
        'method' => 'manual',
      ],
      'salsify_id'
    );
    $field_mapping = $field_mapping['salsify:url'];
    $this->fieldStorageConfig = FieldStorageConfig::loadByName('media', $field_mapping['field_name']);
    $this->fieldConfig = FieldConfig::loadByName('media', $type, $field_mapping['field_name']);
  }

  /**
   * Utility function to move a temporary file to a new field uri structure.
   *
   * @param \Drupal\file\Entity\File $file
   *   The file object to be moved.
   *
   * @return \Drupal\file\FileInterface|false
   *   The updated file entity. FALSE if the move fails.
   */
  private function moveFile(File $file) {
    $scheme = $this->fieldStorageConfig->getSetting('uri_scheme');
    $location = $this->fieldConfig->getSetting('file_directory');
    $uri = $scheme . '://' . $this->token->replace($location);
    $this->fileSystem->prepareDirectory(
      $uri,
      FileSystemInterface::CREATE_DIRECTORY
    );
    return $this->fileRepository->move($file, $uri, FileSystemInterface::EXISTS_RENAME);
  }

}
