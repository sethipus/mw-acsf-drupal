<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base image/video component logic.
 */
abstract class ImageVideoBlockBase extends BlockBase implements ContainerFactoryPluginInterface {

  use EntityBrowserFormTrait;

  /**
   * Image type of content for the block.
   */
  protected const CONTENT_TYPE_IMAGE = 'image';

  /**
   * Video type of content for the block.
   */
  protected const CONTENT_TYPE_VIDEO = 'video';

  /**
   * Lighthouse entity browser image id.
   */
  public const LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID = 'lighthouse_browser';

  /**
   * Lighthouse entity browser video id.
   */
  public const LIGHTHOUSE_ENTITY_BROWSER_VIDEO_ID = 'lighthouse_video_browser';

  /**
   * Mars Media Helper service.
   *
   * @var \Drupal\mars_common\MediaHelper
   */
  protected $mediaHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MediaHelper $media_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mediaHelper = $media_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_common.media_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['block_content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Please choose video or image type of content.'),
      '#default_value' => $config['block_content_type'] ?? self::CONTENT_TYPE_IMAGE,
      '#options' => [
        self::CONTENT_TYPE_VIDEO => $this->t('Video'),
        self::CONTENT_TYPE_IMAGE => $this->t('Image'),
      ],
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 55,
      '#default_value' => $config['title'] ?? '',
    ];

    $form['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#maxlength' => 35,
      '#default_value' => $config['description'] ?? '',
    ];

    $form['svg_asset'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add decorative brand shape'),
      '#default_value' => $config['svg_asset'] ?? FALSE,
    ];

    $image_default = isset($config['image']) ? $config['image'] : NULL;
    // Entity Browser element for background image.
    $form['image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID, $image_default, 1, 'thumbnail');
    // Convert the wrapping container to a details element.
    $form['image']['#type'] = 'details';
    $form['image']['#title'] = $this->t('Image');
    $form['image']['#open'] = TRUE;
    $form['image']['#states'] = [
      'visible' => [
        ':input[name="settings[block_content_type]"]' => ['value' => self::CONTENT_TYPE_IMAGE],
      ],
      'required' => [
        ':input[name="settings[block_content_type]"]' => ['value' => self::CONTENT_TYPE_IMAGE],
      ],
    ];

    $video_default = isset($config['video']) ? $config['video'] : NULL;
    // Entity Browser element for video.
    $form['video'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_VIDEO_ID, $video_default, 1);
    // Convert the wrapping container to a details element.
    $form['video']['#type'] = 'details';
    $form['video']['#title'] = $this->t('Video');
    $form['video']['#open'] = TRUE;
    $form['video']['#states'] = [
      'visible' => [
        [':input[name="settings[block_content_type]"]' => ['value' => self::CONTENT_TYPE_VIDEO]],
      ],
      'required' => [
        [':input[name="settings[block_content_type]"]' => ['value' => self::CONTENT_TYPE_VIDEO]],
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
    $this->setConfiguration($form_state->getValues());
    $this->configuration['image'] = $this->getEntityBrowserValue($form_state, 'image');
    $this->configuration['video'] = $this->getEntityBrowserValue($form_state, 'video');
  }

}
