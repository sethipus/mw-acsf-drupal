<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_media\MediaHelper;

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
   * Parallax image type of content for the block.
   */
  protected const CONTENT_TYPE_PARALLAX_IMAGE = 'parallax_image';

  /**
   * Default heading modifier.
   */
  protected const DEFAULT_HEADING_MODIFIER = 'full-width-heading-left';

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $build['#heading'] = $this->languageHelper->translate($config['title']);
    $build['#heading_modifier'] = isset($config['heading_modifier']) ? $config['heading_modifier'] : static::DEFAULT_HEADING_MODIFIER;
    $build['#content'] = $this->languageHelper->translate($config['description']);
    $build['#block_type'] = $config['block_content_type'];

    if ($config['block_content_type'] == static::CONTENT_TYPE_IMAGE && !empty($config['image'])) {

      $build['#media']['medias'] = [];

      foreach (MediaHelper::LIST_IMAGE_RESOLUTIONS as $resolution) {
        $name = 'image';

        if ($resolution != 'desktop') {
          $name = 'image_' . $resolution;
        }

        if (!empty($config[$name])) {
          $mediaId = $this->mediaHelper->getIdFromEntityBrowserSelectValue($config[$name]);
          $media_params = $this->mediaHelper->getMediaParametersById($mediaId);
          if (!isset($media_params['error'])) {
            $build['#media']['medias'][$resolution] = $media_params;
          }
        }

        // Set value from previous resolution.
        if (empty($build['#media']['medias'][$resolution])) {
          $build['#media']['medias'][$resolution] = end($build['#media']['medias']);
        }
      }

      $build['#media']['src'] = $build['#media']['medias']['desktop']['src'] ?? NULL;
      $build['#media']['alt'] = $build['#media']['medias']['desktop']['alt'] ?? NULL;
      $build['#media']['title'] = $build['#media']['medias']['desktop']['title'] ?? NULL;

      $build['#media']['image'] = TRUE;
    }
    elseif ($config['block_content_type'] == static::CONTENT_TYPE_VIDEO && !empty($config['video'])) {
      // Thumbnail video time.
      $build['#thumbnail_video_time'] = !empty($config['thumbnail_video_time']) ? $config['thumbnail_video_time'] : '';
      $media_id = $this->mediaHelper
        ->getIdFromEntityBrowserSelectValue($config['video']);

      $media_params = $this->mediaHelper->getMediaParametersById($media_id);
      $build['#media'] = [
        'video' => TRUE,
        'src' => $media_params['src'] ?? NULL,
      ];
      $build['#hide_volume'] = $config['hide_volume'];
    }
    elseif ($config['block_content_type'] == static::CONTENT_TYPE_PARALLAX_IMAGE && !empty($config['parallax_image'])) {
      $build['#media']['medias'] = [];

      foreach (MediaHelper::LIST_IMAGE_RESOLUTIONS as $resolution) {
        $name = 'parallax_image';

        if ($resolution != 'desktop') {
          $name = 'parallax_image_' . $resolution;
        }

        if (!empty($config[$name])) {
          $mediaId = $this->mediaHelper->getIdFromEntityBrowserSelectValue($config[$name]);
          $media_params = $this->mediaHelper->getMediaParametersById($mediaId);
          if (!isset($media_params['error'])) {
            $build['#media']['medias'][$resolution] = $media_params;
          }
        }

        // Set value from previous resolution.
        if (empty($build['#media']['medias'][$resolution])) {
          $build['#media']['medias'][$resolution] = end($build['#media']['medias']);
        }
      }

      $build['#media']['parallax_image'] = TRUE;
      $build['#media']['src'] = $build['#media']['medias']['desktop']['src'] ?? NULL;
      $build['#media']['alt'] = $build['#media']['medias']['desktop']['alt'] ?? NULL;
      $build['#media']['title'] = $build['#media']['medias']['desktop']['title'] ?? NULL;
    }
    // Add media aspect ratio.
    $build['#media']['aspect_ratio'] = $config['aspect_ratio'] ?? '16-9';

    $build['#theme'] = 'fullwidth_image_video_block';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['title']['#states'] = [
      'invisible' => [
        ':input[name="settings[block_content_type]"]' => ['value' => self::CONTENT_TYPE_PARALLAX_IMAGE],
      ],
    ];

    $form['heading_modifier'] = [
      '#type' => 'radios',
      '#title' => $this->t('Title alignment'),
      '#default_value' => isset($config['heading_modifier']) ? $config['heading_modifier'] : static::DEFAULT_HEADING_MODIFIER,
      '#options' => [
        'full-width-heading-left' => $this->t('Left'),
        'full-width-heading-center' => $this->t('Center'),
        'full-width-heading-right' => $this->t('Right'),
      ],
      '#states' => [
        'invisible' => [
          ':input[name="settings[block_content_type]"]' => ['value' => self::CONTENT_TYPE_PARALLAX_IMAGE],
        ],
      ],
    ];

    $form['description']['#states'] = [
      'invisible' => [
        ':input[name="settings[block_content_type]"]' => ['value' => self::CONTENT_TYPE_PARALLAX_IMAGE],
      ],
    ];

    $form['block_content_type']['#options'][self::CONTENT_TYPE_PARALLAX_IMAGE] = $this->t('Full width parallax image');
    $form['block_content_type']['#options'][self::CONTENT_TYPE_IMAGE] = $this->t('Breakout image');
    $form['block_content_type']['#options'][self::CONTENT_TYPE_VIDEO] = $this->t('Breakout video');

    foreach (MediaHelper::LIST_IMAGE_RESOLUTIONS as $resolution) {
      $name = 'parallax_image';

      $validate_callback = function ($form_state) {
        return $form_state->getValue(['settings', 'block_content_type']) === self::CONTENT_TYPE_PARALLAX_IMAGE;
      };

      if ($resolution != 'desktop') {
        $name = 'parallax_image_' . $resolution;
        $validate_callback = FALSE;
      }

      $image_default = isset($config[$name]) ? $config[$name] : NULL;
      // Entity Browser element for background image.
      $form[$name] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID,
        $image_default, $form_state, 1, 'thumbnail', $validate_callback
      );
      // Convert the wrapping container to a details element.
      $form[$name]['#type'] = 'details';
      $form[$name]['#title'] = $this->t('Parallax image (@resolution)', ['@resolution' => ucfirst($resolution)]);
      $form[$name]['#open'] = TRUE;
      $form[$name]['#states'] = [
        'visible' => [
          ':input[name="settings[block_content_type]"]' => ['value' => self::CONTENT_TYPE_PARALLAX_IMAGE],
        ],
        'required' => [
          ':input[name="settings[block_content_type]"]' => ['value' => self::CONTENT_TYPE_PARALLAX_IMAGE],
        ],
      ];

      if ($resolution != 'desktop') {
        $form[$name]['#description'] = $this->t('Image Alt and Title will be replaced by Desktop image.');
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * This method processes the blockForm() form fields when the block
   * configuration form is submitted.
   *
   * The blockValidate() method can be used to validate the form submission.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    foreach (MediaHelper::LIST_IMAGE_RESOLUTIONS as $resolution) {
      $name = 'parallax_image';

      if ($resolution != 'desktop') {
        $name = 'parallax_image_' . $resolution;
      }

      $this->configuration[$name] = $this->getEntityBrowserValue($form_state, $name);
    }
  }

}
