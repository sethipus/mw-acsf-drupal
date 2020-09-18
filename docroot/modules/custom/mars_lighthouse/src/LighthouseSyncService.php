<?php

namespace Drupal\mars_lighthouse;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_lighthouse\Controller\LighthouseAdapter;
use Drupal\media\MediaInterface;

/**
 * Class LighthouseSyncService.
 */
class LighthouseSyncService {

  use DependencySerializationTrait;

  /**
   * Lighthouse bundle name.
   */
  const LIGHTHOUSE_IMAGE_BUNDLE = 'lighthouse_image';

  /**
   * Media storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Lighthouse client.
   *
   * @var \Drupal\mars_lighthouse\LighthouseClientInterface
   */
  protected $lighthouseClient;

  /**
   * Fields mapping.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $mapping;

  /**
   * File entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $fileStorage;

  /**
   * Media Type.
   *
   * @var string
   */
  protected $mediaType = 'image';

  /**
   * Media Type config array.
   *
   * @var array
   */
  private $mediaConfig = [
    'image' => [
      'bundle' => 'lighthouse_image',
      'field' => 'field_media_image',
    ],
    'video' => [
      'bundle' => 'lighthouse_video',
      'field' => 'field_media_video_file_1',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    LighthouseClientInterface $lighthouse_client,
    LighthouseInterface $lighthouse
  ) {
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->configFactory = $config_factory;
    $this->lighthouseClient = $lighthouse_client;
    $this->mapping = $this->configFactory->get(LighthouseAdapter::CONFIG_NAME);
    $this->lighthouseAdapter = $lighthouse;
  }

  /**
   * Sync media.
   */
  public function syncLighthouseSite() {
    $media_objects = $this->mediaStorage->loadByProperties([
      'bundle' => self::LIGHTHOUSE_IMAGE_BUNDLE,
    ]);
    if (!empty($media_objects)) {
//      $operations = [];
//      $numOperations = 0;
//      $batchId = 1;
      foreach ($media_objects as $media_object) {
//        $mid = $media_object->id();
        $this->processMediaSync($media_object);
//        $operations[] = [
////          '\Drupal\mars_lighthouse\LighthouseSyncService::processMediaSync',
////          [
////            $mid,
////            t('Media @media', ['@mid' => $mid]),
////          ],
////        ];
////        $batchId++;
////        $numOperations++;
      }
//      $batch = [
//        'title' => t('Updating @num node(s)', ['@num' => $numOperations]),
//        'operations' => $operations,
//        'finished' => [$this, 'processMediaSyncFinished'],
//      ];
//
//      batch_set($batch);
//      $batch =& batch_get();
//      $batch['progressive'] = FALSE;
//      batch_process();

    }

  }

  /**
   * Batch process callback.
   *
   * @param int $id
   *   Id of the batch.
   * @param string $operation_details
   *   Details of the operation.
   * @param object $context
   *   Context for operations.
   */
  public function processMediaSync($media) {
    if ($media instanceof MediaInterface) {
      $external_id = $media->field_external_id->value;
      $params = $this->lighthouseAdapter->getToken();
      try {
        $data = $this->lighthouseClient->getAssetById($external_id, $params);
      }
      catch (TokenIsExpiredException $e) {
        // Try to refresh token.
        $params = $this->lighthouseAdapter->refreshToken($params);
        $data = $this->lighthouseClient->getAssetById($external_id, $params);
      }

      if (!empty($data) &&
        isset($data['versionIdOTMM']) &&
        $data['versionIdOTMM'] != $media->field_version_id->value) {
          $this->updateMediaData($media, $data);
      }
    }


//    $context['results'][] = $id;
//    // Optional message displayed under the progressbar.
//    $context['message'] = t('Running Batch "@id" @details',
//      ['@id' => $id, '@details' => $operation_details]
//    );
  }

  /**
   * Batch Finished callback.
   *
   * @param bool $success
   *   Success of the operation.
   * @param array $results
   *   Array of results for post processing.
   * @param array $operations
   *   Array of operations.
   */
  public function processMediaSyncFinished($success, array $results, array $operations) {
    $messenger = \Drupal::messenger();
    if ($success) {
      // Here we could do something meaningful with the results.
      // We just display the number of nodes we processed...
      $messenger->addMessage(t('@count results processed.', ['@count' => count($results)]));
    }
    else {
      // An error occurred.
      // $operations contains the operations that remained unprocessed.
      $error_operation = reset($operations);
      $messenger->addMessage(
        t('An error occurred while processing @operation with arguments : @args',
          [
            '@operation' => $error_operation[0],
            '@args' => print_r($error_operation[0], TRUE),
          ]
        )
      );
    }
  }

  /**
   * Update media entity from API response.
   *
   * @param array $data
   *   Response data with one entity.
   * @param array $data
   *   Response data with one entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   Media entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateMediaData(MediaInterface $media, array $data) {
    if (!$data) {
      return NULL;
    }

    $file_mapping = $this->mapping->get('media');
    $field_config = $this->mediaConfig[$this->mediaType];
    $field_file = $field_config['field'];
    $fid = $media->$field_file->target_id;
    $this->updateFileEntity($fid, $data);

    foreach ($file_mapping as $field_name => $path_to_value) {
      $media->set($field_name, $data[$path_to_value]);
    }
    $media->save();

    return $media;
  }

  /**
   * Update file entity from API response.
   *
   * @param array $data
   *   Response data with one entity.
   *
   * @return string
   *   ID of File entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function updateFileEntity($fid, array $data): string {
    $file = $this->fileStorage->load($fid);
    $file_mapping = $this->mapping->get('file');

    foreach ($file_mapping as $field_name => $path_to_value) {
      $path_to_value = explode('.', $path_to_value);
      $value = $data[array_shift($path_to_value)] ?? NULL;
      while ($path_to_value) {
        $value = $value[array_shift($path_to_value)] ?? NULL;
      }
      $file->set($field_name, $value);
    }

    $file->save();
    return $file->id();
  }

}
