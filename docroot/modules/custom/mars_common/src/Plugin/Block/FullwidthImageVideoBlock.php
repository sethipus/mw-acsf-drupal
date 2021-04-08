<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;

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

      $build['#media'] = [
        'video' => TRUE,
        'src' => $video_url,
      ];
    }
    elseif ($config['block_content_type'] == static::CONTENT_TYPE_PARALLAX_IMAGE && !empty($config['parallax_image'])) {
      $parallax_image_url = NULL;
      $media_id = $this->mediaHelper
        ->getIdFromEntityBrowserSelectValue($config['parallax_image']);

      if ($media_id) {
        $media_params = $this->mediaHelper->getMediaParametersById($media_id);
        if (!isset($media_params['error'])) {
          $parallax_image_url = file_create_url($media_params['src']);
        }
      }
      $build['#media'] = [
        'parallax_image' => TRUE,
        'src' => $parallax_image_url,
        'alt' => $media_params['alt'] ?? NULL,
        'title' => $media_params['title'] ?? NULL,
      ];
    }

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

    $image_default = isset($config['parallax_image']) ? $config['parallax_image'] : NULL;
    // Entity Browser element for background image.
    $form['parallax_image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID,
      $image_default, $form_state, 1, 'thumbnail', function ($form_state) {
        return $form_state->getValue(['settings', 'block_content_type']) === self::CONTENT_TYPE_PARALLAX_IMAGE;
      }
    );
    // Convert the wrapping container to a details element.
    $form['parallax_image']['#type'] = 'details';
    $form['parallax_image']['#title'] = $this->t('Parallax image');
    $form['parallax_image']['#open'] = TRUE;
    $form['parallax_image']['#states'] = [
      'visible' => [
        ':input[name="settings[block_content_type]"]' => ['value' => self::CONTENT_TYPE_PARALLAX_IMAGE],
      ],
      'required' => [
        ':input[name="settings[block_content_type]"]' => ['value' => self::CONTENT_TYPE_PARALLAX_IMAGE],
      ],
    ];

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
    $this->configuration['parallax_image'] = $this->getEntityBrowserValue($form_state, 'parallax_image');
  }

}
