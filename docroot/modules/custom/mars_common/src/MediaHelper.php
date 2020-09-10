<?php

namespace Drupal\mars_common;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class MediaHelpers.
 */
class MediaHelper {

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

}
