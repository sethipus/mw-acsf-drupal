<?php

namespace Drupal\mars_seo;

use Drupal\Core\Plugin\ContextAwarePluginBase;
use Drupal\media\Entity\Media;

/**
 * Base class for Mars JSON LD Strategy plugins.
 */
abstract class JsonLdStrategyPluginBase extends ContextAwarePluginBase implements JsonLdStrategyInterface {

  /**
   * {@inheritdoc}
   */
  public function label() {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function supportedBundles() {
    return $this->pluginDefinition['bundles'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  abstract public function getStructuredData();

  /**
   * Helper method that extracts image URL from Media entity.
   *
   * @param \Drupal\media\Entity\Media $media
   *   Media entity.
   *
   * @return string|false
   *   Media URL or NULL if bundle is not supported.
   */
  protected function getMediaUrl(Media $media) {
    /** @var \Drupal\file\Entity\File|null $file */
    $file = NULL;

    switch ($media->bundle()) {
      case 'image':
        $file = $media->image->entity;
        break;

      case 'lighthouse_image':
        $file = $media->field_media_image->entity;
        break;

      case 'video_file':
        $file = $media->field_media_video_file->entity;
        break;

      case 'lighthouse_video':
        $file = $media->field_media_video_file_1->entity;
        break;

      case 'video':
        return $media->field_media_video_embed_field->value;

    }

    if (isset($file)) {
      return $file->createFileUrl(FALSE);
    }

    return FALSE;
  }

}
