<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;

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

    $build['#title'] = $this->languageHelper->translate($config['title']);
    $build['#content'] = $this->languageHelper->translate($config['description']);
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
      $build['#image_src'] = $image_url;
      $build['#image_alt'] = $media_params['alt'] ?? NULL;
      $build['#image_title'] = $media_params['title'] ?? NULL;
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
      $build['#video_src'] = $video_url;
    }

    $build['#theme'] = 'inline_image_video_block';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['description']['#type'] = 'textarea';
    $form['description']['#maxlength'] = 300;

    return $form;
  }

}
