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
    elseif ($config['block_content_type'] == static::CONTENT_TYPE_PARALLAX_IMAGE && !empty($config['parallax_image'])) {
      foreach (MediaHelper::LIST_IMAGE_RESOLUTIONS as $resolution) {
        $name = 'parallax_image';

        if ($resolution != 'desktop') {
          $name = 'parallax_image_' . $resolution;
        }

        if (!empty($config[$name])) {
          $mediaId = $this->mediaHelper->getIdFromEntityBrowserSelectValue($config[$name]);
          $media_params = $this->mediaHelper->getMediaParametersById($mediaId);
          if (!isset($media_params['error'])) {
            $media_params['parallax_image'] = TRUE;
            $build['#media'][$resolution] = $media_params;
          }
        }
        else {
          // Set value from previous resolution.
          $build['#media'][$resolution] = end($build['#media']);
        }
      }
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
      $required = TRUE;

      if ($resolution != 'desktop') {
        $name = 'parallax_image_' . $resolution;
        $required = FALSE;
      }

      $image_default = isset($config[$name]) ? $config[$name] : NULL;
      // Entity Browser element for background image.
      $form[$name] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID,
        $image_default, $form_state, 1, 'thumbnail', function ($form_state) {
          return $form_state->getValue(['settings', 'block_content_type']) === self::CONTENT_TYPE_PARALLAX_IMAGE;
        }
      );
      // Convert the wrapping container to a details element.
      $form[$name]['#type'] = 'details';
      $form[$name]['#title'] = $this->t('Parallax image (@resolution)', ['@resolution' => ucfirst($resolution)]);
      $form[$name]['#open'] = TRUE;
      $form[$name]['#required'] = $required;
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
