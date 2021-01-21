<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a parent page header block.
 *
 * @Block(
 *   id = "parent_page_header",
 *   admin_label = @Translation("MARS: Parent Page Header"),
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
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

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
   * Default background style.
   */
  const KEY_OPTION_OTHER_COLOR = 'other';

  /**
   * Default background style.
   */
  const KEY_OPTION_TEXT_COLOR_DEFAULT = 'color_e';

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
   * Theme configurator parser service.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  private $themeConfiguratorParser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_common.language_helper'),
      $container->get('mars_common.media_helper'),
      $container->get('mars_common.theme_configurator_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LanguageHelper $language_helper,
    MediaHelper $media_helper,
    ThemeConfiguratorParser $theme_configurator_parser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageHelper = $language_helper;
    $this->mediaHelper = $media_helper;
    $this->themeConfiguratorParser = $theme_configurator_parser;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();

    $build['#eyebrow'] = $this->languageHelper->translate($conf['eyebrow'] ?? '');
    $build['#label'] = $this->languageHelper->translate($conf['title'] ?? '');
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
        }

      }
    }

    $build['#description'] = $this->languageHelper->translate($conf['description'] ?? '');
    $build['#brand_shape'] = $this->themeConfiguratorParser->getBrandShapeWithoutFill();
    $build['#text_color'] = $this->getTextColor();
    $build['#theme'] = 'parent_page_header_block';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'label_display' => FALSE,
      'text_color' => self::KEY_OPTION_TEXT_COLOR_DEFAULT,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();

    $block_type_value = $config['background_options'] ?? self::KEY_OPTION_DEFAULT;
    $submitted_input = $form_state->getUserInput()['settings'] ?? [];
    $type_for_validation = $submitted_input['background_options'] ?? $block_type_value;

    $form['eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#maxlength' => 30,
      '#default_value' => $this->configuration['eyebrow'] ?? '',
      '#required' => in_array($type_for_validation, [
        self::KEY_OPTION_DEFAULT,
      ]),
      '#states' => [
        'required' => [
          [
            ':input[name="settings[background_options]"]' => ['value' => self::KEY_OPTION_DEFAULT],
          ],
        ],
      ],
    ];
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 55,
      '#default_value' => $this->configuration['title'] ?? '',
      '#required' => in_array($type_for_validation, [
        self::KEY_OPTION_DEFAULT,
      ]),
      '#states' => [
        'required' => [
          [
            ':input[name="settings[background_options]"]' => ['value' => self::KEY_OPTION_DEFAULT],
          ],
        ],
      ],
    ];
    $form['background_options'] = [
      '#type' => 'radios',
      '#title' => $this->t('Background type'),
      '#options' => $this->options,
      '#default_value' => isset($config['background_options']) ? $config['background_options'] : NULL,
    ];

    $image_default = isset($config['background_image']) ? $config['background_image'] : NULL;
    // Entity Browser element for background image.
    $form['background_image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID,
      $image_default, $form_state, 1, 'thumbnail', function ($form_state) {
        return $form_state->getValue(['settings', 'background_options']) === self::KEY_OPTION_IMAGE;
      }
    );
    // Convert the wrapping container to a details element.
    $form['background_image']['#type'] = 'details';
    $form['background_image']['#title'] = $this->t('Image');
    $form['background_image']['#open'] = TRUE;
    $form['background_image']['#states'] = [
      'visible' => [
        ':input[name="settings[background_options]"]' => ['value' => self::KEY_OPTION_IMAGE],
      ],
      'required' => [
        ':input[name="settings[background_options]"]' => ['value' => self::KEY_OPTION_IMAGE],
      ],
    ];

    $video_default = isset($config['background_video']) ? $config['background_video'] : NULL;
    // Entity Browser element for video.
    $form['background_video'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_VIDEO_ID,
      $video_default, $form_state, 1, 'default', function ($form_state) {
        return $form_state->getValue(['settings', 'background_options']) === self::KEY_OPTION_VIDEO;
      }
    );
    // Convert the wrapping container to a details element.
    $form['background_video']['#type'] = 'details';
    $form['background_video']['#title'] = $this->t('Video');
    $form['background_video']['#open'] = TRUE;
    $form['background_video']['#states'] = [
      'visible' => [
        ':input[name="settings[background_options]"]' => ['value' => self::KEY_OPTION_VIDEO],
      ],
      'required' => [
        ':input[name="settings[background_options]"]' => ['value' => self::KEY_OPTION_VIDEO],
      ],
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#maxlength' => 255,
      '#default_value' => $this->configuration['description'] ?? '',
      '#required' => in_array($type_for_validation, [
        self::KEY_OPTION_DEFAULT,
      ]),
      '#states' => [
        'required' => [
          [
            ':input[name="settings[background_options]"]' => ['value' => self::KEY_OPTION_DEFAULT],
          ],
        ],
      ],
    ];

    $form['text_color'] = [
      '#type' => 'radios',
      '#title' => $this->t('Text color'),
      '#options' => $this->getTextColorOptions(),
      '#default_value' => $this->configuration['text_color'] ?? NULL,
    ];

    $form['text_color_other'] = [
      '#type' => 'jquery_colorpicker',
      '#title' => $this->t('Custom text color'),
      '#default_value' => $this->configuration['text_color_other'] ?? NULL,
      '#states' => [
        'visible' => [
          [':input[name="settings[text_color]"]' => ['value' => self::KEY_OPTION_OTHER_COLOR]],
        ],
      ],
    ];

    return $form;
  }

  /**
   * Get text color.
   *
   * @return string
   *   Color hex value.
   */
  private function getTextColor() {
    $color_option = $this->configuration['text_color'];
    $color = NULL;
    if ($color_option == self::KEY_OPTION_OTHER_COLOR) {
      $color = $this->configuration['text_color_other'];
    }
    else {
      $color = $this->themeConfiguratorParser
        ->getSettingValue($color_option);
    }
    return '#' . $color;
  }

  /**
   * Get text color options.
   *
   * @return array
   *   Options.
   */
  private function getTextColorOptions() {
    return [
      'color_a' => 'Color A',
      'color_b' => 'Color B',
      'color_c' => 'Color C',
      'color_d' => 'Color D',
      'color_e' => 'Color E',
      'color_f' => 'Color F',
      self::KEY_OPTION_OTHER_COLOR => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['eyebrow'] = $form_state->getValue('eyebrow');
    $this->configuration['title'] = $form_state->getValue('title');
    $this->configuration['description'] = $form_state->getValue('description');
    $this->configuration['text_color'] = $form_state->getValue('text_color');
    $this->configuration['text_color_other'] = $form_state->getValue('text_color_other');
    $this->configuration['background_options'] = $form_state->getValue('background_options');
    $this->configuration['background_image'] = $this->getEntityBrowserValue($form_state, 'background_image');
    $this->configuration['background_video'] = $this->getEntityBrowserValue($form_state, 'background_video');
  }

}
