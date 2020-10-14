<?php

namespace Drupal\mars_common;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class MediaHelpers.
 */
class MediaHelper {
  use StringTranslationTrait;

  /**
   * Media storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->mediaStorage = $entity_type_manager->getStorage('media');
  }

  /**
   * Helper method that loads Media file URL using Media id.
   *
   * @param int $media_id
   *   Media ID.
   *
   * @return string|null
   *   File URI or NULL if URI cannot be defined.
   */
  public function getMediaUriById($media_id) {
    if (empty($media_id) || !($entity = $this->mediaStorage->load($media_id))) {
      return NULL;
    }

    if (!$entity->image || !$entity->image->target_id) {
      return NULL;
    }

    return $entity->image->entity->uri->value ?? NULL;
  }

  /**
   * Helper method that loads Media parameters Media id.
   *
   * @param int $media_id
   *   Media ID.
   * @param bool $absolute_urls
   *   If TRUE, src will be formatted as an absolute URL.
   *
   * @return array
   *   Media parameters.
   */
  public function getMediaParametersById($media_id, $absolute_urls = FALSE) {
    if (empty($media_id) || empty($entity = $this->mediaStorage->load($media_id))) {
      return ['error' => TRUE, 'message' => $this->t('Media not found.')];
    }

    switch ($entity->bundle()) {
      case 'image':
        if (!$entity->image || !$entity->image->target_id) {
          return ['error' => TRUE, 'message' => $this->t('Image not set.')];
        }

        return [
          'image' => TRUE,
          'src' => $entity->image->entity->createFileUrl(!$absolute_urls),
          'alt' => $entity->image->alt,
          'title' => $entity->image->title,
        ];

      case 'lighthouse_image':
        if (!$entity->field_media_image || !$entity->field_media_image->target_id) {
          return ['error' => TRUE, 'message' => $this->t('Image not set.')];
        }

        return [
          'image' => TRUE,
          'src' => $entity->field_media_image->entity->createFileUrl(!$absolute_urls),
          'alt' => $entity->field_media_image->alt,
          'title' => $entity->field_media_image->title,
        ];

      case 'lighthouse_video':
        if (!$entity->field_media_video_file_1 || !$entity->field_media_video_file_1->target_id) {
          return ['error' => TRUE, 'message' => $this->t('Video not set.')];
        }

        return [
          'video' => TRUE,
          'src' => $entity->field_media_video_file_1->entity->createFileUrl(!$absolute_urls),
        ];

      case 'video_file':
        if (!$entity->field_media_image || !$entity->field_media_image->target_id) {
          return ['error' => TRUE, 'message' => $this->t('Image not set.')];
        }

        return [
          'video' => TRUE,
          'src' => $entity->field_media_image->entity->createFileUrl(!$absolute_urls),
        ];

      case 'video':
        if (!$entity->field_media_video_embed_field || !$entity->field_media_video_embed_field->target_id) {
          return ['error' => TRUE, 'message' => $this->t('Image not set.')];
        }

        return [
          'video' => TRUE,
          'src' => $entity->field_media_video_embed_field->value,
        ];

      default:
        return [
          'error' => TRUE,
          'message' => $this->t('Incorrect media type.'),
        ];

    }
  }

  /**
   * Helper method that extracts image URL from Media entity.
   *
   * @param int $media_id
   *   Media ID.
   *
   * @return string|false
   *   Media URL or NULL if bundle is not supported.
   */
  public function getMediaUrl($media_id) {
    $data = $this->getMediaParametersById($media_id, TRUE);

    if (!empty($data['error'])) {
      return FALSE;
    }

    return $data['src'];
  }

  /**
   * Helper function to get id from entity browser select value.
   *
   * @param string|null $entityBrowserSelectValue
   *   The select value.
   *
   * @return string|null
   *   The resulting id.
   */
  public function getIdFromEntityBrowserSelectValue(
    ?string $entityBrowserSelectValue
  ) {
    if (!$entityBrowserSelectValue || !is_string($entityBrowserSelectValue)) {
      return NULL;
    }
    $colonPosition = strpos($entityBrowserSelectValue, ':');

    if ($colonPosition === FALSE) {
      return NULL;
    }

    return substr($entityBrowserSelectValue, $colonPosition + 1);
  }

}
