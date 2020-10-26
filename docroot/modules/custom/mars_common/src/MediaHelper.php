<?php

namespace Drupal\mars_common;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mars_product\ProductHelper;

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
   * Product helper service.
   *
   * @var \Drupal\mars_product\ProductHelper
   */
  private $productHelper;

  /**
   * MediaHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\mars_product\ProductHelper $product_helper
   *   The product helper service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ProductHelper $product_helper
  ) {
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->productHelper = $product_helper;
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
          // Todo: Get proper format data.
          'format' => 'video/mp4',
        ];

      case 'video_file':
        if (!$entity->field_media_image || !$entity->field_media_image->target_id) {
          return ['error' => TRUE, 'message' => $this->t('Image not set.')];
        }

        return [
          'video' => TRUE,
          'src' => $entity->field_media_video_file->entity->createFileUrl(!$absolute_urls),
          // Todo: Get proper format data.
          'format' => 'video/mp4',
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

  /**
   * Returns the main media id for a given content.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $contentEntity
   *   The content entity.
   *
   * @return string|null
   *   The main image media id or NULL.
   */
  public function getEntityMainMediaId(
    ContentEntityInterface $contentEntity
  ): ?string {
    // TODO: Use event dispatch to handle this.
    $media_id = NULL;
    switch ($contentEntity->bundle()) {
      case 'article':
        $media_id = $this->getTargetIdFromField($contentEntity,
          'field_article_image');
        break;

      case 'recipe':
        $media_id = $this->getTargetIdFromField($contentEntity,
          'field_recipe_image');
        break;

      case 'product':
        $main_variant = $this->productHelper->mainVariant($contentEntity);

        if ($main_variant) {
          $media_id = $this->getEntityMainMediaId($main_variant);
        }
        break;

      case 'product_variant':
        $media_id = $this->getTargetIdFromField($contentEntity,
          'field_product_key_image_override');
        if (empty($media_id)) {
          $media_id = $this->getTargetIdFromField($contentEntity,
            'field_product_key_image');
        }
        break;

      case 'error_page':
        $media_id = $this->getTargetIdFromField($contentEntity,
          'field_error_page_image');
        break;

      case 'campaign':
        $media_id = $this->getTargetIdFromField($contentEntity,
          'field_campaign_image');
        break;

      case 'content_hub_page':
        $media_id = $this->getTargetIdFromField($contentEntity,
          'field_content_hub_image');
        break;

      case 'landing_page':
        $media_id = $this->getTargetIdFromField($contentEntity,
          'field_landing_page_image');
        break;

      case 'mars_diet_allergens':
        $media_id = $this->getTargetIdFromField($contentEntity,
          'field_allergen_image');
        break;
    }
    return $media_id;
  }

  /**
   * Get entity ref field target id value or NULL if it's missing.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $contentEntity
   *   The content enity.
   * @param string $fieldName
   *   The name of the field to check.
   *
   * @return string|null
   *   The entity reference field target id, or null if it does not exist.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  private function getTargetIdFromField(
    ContentEntityInterface $contentEntity,
    string $fieldName
  ) {
    $media_id = NULL;
    if (!$contentEntity->get($fieldName)->isEmpty()) {
      $media_id = $contentEntity
        ->get($fieldName)
        ->first()
        ->target_id;
    }
    return $media_id;
  }

}
