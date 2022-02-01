<?php

namespace Drupal\mars_common;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\file\Element\ManagedFile;
use Drupal\mars_common\Element\OverrideFile;

/**
 * Class ThemeConfiguratorService is responsible for theme BE logic.
 *
 * @package Drupal\mars_common
 */
class ThemeConfiguratorService {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  const FONT_FIELDS = [
    'headline_font',
    'primary_font',
    'secondary_font',
  ];

  const BORDER_STYLE_REPEAT = 'repeat';

  const BORDER_STYLE_STRETCH = 'stretch';

  const LETTERSPACING_MOBILE_DEFAULT = '0.048rem';

  const LETTERSPACING_TABLET_DEFAULT = '0.07rem';

  const LETTERSPACING_DESKTOP_DEFAULT = '0.16rem';

  /**
   * The image factory service.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  private $imageFactory;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  private $moduleHandler;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ThemeConfiguratorService constructor.
   *
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The image factory service.
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The module handler service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    ImageFactory $image_factory,
    ModuleHandler $module_handler,
    FileSystemInterface $file_system,
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->imageFactory = $image_factory;
    $this->moduleHandler = $module_handler;
    $this->fileSystem = $file_system;
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get color data.
   */
  protected function getColorData(string $key, array $config = NULL) {
    return $this->getData('color_settings', $key, $config);
  }

  /**
   * Get font data.
   */
  protected function getFontData(string $key, array $config = NULL) {
    return $this->getData('font_settings', $key, $config);
  }

  /**
   * Get font data.
   */
  protected function getLogoAltData(string $key, array $config = NULL) {
    return $this->getData('logo', $key, $config);
  }

  /**
   * Get icon settings data.
   */
  protected function getIconSettingsData(string $key, array $config = NULL) {
    return $this->getData('icons_settings', $key, $config);
  }

  /**
   * Get data from the passed config array or from current theme.
   */
  protected function getData(string $subject, string $key, array $config = NULL) {
    return !empty($config[$subject][$key]) ? $config[$subject][$key] : theme_get_setting($key);
  }

  /**
   * Get theme configurator form.
   *
   * @param array $form
   *   Base form array where we add the theme config form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state for the form.
   * @param array|null $config
   *   The current config that we want to use for default values.
   *
   * @return array
   *   The array with added theme config form parts.
   */
  public function getThemeConfiguratorForm(
    array &$form,
    FormStateInterface $form_state,
    array $config = NULL
  ) {
    global $base_url;
    $form_storage = $form_state->getStorage();
    $social_settings = !empty($config['social']) ? $config['social'] : theme_get_setting('social');
    // Init social form elements.
    if (!isset($form_storage['social'])) {
      if (isset($social_settings) && count($social_settings) > 0) {
        $form_storage['social'] = $social_settings;
      }
      else {
        $form_storage['social'] = [];
      }
    }
    // Process multiple social links with form_state.
    $triggered = $form_state->getTriggeringElement();

    if (isset($triggered['#parents']) && in_array('remove_social', $triggered['#parents'], TRUE)) {
      $removed_key = array_key_last($triggered['#parents']);
      if (is_int($removed_key)) {
        unset($form_storage['social'][$triggered['#parents'][$removed_key - 1]]);
      }
    }
    if (isset($triggered['#parents']) && in_array('add_social', $triggered['#parents'], TRUE)) {
      array_push($form_storage['social'], [
        'icon' => '',
        'link' => '',
        'name' => '',
      ]);
    }
    $form_state->setStorage($form_storage);

    if ($this->isPluginBlock($form)) {
      $form['logo'] = [
        '#type' => 'details',
        '#title' => $this->t('Logo image'),
        '#open' => TRUE,
      ];
      $form['logo']['settings']['logo_path'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Path to custom logo'),
        '#default_value' => !empty($config['logo_path']) ? $config['logo_path'] : \theme_get_setting('logo.path'),
      ];
      $form['logo']['settings']['logo_upload'] = [
        '#type' => 'file',
        '#title' => $this->t('Upload logo image'),
        '#maxlength' => 40,
        '#description' => $this->t("If you don't have direct file access to the server,
        use this field to upload your logo."),
        '#upload_validators' => [
          'file_validate_is_image' => [],
        ],
        '#process' => [
          [OverrideFile::class, 'processFile'],
        ],
      ];
    }

    $form['logo']['settings']['logo_alt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Alternative image text'),
      '#default_value' => $this->getLogoAltData('logo_alt', $config),
    ];

    $form['color_settings'] = [
      '#type'        => 'details',
      '#title'       => $this->t('Color settings'),
      '#open'        => TRUE,
      '#description' => $this->t("MARS theme settings for color pallete."),
    ];

    $form['color_settings']['color_a'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Color A'),
      '#default_value' => $this->getColorData('color_a', $config),
      '#description'   => $this->t('Primary Color. Will be used as a main color
      throughout the site. Must be AA compliant.'),
    ];

    $form['color_settings']['color_b'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Color B'),
      '#default_value' => $this->getColorData('color_b', $config),
      '#description'   => $this->t('Secondary Color. Will be used as a main color
      throughout the site. Must be AA compliant.'),
    ];

    $form['color_settings']['color_c'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Color C'),
      '#default_value' => $this->getColorData('color_c', $config),
      '#description'   => $this->t('Includes the option to select a radial
      gradient variation (white in the center, assigned color on the outside)
       or keep the default flat color. Accent Color. Will be used for visual accents
       throughout the site. Must be AA compliant.'),
    ];

    $form['color_settings']['color_d'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Color D'),
      '#default_value' => $this->getColorData('color_d', $config),
      '#description'   => $this->t('Accent Color. Will be used for visual accents
      throughout the site. Must be AA compliant.'),
    ];

    $form['color_settings']['color_e'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Color E'),
      '#default_value' => $this->getColorData('color_e', $config),
      '#description'   => $this->t('Accent Color. Will be used for visual accents
       throughout the site. Must be AA compliant.'),
    ];

    $form['color_settings']['color_f'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Color F'),
      '#default_value' => $this->getColorData('color_f', $config),
      '#description'   => $this->t('Accent Color. Will be used for visual accents
      throughout the site. Must be AA compliant.'),
    ];

    $form['color_settings']['header'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Header Colors'),
    ];

    $form['color_settings']['header']['top_nav'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Header color'),
      '#default_value' => $this->getColorData('top_nav', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to color C.'),
    ];

    $form['color_settings']['header']['top_nav_gradient'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Header gradient color'),
      '#default_value' => $this->getColorData('top_nav_gradient', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to Header color.'),
    ];

    $form['color_settings']['footer'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Footer Colors'),
    ];

    $form['color_settings']['footer']['footer_top'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Top part of footer color'),
      '#default_value' => $this->getColorData('footer_top', $config),
      '#description'   => $this->t('If this field is left empty, it falls back to header color.'),
      '#attributes' => ['class' => ['show-clear']],
    ];

    $form['color_settings']['footer']['footer_top_gradient'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Top part of footer gradient.'),
      '#default_value' => $this->getColorData('footer_top_gradient', $config),
      '#description'   => $this->t('If this field is left empty, it falls back to the top part of the footer color.  If top part of the footer is empty, it falls back to the header gradient color.  If the header gradient color is empty, it falls back to the header color.'),
      '#attributes' => ['class' => ['show-clear']],
    ];

    $form['color_settings']['footer']['bottom_nav'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Bottom part of the footer'),
      '#default_value' => $this->getColorData('bottom_nav', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to Color A.'),
    ];

     // Language and region selector color settings.
     $form['color_settings']['language_region_selector'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Language and region selector Settings'),
    ];

    $form['color_settings']['language_region_selector']['language_region_selector_text_color'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Language and region selector text color'),
      '#default_value' => $this->getColorData('language_region_selector_text_color', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to Color A.'),
    ];

    // Product filter color settings.
    $form['color_settings']['product_filter'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Product filter Settings'),
    ];

    $form['color_settings']['product_filter']['product_filter_arrow_color'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Product filter arrow color'),
      '#default_value' => $this->getColorData('product_filter_arrow_color', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to Color B.'),
    ];

    $form['color_settings']['product_filter']['product_filter_clearall_color'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Product filter clearall text color'),
      '#default_value' => $this->getColorData('product_filter_clearall_color', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to Color B.'),
    ];

    $form['color_settings']['product_filter']['product_filter_tickmark_color'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Product filter tickmark color'),
      '#default_value' => $this->getColorData('product_filter_tickmark_color', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to Color D.'),
    ];

    // Entry gate color settings.
    $form['color_settings']['entrygate_banner_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Entry Gate Settings'),
    ];

    $form['color_settings']['entrygate_banner_settings']['entrygate_background_color'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Entry gate background color'),
      '#default_value' => $this->getColorData('entrygate_background_color', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to Color D.'),
    ];

    $form['color_settings']['entrygate_banner_settings']['entrygate_title_color'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Entry gate title color'),
      '#default_value' => $this->getColorData('entrygate_title_color', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to Color A.'),
    ];

    $form['color_settings']['entrygate_banner_settings']['entrygate_text_color'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Entry gate text color'),
      '#default_value' => $this->getColorData('entrygate_text_color', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to Color A.'),
    ];

    $form['color_settings']['entrygate_banner_settings']['entrygate_date_color'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Entry gate date color'),
      '#default_value' => $this->getColorData('entrygate_date_color', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to Color A.'),
    ];

    $form['color_settings']['entrygate_banner_settings']['entrygate_alert_color'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Entry gate alert message color'),
      '#default_value' => $this->getColorData('entrygate_alert_color', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to Color B.'),
    ];

    $form['color_settings']['cookie_banner_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Cookie Banner Settings'),
    ];

    $form['color_settings']['cookie_banner_settings']['cookie_banner'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Outer gradient of the cookie banner'),
      '#default_value' => $this->getColorData('cookie_banner', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to Color B.'),
    ];

    $form['color_settings']['cookie_banner_settings']['cookie_banner_gradient'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Inner gradient of the cookie banner'),
      '#default_value' => $this->getColorData('cookie_banner_gradient', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to Color B.'),
    ];

    $form['color_settings']['cookie_banner_settings']['cookie_banner_text'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Cookie banner text color'),
      '#default_value' => $this->getColorData('cookie_banner_text', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to Color A.'),
    ];

    $form['color_settings']['cookie_banner_settings']['cookie_banner_close'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Cookie banner close icon color'),
      '#default_value' => $this->getColorData('cookie_banner_close', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to cookie banner text color.'),
    ];

    $form['color_settings']['cookie_banner_settings']['cookie_banner_brand_border'] = [
      '#type' => 'checkbox',
      '#title'         => $this->t('Show brand border on cookie banner'),
      '#default_value' => $this->getData('cookie_banner_settings', 'cookie_banner_brand_border', $config),
    ];

    $form['color_settings']['cookie_banner_settings']['cookie_banner_override'] = [
      '#type' => 'checkbox',
      '#title'         => $this->t('Enable default cookie banner'),
      '#default_value' => $this->getData('cookie_banner_settings', 'cookie_banner_override', $config),
    ];

    $form['color_settings']['card'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Card Colors'),
    ];

    $form['color_settings']['card']['card_background'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Card Background'),
      '#default_value' => $this->getColorData('card_background', $config),
      '#description'   => $this->t('To set the outer gradient, use this form: <a href="@url">here</a>.', ['@url' => $GLOBALS['base_url'] . '/admin/config/card-color-settings']),
    ];

    $form['color_settings']['card']['card_title'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Card Title'),
      '#default_value' => $this->getColorData('card_title', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to Color A.'),
    ];

    $form['color_settings']['card']['card_eyebrow'] = [
      '#type'          => 'jquery_colorpicker',
      '#title'         => $this->t('Card Eyebrow'),
      '#default_value' => $this->getColorData('card_eyebrow', $config),
      '#attributes' => ['class' => ['show-clear']],
      '#description'   => $this->t('If this field is left empty, it falls back to Color A.'),
    ];

    $form['font_settings'] = [
      '#type'        => 'details',
      '#title'       => $this->t('Theme font settings'),
      '#open'        => TRUE,
      '#description' => $this->t("MARS theme settings for font upload."),
    ];

    $headline_font_path = $this->getFontData('headline_font_path', $config);

    $form['font_settings']['headline_font_path'] = [
      '#type'  => 'textfield',
      '#title' => $this->t('Path to Headline Campaign Typeface'),
      '#default_value'   => $headline_font_path,
    ];

    $form['font_settings']['headline_font'] = [
      '#type'              => 'file',
      '#title'             => $this->t('Headline Campaign Typeface'),
      '#upload_location' => 'public://theme_config/',
      '#upload_validators' => [
        'file_validate_extensions' => ['woff ttf'],
      ],
      '#process'         => [
        [OverrideFile::class, 'processFile'],
      ],
    ];
    $headline_font_mobile_letterspacing = $this->getFontData(
      'headline_font_mobile_letterspacing',
      $config
    );
    $form['font_settings']['headline_font_mobile_letterspacing'] = [
      '#type'  => 'textfield',
      '#title' => $this->t('Letter-spacing value for mobile devices'),
      '#default_value'   => $headline_font_mobile_letterspacing ?? self::LETTERSPACING_MOBILE_DEFAULT,
    ];
    $headline_font_tablet_letterspacing = $this->getFontData(
      'headline_font_tablet_letterspacing',
      $config
    );
    $form['font_settings']['headline_font_tablet_letterspacing'] = [
      '#type'  => 'textfield',
      '#title' => $this->t('Letter-spacing value for tablet devices'),
      '#default_value'   => $headline_font_tablet_letterspacing ?? self::LETTERSPACING_TABLET_DEFAULT,
    ];
    $headline_font_desktop_letterspacing = $this->getFontData(
      'headline_font_desktop_letterspacing',
      $config
    );
    $form['font_settings']['headline_font_desktop_letterspacing'] = [
      '#type'  => 'textfield',
      '#title' => $this->t('Letter-spacing value for desktop devices'),
      '#default_value'   => $headline_font_desktop_letterspacing ?? self::LETTERSPACING_DESKTOP_DEFAULT,
    ];

    $primary_font_path = $this->getFontData('primary_font_path', $config);

    $form['font_settings']['primary_font_path'] = [
      '#type'  => 'textfield',
      '#title' => $this->t('Path to Primary Typeface'),
      '#default_value'   => $primary_font_path,
    ];

    $form['font_settings']['primary_font'] = [
      '#type'              => 'file',
      '#title'             => $this->t('Primary Typeface'),
      '#upload_location' => 'public://theme_config/',
      '#upload_validators' => [
        'file_validate_extensions' => ['woff ttf'],
      ],
      '#process'         => [
        [OverrideFile::class, 'processFile'],
      ],
    ];
    $primary_font_mobile_letterspacing = $this->getFontData(
      'primary_font_mobile_letterspacing',
      $config
    );
    $form['font_settings']['primary_font_mobile_letterspacing'] = [
      '#type'  => 'textfield',
      '#title' => $this->t('Letter-spacing value for mobile devices'),
      '#default_value'   => $primary_font_mobile_letterspacing ?? self::LETTERSPACING_MOBILE_DEFAULT,
    ];
    $primary_font_tablet_letterspacing = $this->getFontData(
      'primary_font_tablet_letterspacing',
      $config
    );
    $form['font_settings']['primary_font_tablet_letterspacing'] = [
      '#type'  => 'textfield',
      '#title' => $this->t('Letter-spacing value for tablet devices'),
      '#default_value'   => $primary_font_tablet_letterspacing ?? self::LETTERSPACING_TABLET_DEFAULT,
    ];
    $primary_font_desktop_letterspacing = $this->getFontData(
      'primary_font_desktop_letterspacing',
      $config
    );
    $form['font_settings']['primary_font_desktop_letterspacing'] = [
      '#type'  => 'textfield',
      '#title' => $this->t('Letter-spacing value for desktop devices'),
      '#default_value'   => $primary_font_desktop_letterspacing ?? self::LETTERSPACING_DESKTOP_DEFAULT,
    ];

    $secondary_font_path = $this->getFontData('secondary_font_path', $config);

    $form['font_settings']['secondary_font_path'] = [
      '#type'  => 'textfield',
      '#title' => $this->t('Path to Secondary Typeface'),
      '#default_value'   => $secondary_font_path,
    ];

    $form['font_settings']['secondary_font'] = [
      '#type'              => 'file',
      '#title'             => $this->t('Secondary Typeface'),
      '#upload_location' => 'public://theme_config/',
      '#upload_validators' => [
        'file_validate_extensions' => ['woff ttf'],
      ],
      '#process'         => [
        [OverrideFile::class, 'processFile'],
      ],
    ];
    $secondary_font_mobile_letterspacing = $this->getFontData(
      'secondary_font_mobile_letterspacing',
      $config
    );
    $form['font_settings']['secondary_font_mobile_letterspacing'] = [
      '#type'  => 'textfield',
      '#title' => $this->t('Letter-spacing value for mobile devices'),
      '#default_value'   => $secondary_font_mobile_letterspacing ?? self::LETTERSPACING_MOBILE_DEFAULT,
    ];
    $secondary_font_tablet_letterspacing = $this->getFontData(
      'secondary_font_tablet_letterspacing',
      $config
    );
    $form['font_settings']['secondary_font_tablet_letterspacing'] = [
      '#type'  => 'textfield',
      '#title' => $this->t('Letter-spacing value for tablet devices'),
      '#default_value'   => $secondary_font_tablet_letterspacing ?? self::LETTERSPACING_TABLET_DEFAULT,
    ];
    $secondary_font_desktop_letterspacing = $this->getFontData(
      'secondary_font_desktop_letterspacing',
      $config
    );
    $form['font_settings']['secondary_font_desktop_letterspacing'] = [
      '#type'  => 'textfield',
      '#title' => $this->t('Letter-spacing value for desktop devices'),
      '#default_value'   => $secondary_font_desktop_letterspacing ?? self::LETTERSPACING_DESKTOP_DEFAULT,
    ];

    $form['favicon']['default_favicon']['#type'] = 'hidden';
    $form['favicon']['default_favicon']['#value'] = FALSE;
    if (isset($form['favicon']['settings']['favicon_upload'])) {
      $form['favicon']['settings']['favicon_upload']['#description'] .= $this->t('<br /> Recommended image size for favicons is 64x64px.');
    }

    $form['logo']['default_logo']['#type'] = 'hidden';
    $form['logo']['default_logo']['#value'] = FALSE;
    if (isset($form['logo']['settings']['logo_upload'])) {
      $form['logo']['settings']['logo_upload']['#description'] .= $this->t('<br /> The recommended image size should be “minimum width for wide logos: 212px, minimum height for tall logos: 95px”');
    }
    $form['icons_settings'] = [
      '#type'        => 'details',
      '#title'       => $this->t('Theme images settings'),
      '#open'        => TRUE,
      '#description' => $this->t("MARS theme settings for icons/images upload."),
    ];

    $form['icons_settings']['graphic_divider'] = [
      '#title'           => $this->t('Graphic Divider'),
      '#type'            => 'managed_file',
      '#description'     => $this->t('Will be designed by each brand team.
      Size and format requirements detailed out in the Style Guide.'),
      '#upload_location' => 'public://theme_config/',
      '#required'        => FALSE,
      '#process'         => [
        [ManagedFile::class, 'processManagedFile'],
        [$this, 'processImageWidget'],
      ],
      '#upload_validators' => [
        'file_validate_extensions' => ['svg'],
      ],
      '#theme'               => 'image_widget',
      '#preview_image_style' => 'medium',
      '#default_value'       => $this->getIconSettingsData('graphic_divider', $config),
    ];

    $form['icons_settings']['brand_shape'] = [
      '#title'           => $this->t('Path to Brand Shape'),
      '#type'            => 'managed_file',
      '#description'     => $this->t('Will be designed by each brand team.
      Size and format requirements detailed out in the Style Guide.'),
      '#upload_location' => 'public://theme_config/',
      '#required'        => FALSE,
      '#process'         => [
        [ManagedFile::class, 'processManagedFile'],
        [$this, 'processImageWidget'],
      ],
      '#upload_validators' => [
        'file_validate_extensions' => ['svg'],
      ],
      '#theme'               => 'image_widget',
      '#preview_image_style' => 'medium',
      '#default_value'       => $this->getIconSettingsData('brand_shape', $config),
    ];

    $form['icons_settings']['brand_borders'] = [
      '#title'           => $this->t('Brand Borders'),
      '#type'            => 'managed_file',
      '#description'     => $this->t('Will be designed by each brand team.
       Size and format requirements detailed out in the Style Guide.'),
      '#upload_location' => 'public://theme_config/',
      '#required'        => FALSE,
      '#process'         => [
        [ManagedFile::class, 'processManagedFile'],
        [$this, 'processImageWidget'],
      ],
      '#upload_validators' => [
        'file_validate_extensions' => ['svg'],
      ],
      '#theme'               => 'image_widget',
      '#preview_image_style' => 'medium',
      '#default_value'       => $this->getIconSettingsData('brand_borders', $config),
    ];
    $image_path = $base_url . '/themes/custom/emulsifymars/images/';

    $form['icons_settings']['brand_border_style'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Brand border style'),
      '#description'   => $this->t('Designates stretched border or repeated border shape.'),
      '#default_value' => $this->getIconSettingsData('brand_border_style', $config),
      '#options' => [
        self::BORDER_STYLE_REPEAT => $this->t('Repeat'),
        self::BORDER_STYLE_STRETCH => $this->t('Stretch'),
      ],
      '#markup' => '<b>Example</b>: <br /><br />Repeat <img src="' . $image_path . 'border_style_repeat.svg' . '"/><br />Stretch <img src="' . $image_path . 'border_style_stretch.svg' . '"/>',
    ];

    $form['icons_settings']['brand_borders_2'] = [
      '#title'           => $this->t('Secondary Brand Border'),
      '#type'            => 'managed_file',
      '#description'     => $this->t('Will be designed by each brand team.
      Size and format requirements detailed out in the Style Guide.'),
      '#upload_location' => 'public://theme_config/',
      '#required'        => FALSE,
      '#process'         => [
        [ManagedFile::class, 'processManagedFile'],
        [$this, 'processImageWidget'],
      ],
      '#upload_validators' => [
        'file_validate_extensions' => ['svg'],
      ],
      '#theme'               => 'image_widget',
      '#preview_image_style' => 'medium',
      '#default_value'       => $this->getIconSettingsData('brand_borders_2', $config),
    ];

    $form['icons_settings']['png_asset'] = [
      '#title'           => $this->t('PNG Asset'),
      '#type'            => 'managed_file',
      '#description'     => $this->t('Will be designed by each brand team.
      Size and format requirements detailed out in the Style Guide. <br /> Recommended image size : Minimum width 375px.'),
      '#upload_location' => 'public://theme_config/',
      '#required'        => FALSE,
      '#process'         => [
        [ManagedFile::class, 'processManagedFile'],
        [$this, 'processImageWidget'],
      ],
      '#upload_validators' => [
        'file_validate_extensions' => ['svg png'],
      ],
      '#theme'               => 'image_widget',
      '#preview_image_style' => 'medium',
      '#default_value'       => $this->getIconSettingsData('png_asset', $config),
    ];

    $form['icons_settings']['button_style'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Button/Card Style'),
      '#description'   => $this->t('Designates rounded buttons or sharp corner buttons and card corner.'),
      '#default_value' => $this->getIconSettingsData('button_style', $config),
      '#options' => [
        0 => $this->t('Round'),
        1 => $this->t('Sharp'),
      ],
    ];

    $form['social'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => $this->t('Theme social link settings'),
      '#description' => $this->t("MARS theme settings for icons/images upload."),
      '#prefix' => '<div id="social">',
      '#suffix' => '</div>',
    ];

    if (isset($form_storage['social'])) {
      foreach ($form_storage['social'] as $key => $value) {
        $form['social'][$key] = [
          '#type' => 'fieldset',
          '#tree' => TRUE,
        ];
        $form['social'][$key]['icon'] = [
          '#title' => $this->t('Social network icon'),
          '#type' => 'managed_file',
          '#upload_location' => 'public://theme_config/',
          '#required' => TRUE,
          '#process' => [
            [ManagedFile::class, 'processManagedFile'],
            [$this, 'processImageWidget'],
          ],
          '#upload_validators' => [
            'file_validate_extensions' => ['svg'],
          ],
          '#theme' => 'image_widget',
          '#preview_image_style' => 'thumbnail',
          '#default_value' => $value['icon'],
        ];
        $form['social'][$key]['link'] = [
          '#title'         => $this->t('Social network link'),
          '#type'          => 'textfield',
          '#required'      => TRUE,
          '#default_value' => $value['link'],
        ];
        $form['social'][$key]['name'] = [
          '#title'         => $this->t('Social network title'),
          '#type'          => 'textfield',
          '#required'      => TRUE,
          '#default_value' => $value['name'],
        ];
        $form['social'][$key]['remove_social'] = [
          '#type'  => 'button',
          '#name' => 'social_' . $key,
          '#value' => $this->t('Remove social link'),
          '#limit_validation_errors' => [],
          '#ajax'  => [
            'callback' => [$this, 'themeSettingsAjaxRemoveSocial'],
            'wrapper' => 'social',
          ],
        ];
      }
    }

    $form['social']['add_social'] = [
      '#type' => 'button',
      '#value' => $this->t('Add new social link'),
      '#href' => '',
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [$this, 'themeSettingsAjaxAddSocial'],
        'wrapper' => 'social',
      ],
    ];

    if (!$this->isPluginBlock($form)) {
      $form['product_layout'] = [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $this->t('Product layout settings'),
        '#description' => $this->t("MARS theme settings for Product layout."),
      ];
      $form['product_layout']['show_allergen_info'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Show allergen info'),
        '#default_value' => $this->getData('product_layout', 'show_allergen_info', $config),
      ];
      $form['product_layout']['show_cooking_info'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Show cooking (instructions) info'),
        '#default_value' => $this->getData('product_layout', 'show_cooking_info', $config),
      ];
      $form['product_layout']['show_nutrition_info'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Show nutrition (table) info'),
        '#default_value' => $this->getData('product_layout', 'show_nutrition_info', $config) ?? TRUE,
      ];
      $form['product_layout']['show_nutrition_claims_benefits'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Show Nutrition Claims and benefits info'),
        '#default_value' => $this->getData('product_layout', 'show_nutrition_claims_benefits', $config) ?? FALSE,
      ];

      $form['card_grid'] = [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $this->t('Card grid settings'),
        '#description' => $this->t("MARS theme settings for card grid"),
      ];
      $form['card_grid']['facets_text_transform'] = [
        '#type' => 'select',
        '#title' => $this->t('Filter facet name styling'),
        '#options' => [
          'capitalize' => $this->t('Capitalized'),
          'uppercase' => $this->t('Uppercased'),
        ],
        '#default_value' => $this->getData('card_grid', 'facets_text_transform', $config) ?? 'uppercase',
      ];
    }

    if (!$this->isPluginBlock($form)) {
      $form['#validate'][] = [$this, 'formSystemThemeSettingsValidate'];
      $form['#submit'][] = [$this, 'formSystemThemeSettingsSubmit'];
    }

    return $form;
  }

  /**
   * Process managed_file element to add preview element with uploaded image.
   *
   * @param array $element
   *   Form element children.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param array $complete_form
   *   Processed form.
   *
   * @return array
   *   Form element for further processing and theming
   */
  public function processImageWidget(
    array &$element,
    FormStateInterface $form_state,
    array &$complete_form
  ) {
    if (empty($element['fids']['#value'])) {
      return $element;
    }

    $file = reset($element['#files']);
    $file_variables = [
      'style_name' => $element['#preview_image_style'],
      'uri' => $file->getFileUri(),
    ];

    // Determine image dimensions.
    if (isset($element['#value']['width']) && isset($element['#value']['height'])) {
      $file_variables['width'] = $element['#value']['width'];
      $file_variables['height'] = $element['#value']['height'];
    }
    else {
      $image = $this->imageFactory->get($file->getFileUri());
      if ($image->isValid()) {
        $file_variables['width'] = $image->getWidth();
        $file_variables['height'] = $image->getHeight();
      }
      else {
        $file_variables['width'] = $file_variables['height'] = NULL;
      }
    }

    $element['preview'] = [
      '#weight' => -10,
      '#theme' => 'image_style',
      '#width' => $file_variables['width'],
      '#height' => $file_variables['height'],
      '#style_name' => $file_variables['style_name'],
      '#uri' => $file_variables['uri'],
    ];

    // Store the dimensions in the form so the file doesn't have to be
    // accessed again. This is important for remote files.
    $element['width'] = [
      '#type' => 'hidden',
      '#value' => $file_variables['width'],
    ];
    $element['height'] = [
      '#type' => 'hidden',
      '#value' => $file_variables['height'],
    ];

    return $element;
  }

  /**
   * Add remove social link callback.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   *
   * @return array
   *   Social container of theme settings.
   */
  public function themeSettingsAjaxRemoveSocial(
    array $form,
    FormStateInterface $form_state
  ): array {
    if ($this->isPluginBlock($form)) {
      return $form['settings']['social'];
    }
    else {
      return $form['social'];
    }
  }

  /**
   * Add new social link callback.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   *
   * @return array
   *   Social container of theme settings.
   */
  public function themeSettingsAjaxAddSocial(
    array $form,
    FormStateInterface $form_state
  ): array {
    if ($this->isPluginBlock($form)) {
      return $form['settings']['social'];
    }
    else {
      return $form['social'];
    }
  }

  /**
   * Helper function font fields list.
   *
   * @return array
   *   Return list of font form elements.
   */
  public function getFontFields(): array {
    return self::FONT_FIELDS;
  }

  /**
   * Validate theme settings form.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   */
  public function formSystemThemeSettingsValidate(
    array &$form,
    FormStateInterface $form_state
  ) {
    if ($this->moduleHandler->moduleExists('file')) {
      foreach (self::FONT_FIELDS as $font) {
        $this->fileSaveProcess(
          $form['font_settings'][$font],
          $form_state,
          $font
        );
      }
    }
  }

  /**
   * Helper function for uploading files from settings from.
   *
   * @param array $form_element
   *   Form element to process uploaded file state.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   * @param string $value_name
   *   File value to store.
   */
  private function fileSaveProcess(
    array $form_element,
    FormStateInterface &$form_state,
    string $value_name
  ) {
    $file = _file_save_upload_from_form($form_element, $form_state, 0);
    if ($file) {
      $form_state->setValue($value_name, $file);
    }
  }

  /**
   * Submit theme settings form.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   */
  public function formSystemThemeSettingsSubmit(
    array &$form,
    FormStateInterface $form_state
  ) {
    $config = $this->configFactory->get('system.file');
    $default_scheme = $config->get('default_scheme');
    foreach (self::FONT_FIELDS as $font) {
      $this->fileStoreProcess($form_state, $font, $default_scheme);
    }
    $this->fileStatusProcess($form, $form_state);
  }

  /**
   * Helper function to set permanent state for all file entities.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   */
  private function fileStatusProcess(
    array $form,
    FormStateInterface &$form_state
  ) {
    $values = $form_state->getUserInput();
    foreach ($values as $value) {
      if (!is_array($value) || !array_key_exists('fids', $value)) {
        continue;
      }
      $file = $this->entityTypeManager->getStorage('file')->load($value['fids']);
      if ($file && $file->isTemporary()) {
        $file->setPermanent();
        $file->save();
      }
    }
    if (!array_key_exists('social', $values)) {
      return;
    }
    foreach ($values['social'] as $social) {
      if (array_key_exists('icon', $social) && array_key_exists('fids', $social['icon'])) {
        $file = $this->entityTypeManager->getStorage('file')->load($social['icon']['fids']);
        if ($file && $file->isTemporary()) {
          $file->setPermanent();
          $file->save();
        }
      }
    }
  }

  /**
   * Helper function to store file location in config.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   * @param string $value_name
   *   File upload value.
   * @param string $default_scheme
   *   Default file scheme.
   */
  private function fileStoreProcess(
    FormStateInterface &$form_state,
    string $value_name,
    string $default_scheme
  ) {
    $values = $form_state->getValues();
    try {
      if (!empty($values[$value_name])) {
        $filename = $this->fileSystem->copy($values[$value_name]->getFileUri(),
          $default_scheme . '://');
        $form_state->setValue($value_name, '');
        $form_state->setValue($value_name . '_path',
          file_create_url($filename));
      }
    }
    catch (FileException $e) {
      // Ignore.
    }
  }

  /**
   * Check provider is plugin block.
   *
   * @param array $form
   *   The form array.
   *
   * @return bool
   *   The result.
   */
  private function isPluginBlock(array $form) {
    $is_plugin_block = FALSE;
    if ((isset($form['settings']) &&
        isset($form['settings']['#class_provider']) &&
        $form['settings']['#class_provider'] instanceof BlockPluginInterface) ||
      (isset($form['#class_provider']) &&
        $form['#class_provider'] instanceof BlockPluginInterface)) {
      $is_plugin_block = TRUE;
    }
    return $is_plugin_block;
  }

}
