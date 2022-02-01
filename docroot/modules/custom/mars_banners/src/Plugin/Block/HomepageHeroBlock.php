<?php

namespace Drupal\mars_banners\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Homepage hero Block.
 *
 * @Block(
 *   id = "homepage_hero_block",
 *   admin_label = @Translation("MARS: Homepage Hero block"),
 *   category = @Translation("Global elements"),
 * )
 */
class HomepageHeroBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use EntityBrowserFormTrait;

  /**
   * Lighthouse entity browser image id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID = 'lighthouse_browser';

  /**
   * Lighthouse entity browser video id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_VIDEO_ID = 'lighthouse_video_browser';

  /**
   * Key option image.
   */
  const KEY_OPTION_DEFAULT = 'default';

  /**
   * Key option video.
   */
  const KEY_OPTION_VIDEO = 'video';

  /**
   * Key option video loop.
   */
  const KEY_OPTION_VIDEO_LOOP = 'video_loop';

  /**
   * Key option image.
   */
  const KEY_OPTION_IMAGE = 'image';

  /**
   * Key option image + text.
   */
  const KEY_OPTION_IMAGE_AND_TEXT = 'image_and_text';

  /**
   * Mars Media Helper service.
   *
   * @var \Drupal\mars_media\MediaHelper
   */
  protected $mediaHelper;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * Service for dealing with theme configs.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  private $themeConfigParser;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MediaHelper $media_helper,
    LanguageHelper $language_helper,
    ThemeConfiguratorParser $theme_config_parser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mediaHelper = $media_helper;
    $this->languageHelper = $language_helper;
    $this->themeConfigParser = $theme_config_parser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_media.media_helper'),
      $container->get('mars_common.language_helper'),
      $container->get('mars_common.theme_configurator_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $build['#label'] = $this->languageHelper->translate($config['label']);
    $build['#eyebrow'] = $this->languageHelper->translate($config['eyebrow']);
    $build['#title_url'] = $config['title']['url'];
    $build['#title_label'] = $this->languageHelper->translate($config['title']['label']);
    $title_override = !empty($config['title']['next_line_label']['value']) ? $config['title']['next_line_label']['value'] : '';
    $build['#title_label_override'] = $this->languageHelper->translate($title_override);
    $build['#cta_url'] = ['href' => $config['cta']['url']];
    $build['#cta_title'] = $this->languageHelper->translate($config['cta']['title']);
    $build['#block_type'] = $config['block_type'];
    $build['#background_assets'] = $this->getBgAssets();
    $background_color = !empty($this->configuration['use_background_color']) && !empty($this->configuration['background_color']) ?
      $this->configuration['background_color'] : '';
    $build['#background_color'] = $background_color;
    $build['#brand_shape'] = $this->themeConfigParser->getBrandShapeWithoutFill();
    $build['#dark_overlay'] = $this->configuration['use_dark_overlay'] ?? TRUE;
    $build['#hide_volume'] = $this->configuration['hide_volume'] ?? FALSE;

    if (!empty($config['card'])) {
      foreach ($config['card'] as $key => $card) {
        $build['#blocks'][$key]['eyebrow'] = $this->languageHelper->translate($card['eyebrow']);
        $build['#blocks'][$key]['title_label'] = $this->languageHelper->translate($card['title']['label']);
        $build['#blocks'][$key]['title_href'] = $card['title']['url'];
        $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($card['foreground_image']);
        $media_data = $this->mediaHelper->getMediaParametersById($media_id);
        if (!isset($media_data['error']) || $media_data['error'] !== TRUE) {
          $file_url = $media_data['src'];
          $format = '%s 375w, %s 768w, %s 1024w, %s 1440w';
          $default_alt = $this->languageHelper->translate('Homepage hero 3up image');
          $build['#blocks'][$key]['image'][] = [
            'srcset' => sprintf($format, $file_url, $file_url, $file_url,
              $file_url),
            'src' => $file_url,
            'class' => 'block1-small',
            'alt' => !empty($media_data['alt']) ? $media_data['alt'] : $default_alt,
            'title' => !empty($media_data['title']) ? $media_data['title'] : $default_alt,
          ];
        }

        $build['#blocks'][$key]['cta'][] = [
          'title' => $this->languageHelper->translate($card['cta']['title']),
          'link_attributes' => [
            [
              'href' => $card['cta']['url'],
            ],
          ],
        ];
      }
    }

    $build['#theme'] = 'homepage_hero_block';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();
    $block_type_value = $config['block_type'] ?? self::KEY_OPTION_DEFAULT;
    $submitted_input = $form_state->getUserInput()['settings'] ?? [];
    $type_for_validation = $submitted_input['block_type'] ?? $block_type_value;

    $form['block_type'] = [
      '#title' => $this->t('Choose block type'),
      '#type' => 'select',
      '#options' => [
        self::KEY_OPTION_DEFAULT => $this->t('Default'),
        self::KEY_OPTION_IMAGE => $this->t('Image'),
        self::KEY_OPTION_IMAGE_AND_TEXT => $this->t('Image + text'),
        self::KEY_OPTION_VIDEO => $this->t('Video'),
        self::KEY_OPTION_VIDEO_LOOP => $this->t('Video No Text, CTA'),
      ],
      '#default_value' => $block_type_value,
    ];
    $form['eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#maxlength' => 15,
      '#default_value' => $config['eyebrow'] ?? '',
      '#required' => in_array($type_for_validation, [
        self::KEY_OPTION_DEFAULT,
        self::KEY_OPTION_IMAGE,
        self::KEY_OPTION_VIDEO,
      ]),
      '#states' => [
        'invisible' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO_LOOP]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE_AND_TEXT]],
        ],
        'required' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_DEFAULT]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
        ],
      ],
    ];

    $form['custom_foreground_image'] = [
      '#type' => 'details',
      '#title' => $this->t('Custom Foreground Image'),
      '#open' => TRUE,
      '#states' => [
        'visible' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_DEFAULT]],
        ],
      ],
    ];
    $fg_image_default = isset($config['custom_foreground_image']['image']) ? $config['custom_foreground_image']['image'] : NULL;
    $form['custom_foreground_image']['image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID, $fg_image_default, $form_state, 1, 'thumbnail', FALSE);

    $form['title'] = [
      '#type' => 'details',
      '#title' => $this->t('Title'),
      '#open' => TRUE,
      '#states' => [
        'invisible' => [
          ':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO_LOOP],
        ],
      ],
    ];
    $form['title']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title Link URL'),
      '#description' => $this->t('Please check if string starts with: "/", "http://", "https://".'),
      '#maxlength' => 2048,
      '#default_value' => $config['title']['url'] ?? '',
      '#required' => in_array($type_for_validation, [
        self::KEY_OPTION_DEFAULT,
        self::KEY_OPTION_IMAGE,
        self::KEY_OPTION_VIDEO,
      ]),
      '#states' => [
        'invisible' => [
          ':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE_AND_TEXT],
        ],
        'required' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_DEFAULT]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
        ],
      ],
    ];
    $form['title']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title label'),
      '#maxlength' => 55,
      '#description' => $this->t('Default lable for title, if choose override title label option make this field empty.'),
      '#default_value' => $config['title']['label'] ?? '',
      '#states' => [
        'visible' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_DEFAULT]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE_AND_TEXT]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
        ],
      ],
    ];
    // Title override.
    $form['title']['next_line_label'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Override Title label'),
      '#description' => $this->t('Override the default title label by using the html tags.'),
      '#default_value' => $config['title']['next_line_label']['value'] ?? '',
      '#states' => [
        'visible' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_DEFAULT]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE_AND_TEXT]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
        ],
      ],
    ];

    $form['cta'] = [
      '#type' => 'details',
      '#title' => $this->t('CTA'),
      '#open' => TRUE,
      '#states' => [
        'invisible' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO_LOOP]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE_AND_TEXT]],
        ],
      ],
    ];
    $form['cta']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Link URL'),
      '#description' => $this->t('Please check if string starts with: "/", "http://", "https://".'),
      '#maxlength' => 2048,
      '#default_value' => $config['cta']['url'] ?? '',
      '#required' => in_array($type_for_validation, [
        self::KEY_OPTION_DEFAULT,
        self::KEY_OPTION_IMAGE,
        self::KEY_OPTION_VIDEO,
      ]),
      '#states' => [
        'required' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_DEFAULT]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
        ],
      ],
    ];
    $form['cta']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Link Title'),
      '#maxlength' => 15,
      '#default_value' => $config['cta']['title'] ?? 'Explore',
      '#required' => in_array($type_for_validation, [
        self::KEY_OPTION_DEFAULT,
        self::KEY_OPTION_IMAGE,
        self::KEY_OPTION_VIDEO,
      ]),
      '#states' => [
        'required' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_DEFAULT]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
        ],
      ],
    ];

    foreach (MediaHelper::LIST_IMAGE_RESOLUTIONS as $resolution) {
      $name = 'background_image';

      if ($resolution != 'desktop') {
        $name = 'background_image_' . $resolution;
      }

      $image_default = $config[$name] ?? NULL;

      // Entity Browser element for background image.
      $validate_callback = FALSE;
      if ($resolution == 'desktop') {
        $validate_callback = function ($form_state) {
          return in_array($form_state->getValue(['settings', 'block_type']),
            [self::KEY_OPTION_IMAGE, self::KEY_OPTION_IMAGE_AND_TEXT]);
        };
      }

      $form[$name] = $this->getEntityBrowserForm(
        self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID,
        $image_default,
        $form_state,
        1,
        'thumbnail',
        $validate_callback
      );

      // Convert the wrapping container to a details element.
      $form[$name]['#type'] = 'details';
      $form[$name]['#required'] = ($resolution == 'desktop');
      $form[$name]['#title'] = $this->t('Background Image (@resolution)', ['@resolution' => ucfirst($resolution)]);
      $form[$name]['#open'] = TRUE;
      $form[$name]['#states'] = [
        'visible' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE_AND_TEXT]],
        ],
      ];

      if ($resolution != 'desktop') {
        $form[$name]['#description'] = $this->t('Image Alt and Title will be replaced by Desktop image.');
      }
    }

    $video_default = isset($config['background_video']) ? $config['background_video'] : NULL;
    // Entity Browser element for video.
    $form['background_video'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_VIDEO_ID,
      $video_default, $form_state, 1, 'default', function ($form_state) {
        return in_array($form_state->getValue(
          ['settings', 'block_type']),
          [self::KEY_OPTION_VIDEO, self::KEY_OPTION_VIDEO_LOOP]
        );
      }
    );
    // Convert the wrapping container to a details element.
    $form['background_video']['#type'] = 'details';
    $form['background_video']['#title'] = $this->t('Background Video');
    $form['background_video']['#open'] = TRUE;
    $form['background_video']['#states'] = [
      'visible' => [
        [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
        'or',
        [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO_LOOP]],
      ],
      'required' => [
        [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
        'or',
        [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO_LOOP]],
      ],
    ];
    $form['use_background_color'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Background Color Override'),
      '#default_value' => $this->configuration['use_background_color'] ?? FALSE,
      '#states' => [
        'visible' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_DEFAULT]],
        ],
      ],
    ];
    $form['background_color'] = [
      '#type' => 'jquery_colorpicker',
      '#title' => $this->t('Background Color Override'),
      '#default_value' => $this->configuration['background_color'] ?? '',
      '#states' => [
        'visible' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_DEFAULT]],
          'and',
          [':input[name="settings[use_background_color]"]' => ['checked' => TRUE]],
        ],
      ],
    ];
    $form['card'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => $this->t('Setup 3UP variant'),
      '#description' => $this->t('2 additional cards for hero block on homepage.'),
      '#prefix' => '<div id="cards-wrapper">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => 'js-form-wrapper',
      ],
      '#states' => [
        'visible' => [
          ':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE],
        ],
      ],
    ];

    $saved_cards = !empty($config['card']) ? $config['card'] : [];
    $submitted_cards = $submitted_input['card'] ?? [];
    $current_card_state = $form_state->get('card_storage');

    if (empty($current_card_state)) {
      if (!empty($submitted_cards)) {
        $current_card_state = $submitted_cards;
      }
      else {
        $current_card_state = $saved_cards;
      }
    }
    $form_state->set('card_storage', $current_card_state);

    foreach ($current_card_state as $key => $value) {
      $form['card'][$key] = [
        '#type' => 'details',
        '#title' => $this->t('Product card'),
        '#open' => TRUE,
      ];
      $form['card'][$key]['eyebrow'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Card Eyebrow'),
        '#maxlength' => 15,
        '#default_value' => $config['card'][$key]['eyebrow'] ?? '',
        '#required' => in_array($type_for_validation, [
          self::KEY_OPTION_IMAGE,
        ]),
        '#states' => [
          'required' => [
            ':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE],
          ],
        ],
      ];
      $form['card'][$key]['title']['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Card Title label'),
        '#maxlength' => 55,
        '#default_value' => $config['card'][$key]['title']['label'] ?? '',
        '#required' => in_array($type_for_validation, [
          self::KEY_OPTION_IMAGE,
        ]),
        '#states' => [
          'required' => [
            ':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE],
          ],
        ],
      ];
      $form['card'][$key]['title']['url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Card Title Link URL'),
        '#description' => $this->t('Please check if string starts with: "/", "http://", "https://".'),
        '#maxlength' => 2048,
        '#default_value' => $config['card'][$key]['title']['url'] ?? '',
        '#required' => in_array($type_for_validation, [
          self::KEY_OPTION_IMAGE,
        ]),
        '#states' => [
          'required' => [
            ':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE],
          ],
        ],
      ];
      $form['card'][$key]['cta']['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('CTA Link Title'),
        '#maxlength' => 15,
        '#default_value' => $config['card'][$key]['cta']['title'] ?? 'Explore',
        '#required' => in_array($type_for_validation, [
          self::KEY_OPTION_IMAGE,
        ]),
        '#states' => [
          'required' => [
            ':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE],
          ],
        ],
      ];
      $form['card'][$key]['cta']['url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('CTA Link URL'),
        '#description' => $this->t('Please check if string starts with: "/", "http://", "https://".'),
        '#maxlength' => 2048,
        '#default_value' => $config['card'][$key]['cta']['url'] ?? '',
        '#required' => in_array($type_for_validation, [
          self::KEY_OPTION_IMAGE,
        ]),
        '#states' => [
          'required' => [
            ':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE],
          ],
        ],
      ];

      $foreground_default = isset($config['card'][$key]['foreground_image']) ? $config['card'][$key]['foreground_image'] : NULL;
      $form['card'][$key]['foreground_image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID,
        $foreground_default, $form_state, 1, 'thumbnail', FALSE);
      // Convert the wrapping container to a details element.
      $form['card'][$key]['foreground_image']['#type'] = 'details';
      $form['card'][$key]['foreground_image']['#title'] = $this->t('Foreground Image');
      $form['card'][$key]['foreground_image']['#open'] = TRUE;

      $form['card'][$key]['remove_card'] = [
        '#type' => 'submit',
        '#name' => 'card_' . $key,
        '#value' => $this->t('Remove card'),
        '#ajax' => [
          'callback' => [$this, 'ajaxRemoveCardCallback'],
          'wrapper' => 'cards-wrapper',
        ],
        '#submit' => [[$this, 'removeCardSubmitted']],
      ];
    }
    if (count($current_card_state) < 2) {
      $form['card']['add_card'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add card'),
        '#ajax' => [
          'callback' => [$this, 'ajaxAddCardCallback'],
          'wrapper' => 'cards-wrapper',
        ],
        '#limit_validation_errors' => [],
        '#submit' => [[$this, 'addCardSubmitted']],
      ];
    }

    $form['use_dark_overlay'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use dark overlay'),
      '#default_value' => $this->configuration['use_dark_overlay'] ?? TRUE,
      '#states' => [
        'visible' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_DEFAULT]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
          'or',
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE_AND_TEXT]],
        ],
      ],
    ];

    $form['hide_volume'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide Volume'),
      '#default_value' => $this->configuration['hide_volume'] ?? FALSE,
    ];
    return $form;
  }

  /**
   * Add new card link callback.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   *
   * @return array
   *   Card container of configuration settings.
   */
  public function ajaxAddCardCallback(array $form, FormStateInterface $form_state) {
    return $form['settings']['card'];
  }

  /**
   * Add remove card callback.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   *
   * @return array
   *   Card container of configuration settings.
   */
  public function ajaxRemoveCardCallback(array $form, FormStateInterface $form_state) {
    return $form['settings']['card'];
  }

  /**
   * Custom submit card configuration settings form.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   */
  public function addCardSubmitted(
    array $form,
    FormStateInterface $form_state
  ) {
    $storage = $form_state->get('card_storage') ?? [];
    array_push($storage, 1);
    $form_state->set('card_storage', $storage);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Custom submit card configuration settings form.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   */
  public function removeCardSubmitted(
    array $form,
    FormStateInterface $form_state
  ) {
    $triggered = $form_state->getTriggeringElement();
    if (isset($triggered['#parents'][3]) && $triggered['#parents'][3] == 'remove_card') {
      $card_storage = $form_state->get('card_storage');
      $id = $triggered['#parents'][2];
      unset($card_storage[$id]);
      $form_state->set('card_storage', $card_storage);
      $form_state->setRebuild(TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    unset($values['card']['add_card']);
    $this->setConfiguration($values);
    $this->configuration['use_dark_overlay'] = ($values['use_dark_overlay'])
      ? TRUE
      : FALSE;
    $this->configuration['hide_volume'] = ($values['hide_volume'])
      ? TRUE
      : FALSE;

    foreach (MediaHelper::LIST_IMAGE_RESOLUTIONS as $resolution) {
      $name = 'background_image';
      if ($resolution != 'desktop') {
        $name = 'background_image_' . $resolution;
      }

      $this->configuration[$name] = $this->getEntityBrowserValue($form_state, $name);
    }

    $this->configuration['background_video'] = $this->getEntityBrowserValue($form_state, 'background_video');
    $this->configuration['custom_foreground_image']['image'] = $this->getEntityBrowserValue(
      $form_state,
      ['custom_foreground_image', 'image']
    );
    if (isset($values['card']) && !empty($values['card'])) {
      foreach ($values['card'] as $key => $card) {
        $this->configuration['card'][$key]['foreground_image'] = $this->getEntityBrowserValue($form_state, [
          'card',
          $key,
          'foreground_image',
        ]);
      }
    }
  }

  /**
   * Returns the bg image URL or NULL.
   *
   * @return array|null
   *   The bg image url or null of there is none.
   */
  private function getBgAssets(): ?array {
    $config = $this->getConfiguration();
    $bg_image_media_ids = [];
    $assets = [];
    $title = 'homepage hero background image';
    $alt = 'homepage hero background image';

    if (in_array(
      $config['block_type'],
      [self::KEY_OPTION_IMAGE, self::KEY_OPTION_IMAGE_AND_TEXT]
    )) {
      foreach (MediaHelper::LIST_IMAGE_RESOLUTIONS as $resolution) {
        // Generate image field name.
        // NOTE: "background_image" for desktop without any suffixes
        // for compatibility with existing data.
        $name = $resolution == 'desktop' ? 'background_image' : 'background_image_' . $resolution;

        // Set value for each resolution.
        if (!empty($config[$name])) {
          $bg_image_media_ids[$resolution] = $this->mediaHelper->getIdFromEntityBrowserSelectValue($config[$name]);
        }
        else {
          // Set value from previous resolution.
          $bg_image_media_ids[$resolution] = end($bg_image_media_ids);
        }
      }
    }
    elseif (in_array(
      $config['block_type'],
      [self::KEY_OPTION_VIDEO, self::KEY_OPTION_VIDEO_LOOP]
    )) {
      $bg_image_media_ids['video'] = NULL;

      if (!empty($config['background_video'])) {
        $bg_image_media_ids['video'] = $this->mediaHelper->getIdFromEntityBrowserSelectValue($config['background_video']);
      }
    }
    else {
      foreach (MediaHelper::LIST_IMAGE_RESOLUTIONS as $resolution) {
        $bg_image_media_ids[$resolution] = NULL;
      }
    }

    foreach ($bg_image_media_ids as $name => $bg_image_media_id) {
      $bg_image_url = NULL;
      if (!empty($bg_image_media_id)) {
        $media_params = $this->mediaHelper->getMediaParametersById($bg_image_media_id);
        if (!isset($media_params['error'])) {
          $bg_image_url = file_create_url($media_params['src']);
          $title = !empty($media_params['title']) ? $media_params['title'] : $title;
          $alt = !empty($media_params['alt']) ? $media_params['alt'] : $alt;
        }
      }

      if (!$bg_image_url) {
        $custom_brand_shape_url = $this->getCustomForegroundImageUrl($config);
        $bg_url_object = $this->themeConfigParser->getUrlForFile('brand_shape');
        if ($bg_url_object) {
          $bg_image_url = !empty($custom_brand_shape_url) ? $custom_brand_shape_url : $bg_url_object->toUriString();
        }
      }

      $assets[$name] = [
        'src' => $bg_image_url,
        'alt' => $alt,
        'title' => $title,
      ];
    }

    return $assets;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $title_url = $form_state->getValue('title')['url'];
    $cta_url = $form_state->getValue('cta')['url'];
    $title_label = $form_state->getValue('title')['label'];
    $title_label_override = $form_state->getValue('title')['next_line_label']['value'];
    $block_type = $form_state->getValue('block_type');
    $product_cards = $form_state->get('card_storage');
    // Validation for title label and title label override.
    if ($title_label && $title_label_override) {
      if($block_type == 'default') {
        $form_state->setErrorByName('title][label', $this->t('Title label or Override Title label field must be given.'));
        $form_state->setErrorByName('title][next_line_label', '');
      }
      elseif ($block_type == 'image') {
        $form_state->setErrorByName('title][label', $this->t('Title label or Override Title label field must be given.'));
        $form_state->setErrorByName('title][next_line_label', '');
      }
      elseif ($block_type == 'video') {
        $form_state->setErrorByName('title][label', $this->t('Title label or Override Title label field must be given.'));
        $form_state->setErrorByName('title][next_line_label', '');
      }
    }
    if (!$title_label && !$title_label_override) {
      if($block_type == 'default') {
        $form_state->setErrorByName('title][label', $this->t('Title label or Override Title label field must be given.'));
        $form_state->setErrorByName('title][next_line_label', '');
      }
      elseif ($block_type == 'image') {
        $form_state->setErrorByName('title][label', $this->t('Title label or Override Title label field must be given.'));
        $form_state->setErrorByName('title][next_line_label', '');
      }
      elseif ($block_type == 'video') {
        $form_state->setErrorByName('title][label', $this->t('Title label or Override Title label field must be given.'));
        $form_state->setErrorByName('title][next_line_label', '');
      }
    }
    if (!empty($product_cards)) {
      foreach ($product_cards as $key => $product_card) {
        $cards_title_url = $product_card['title']['url'];
        $cards_cta_url = $product_card['cta']['url'];
        if (!((bool) preg_match("/^(http:\/\/|https:\/\/|\/)(?:[\p{L}\p{N}\x7f-\xff#!:\.\?\+=&@$'~*,;_\/\(\)\[\]\-]|%[0-9a-f]{2})+$/i", $cards_title_url))) {
          $form_state->setErrorByName('card][' . $key . '][title][url', $this->t('The URL is not valid.'));
        }
        if (!((bool) preg_match("/^(http:\/\/|https:\/\/|\/)(?:[\p{L}\p{N}\x7f-\xff#!:\.\?\+=&@$'~*,;_\/\(\)\[\]\-]|%[0-9a-f]{2})+$/i", $cards_cta_url))) {
          $form_state->setErrorByName('card][' . $key . '][cta][url', $this->t('The URL is not valid.'));
        }
      }
    }

    if (!empty($title_url)) {
      if (!((bool) preg_match("/^(http:\/\/|https:\/\/|\/)(?:[\p{L}\p{N}\x7f-\xff#!:\.\?\+=&@$'~*,;_\/\(\)\[\]\-]|%[0-9a-f]{2})+$/i", $title_url))) {
        $form_state->setErrorByName('title][url', $this->t('The URL is not valid.'));
      }
    }
    if (!empty($cta_url)) {
      if (!((bool) preg_match("/^(http:\/\/|https:\/\/|\/)(?:[\p{L}\p{N}\x7f-\xff#!:\.\?\+=&@$'~*,;_\/\(\)\[\]\-]|%[0-9a-f]{2})+$/i", $cta_url))) {
        $form_state->setErrorByName('cta][url', $this->t('The URL is not valid.'));
      }
    }
  }

  /**
   * Provides uploaded custom foreground image file URL.
   *
   * @param array $config
   *   Block settings config array.
   *
   * @return mixed
   *   Returns a file URL or an empty string value.
   */
  private function getCustomForegroundImageUrl(array $config) {
    $custom_shape_image_id = !empty($config["custom_foreground_image"]["image"]) ? $config["custom_foreground_image"]["image"] : NULL;
    if (!empty($custom_shape_image_id)) {
      $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($custom_shape_image_id);
      $media_data = $this->mediaHelper->getMediaParametersById($media_id);
      return !empty($media_data['src']) ? $media_data['src'] : '';
    }
    return '';
  }

}
