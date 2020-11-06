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

    $build['#heading'] = $config['title'];
    $build['#content'] = $config['description'];
    $build['#shape_motif'] = (bool) $config['svg_asset'];
    $build['#block_type'] = $config['block_content_type'];

    if ($config['block_content_type'] == self::CONTENT_TYPE_IMAGE && !empty($config['image'])) {

      $image_url = NULL;
      $media_id = $this->mediaHelper
        ->getIdFromEntityBrowserSelectValue($config['image']);

      if ($media_id) {
        $media_params = $this->mediaHelper->getMediaParametersById($media_id);
        if (!isset($media_params['error'])) {
          $image_url = file_create_url($media_params['src']);
        }
      }
      $build['#media'] = [
        'image' => TRUE,
        'src' => $image_url,
        'alt' => $media_params['alt'] ?? NULL,
        'title' => $media_params['title'] ?? NULL,
      ];
    }
    elseif ($config['block_content_type'] == self::CONTENT_TYPE_VIDEO && !empty($config['video'])) {

      $video_url = NULL;
      $media_id = $this->mediaHelper
        ->getIdFromEntityBrowserSelectValue($config['video']);

      if ($media_id) {
        $media_params = $this->mediaHelper->getMediaParametersById($media_id);
        if (!isset($media_params['error'])) {
          $video_url = file_create_url($media_params['src']);
        }
      }

      $build['#media'] = [
        'video' => TRUE,
        'src' => $video_url,
      ];
      // Datalayer attributes.
      $build['#data_layer'] = [
        'video_id' => $media_id,
        'video_flag' => 'User played',
      ];
    }

    $build['#theme'] = 'fullwidth_image_video_block';

    return $build;
  }

}
