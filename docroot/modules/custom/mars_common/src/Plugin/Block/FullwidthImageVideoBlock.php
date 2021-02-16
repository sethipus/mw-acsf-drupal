<?php

namespace Drupal\mars_common\Plugin\Block;

/**
 * Provides a Fullwidth image/video component block.
 *
 * @Block(
 *   id = "fullwidth_image_video_block",
 *   admin_label = @Translation("MARS: Fullwidth image/video block"),
 *   category = @Translation("Page components"),
 * )
 */
class FullwidthImageVideoBlock extends ImageVideoBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $build['#heading'] = $this->languageHelper->translate($config['title']);
    $build['#content'] = $this->languageHelper->translate($config['description']);
    $build['#block_type'] = $config['block_content_type'];

    if ($config['block_content_type'] == static::CONTENT_TYPE_IMAGE && !empty($config['image'])) {

      $media_id = $this->mediaHelper
        ->getIdFromEntityBrowserSelectValue($config['image']);

      $media_params = $this->mediaHelper->getMediaParametersById($media_id);
      $build['#media'] = [
        'image' => TRUE,
        'src' => $media_params['src'] ?? NULL,
        'alt' => $media_params['alt'] ?? NULL,
        'title' => $media_params['title'] ?? NULL,
      ];
    }
    elseif ($config['block_content_type'] == static::CONTENT_TYPE_VIDEO && !empty($config['video'])) {

      $media_id = $this->mediaHelper
        ->getIdFromEntityBrowserSelectValue($config['video']);

      $media_params = $this->mediaHelper->getMediaParametersById($media_id);
      $build['#media'] = [
        'video' => TRUE,
        'src' => $media_params['src'] ?? NULL,
      ];
    }

    $build['#theme'] = 'fullwidth_image_video_block';

    return $build;
  }

}
