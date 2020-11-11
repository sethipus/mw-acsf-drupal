<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a parent page header block.
 *
 * @Block(
 *   id = "parent_page_header",
 *   admin_label = @Translation("Parent Page Header"),
 *   category = @Translation("Custom")
 * )
 */
class ParentPageHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use EntityBrowserFormTrait;

  /**
   * Media storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * Lighthouse entity browser image id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID = 'lighthouse_browser';

  /**
   * Lighthouse entity browser video id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_VIDEO_ID = 'lighthouse_video_browser';

  /**
   * Key option background video.
   */
  const KEY_OPTION_VIDEO = 'video';

  /**
   * Key option background image.
   */
  const KEY_OPTION_IMAGE = 'image';

  /**
   * Default background style.
   */
  const KEY_OPTION_DEFAULT = 'default';

  /**
   * Background options.
   *
   * @var array
   */
  protected $options = [
    self::KEY_OPTION_DEFAULT => 'Default background style',
    self::KEY_OPTION_VIDEO => 'Video',
    self::KEY_OPTION_IMAGE => 'Image',
  ];

  /**
   * Mars Media Helper service.
   *
   * @var \Drupal\mars_common\MediaHelper
   */
  protected $mediaHelper;

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
  public function build() {
    $conf = $this->getConfiguration();

    $build['#eyebrow'] = $conf['eyebrow'] ?? '';
    $build['#label'] = $conf['title'] ?? '';
    $media_id = NULL;

    if (!empty($conf['background_options'])) {
      if ($conf['background_options'] == self::KEY_OPTION_IMAGE && !empty($conf['background_image'])) {
        $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($conf['background_image']);
      }
      elseif ($conf['background_options'] == self::KEY_OPTION_VIDEO && !empty($conf['background_video'])) {
        $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($conf['background_video']);
      }
    }

    if ($media_id) {
      $media_params = $this->mediaHelper->getMediaParametersById($media_id);
      if (!isset($media_params['error'])) {
        $build['#background'] = $media_params['src'];
        $build['#media_type'] = 'image';

        if ($media_params['video'] ?? FALSE) {
          $build['#media_type'] = 'video';
          $build['#media_format'] = $media_params['format'];
          $build['#data_layer'] = [
            'video_id' => $media_id,
            'video_flag' => 'Background',
          ];
        }

      }
    }

    $build['#description'] = $conf['description'] ?? '';
    $build['#theme'] = 'parent_page_header_block';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'label_display' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#maxlength' => 30,
      '#default_value' => $this->configuration['eyebrow'] ?? '',
    ];
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 55,
      '#default_value' => $this->configuration['title'] ?? '',
    ];
    $form['background_options'] = [
      '#type' => 'radios',
      '#title' => $this->t('Background type'),
      '#options' => $this->options,
      '#default_value' => isset($config['background_options']) ? $config['background_options'] : NULL,
    ];

    $image_default = isset($config['background_image']) ? $config['background_image'] : NULL;
    // Entity Browser element for background image.
    $form['background_image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID, $image_default, 1, 'thumbnail');
    // Convert the wrapping container to a details element.
    $form['background_image']['#type'] = 'details';
    $form['background_image']['#title'] = $this->t('Image');
    $form['background_image']['#open'] = TRUE;
    $form['background_image']['#states'] = [
      'visible' => [
        ':input[name="settings[background_options]"]' => ['value' => self::KEY_OPTION_IMAGE],
      ],
    ];

    $video_default = isset($config['background_video']) ? $config['background_video'] : NULL;
    // Entity Browser element for video.
    $form['background_video'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_VIDEO_ID, $video_default, 1);
    // Convert the wrapping container to a details element.
    $form['background_video']['#type'] = 'details';
    $form['background_video']['#title'] = $this->t('Video');
    $form['background_video']['#open'] = TRUE;
    $form['background_video']['#states'] = [
      'visible' => [
        ':input[name="settings[background_options]"]' => ['value' => self::KEY_OPTION_VIDEO],
      ],
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#maxlength' => 255,
      '#default_value' => $this->configuration['description'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['eyebrow'] = $form_state->getValue('eyebrow');
    $this->configuration['title'] = $form_state->getValue('title');
    $this->configuration['description'] = $form_state->getValue('description');
    $this->configuration['background_options'] = $form_state->getValue('background_options');
    $this->configuration['background_image'] = $this->getEntityBrowserValue($form_state, 'background_image');
    $this->configuration['background_video'] = $this->getEntityBrowserValue($form_state, 'background_video');
  }

}
