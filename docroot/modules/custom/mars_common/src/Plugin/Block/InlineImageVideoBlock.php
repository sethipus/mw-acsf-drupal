<?php

namespace Drupal\mars_common\Plugin\Block;

/**
 * Provides a Inline image/video component block.
 *
 * @Block(
 *   id = "inline_image_video_block",
 *   admin_label = @Translation("MARS: Inline image/video block"),
 *   category = @Translation("Page components"),
 * )
 */
class InlineImageVideoBlock extends ImageVideoBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $build['#heading'] = !empty($config['title']) ? $this->languageHelper->translate($config['title']) : '';
    $build['#content'] = $this->languageHelper->translate($config['description']);
    $build['#shape_motif'] = $config['svg_asset'] ? $this->themeConfiguratorParser->getBrandShapeWithoutFill() : FALSE;
    $build['#block_type'] = $config['block_content_type'];

    if ($config['block_content_type'] == static::CONTENT_TYPE_IMAGE && !empty($config['image'])) {

      $image_url = NULL;
      $media_id = $this->mediaHelper
        ->getIdFromEntityBrowserSelectValue($config['image']);

      if ($media_id) {
        $media_params = $this->mediaHelper->getMediaParametersById($media_id);
        if (!isset($media_params['error'])) {
          $image_url = file_create_url($media_params['src']);
        }
      }
      $build['#image_src'] = $image_url;
      $build['#image_alt'] = $media_params['alt'] ?? NULL;
      $build['#image_title'] = $media_params['title'] ?? NULL;
    }
    elseif ($config['block_content_type'] == static::CONTENT_TYPE_VIDEO && !empty($config['video'])) {

      $video_url = NULL;
      $media_id = $this->mediaHelper
        ->getIdFromEntityBrowserSelectValue($config['video']);

      if ($media_id) {
        $media_params = $this->mediaHelper->getMediaParametersById($media_id);
        if (!isset($media_params['error'])) {
          $video_url = file_create_url($media_params['src']);
        }
      }
      $build['#video_src'] = $video_url;
      $build['#hide_volume'] = !empty($config['hide_volume']) ? TRUE : FALSE ;
    }

    // Add media aspect ratio.
    $build['#aspect_ratio'] = $config['aspect_ratio'] ?? '16-9';

    $build['#theme'] = 'inline_image_video_block';

    return $build;
  }

}
