<?php

namespace Drupal\mars_banners\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Homepage hero Block.
 *
 * @Block(
 *   id = "homepage_hero_block",
 *   admin_label = @Translation("Homepage Hero block"),
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
   * @var \Drupal\mars_common\MediaHelper
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
      $container->get('mars_common.media_helper'),
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
    $build['#cta_url'] = ['href' => $config['cta']['url']];
    $build['#cta_title'] = $this->languageHelper->translate($config['cta']['title']);
    $build['#block_type'] = $config['block_type'];
    $build['#background_asset'] = $this->getBgAsset();

    if (!empty($config['card'])) {
      foreach ($config['card'] as $key => $card) {
        $build['#blocks'][$key]['eyebrow'] = $this->languageHelper->translate($card['eyebrow']);
        $build['#blocks'][$key]['title_label'] = $this->languageHelper->translate($card['title']['label']);
        $build['#blocks'][$key]['title_href'] = $card['title']['url'];
        $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($card['foreground_image']);
        $media_data = $this->mediaHelper->getMediaParametersById($media_id);
        $file_url = NULL;
        if (!isset($media_data['error'])) {
          $file_url = $media_data['src'];
        }
        $format = '%s 375w, %s 768w, %s 1024w, %s 1440w';
        $default_alt = $this->languageHelper->translate('homepage hero 3up image');
        $build['#blocks'][$key]['image'][] = [
          'srcset' => sprintf($format, $file_url, $file_url, $file_url, $file_url),
          'src' => $file_url,
          'class' => 'block1-small',
          'alt' => !empty($media_data['alt']) ? $media_data['alt'] : $default_alt,
          'title' => !empty($media_data['title']) ? $media_data['title'] : $default_alt,
        ];
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
      '#type' => 'url',
      '#title' => $this->t('Title Link URL'),
      '#maxlength' => 2048,
      '#default_value' => $config['title']['url'] ?? '',
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
      '#default_value' => $config['title']['label'] ?? '',
      '#states' => [
        'required' => [
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
      '#type' => 'url',
      '#title' => $this->t('CTA Link URL'),
      '#maxlength' => 2048,
      '#default_value' => $config['cta']['url'] ?? '',
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

    $image_default = isset($config['background_image']) ? $config['background_image'] : NULL;
    // Entity Browser element for background image.
    $form['background_image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID,
      $image_default, $form_state, 1, 'thumbnail', function ($form_state) {
          return in_array($form_state->getValue(['settings', 'block_type']), [self::KEY_OPTION_IMAGE, self::KEY_OPTION_IMAGE_AND_TEXT]);
      }
    );
    // Convert the wrapping container to a details element.
    $form['background_image']['#type'] = 'details';
    $form['background_image']['#title'] = $this->t('Background Image');
    $form['background_image']['#open'] = TRUE;
    $form['background_image']['#states'] = [
      'visible' => [
        [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
        'or',
        [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE_AND_TEXT]],
      ],
      'required' => [
        [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
        'or',
        [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE_AND_TEXT]],
      ],
    ];

    $video_default = isset($config['background_video']) ? $config['background_video'] : NULL;
    // Entity Browser element for video.
    $form['background_video'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_VIDEO_ID,
      $video_default, $form_state, 1, 'default', function ($form_state) {
        return in_array($form_state->getValue(['settings', 'block_type']), [self::KEY_OPTION_VIDEO, self::KEY_OPTION_VIDEO_LOOP]);
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
        'invisible' => [
          ':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE_AND_TEXT],
        ],
      ],
    ];

    $card_settings = !empty($config['card']) ? $config['card'] : '';
    $card_storage = $form_state->get('card_storage');
    if (!isset($card_storage)) {
      if (!empty($card_settings)) {
        $card_storage = array_keys($card_settings);
      }
      else {
        $card_storage = [];
      }
      $form_state->set('card_storage', $card_storage);
    }

    $triggered = $form_state->getTriggeringElement();
    if (isset($triggered['#parents'][3]) && $triggered['#parents'][3] == 'remove_card') {
      $card_storage = $form_state->get('card_storage');
      $id = $triggered['#parents'][2];
      unset($card_storage[$id]);
    }

    foreach ($card_storage as $key => $value) {
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
      ];
      $form['card'][$key]['title']['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Card Title label'),
        '#maxlength' => 55,
        '#default_value' => $config['card'][$key]['title']['label'] ?? '',
      ];
      $form['card'][$key]['title']['url'] = [
        '#type' => 'url',
        '#title' => $this->t('Card Title Link URL'),
        '#maxlength' => 2048,
        '#default_value' => $config['card'][$key]['title']['url'] ?? '',
      ];
      $form['card'][$key]['cta']['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('CTA Link Title'),
        '#maxlength' => 15,
        '#default_value' => $config['card'][$key]['cta']['title'] ?? 'Explore',
      ];
      $form['card'][$key]['cta']['url'] = [
        '#type' => 'url',
        '#title' => $this->t('CTA Link URL'),
        '#maxlength' => 2048,
        '#default_value' => $config['card'][$key]['cta']['url'] ?? '',
      ];

      $foreground_default = isset($config['card'][$key]['foreground_image']) ? $config['card'][$key]['foreground_image'] : NULL;
      $form['card'][$key]['foreground_image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID,
        $foreground_default, $form_state, 1, 'thumbnail', FALSE);
      // Convert the wrapping container to a details element.
      $form['card'][$key]['foreground_image']['#type'] = 'details';
      $form['card'][$key]['foreground_image']['#title'] = $this->t('Foreground Image');
      $form['card'][$key]['foreground_image']['#open'] = TRUE;

      $form['card'][$key]['remove_card'] = [
        '#type'  => 'button',
        '#name' => 'card_' . $key,
        '#value' => $this->t('Remove card'),
        '#ajax'  => [
          'callback' => [$this, 'ajaxRemoveCardCallback'],
          'wrapper' => 'cards-wrapper',
        ],
      ];
    }
    if (count($card_storage) < 2) {
      $form['card']['add_card'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add card'),
        '#ajax' => [
          'callback' => [$this, 'ajaxAddCardCallback'],
          'wrapper' => 'cards-wrapper',
        ],
        '#limit_validation_errors' => [],
        '#submit' => [[$this, 'addCardSubmited']],
      ];
    }

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
  public function addCardSubmited(array $form, FormStateInterface $form_state) {
    $storage = $form_state->get('card_storage');
    array_push($storage, 1);
    $form_state->set('card_storage', $storage);
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    unset($values['card']['add_card']);
    $this->setConfiguration($values);
    $this->configuration['background_image'] = $this->getEntityBrowserValue($form_state, 'background_image');
    $this->configuration['background_video'] = $this->getEntityBrowserValue($form_state, 'background_video');
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
  private function getBgAsset(): ?array {
    $config = $this->getConfiguration();
    $bg_image_media_id = NULL;
    $bg_image_url = NULL;
    $title = 'homepage hero background image';
    $alt = 'homepage hero background image';

    if (in_array($config['block_type'], [self::KEY_OPTION_IMAGE, self::KEY_OPTION_IMAGE_AND_TEXT]) && !empty($config['background_image'])) {
      $bg_image_media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($config['background_image']);
    }
    elseif (in_array($config['block_type'], [self::KEY_OPTION_VIDEO, self::KEY_OPTION_VIDEO_LOOP]) && !empty($config['background_video'])) {
      $bg_image_media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($config['background_video']);
    }

    if ($bg_image_media_id) {
      $media_params = $this->mediaHelper->getMediaParametersById($bg_image_media_id);
      if (!isset($media_params['error'])) {
        $bg_image_url = file_create_url($media_params['src']);
        $title = !empty($media_params['title']) ? $media_params['title'] : $title;
        $alt = !empty($media_params['alt']) ? $media_params['alt'] : $alt;
      }
    }

    if (!$bg_image_url) {
      $bg_url_object = $this->themeConfigParser->getUrlForFile('brand_shape');
      if ($bg_url_object) {
        $bg_image_url = $bg_url_object->toUriString();
      }
    }

    return [
      'src' => $bg_image_url,
      'alt' => $alt,
      'title' => $title,
    ];
  }

}
