<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FullWidthCarouselBlock is used for Full width Carousel component logic.
 *
 * @Block(
 *   id = "full_width_carousel_block",
 *   admin_label = @Translation("MARS: Fullwidth Carousel component"),
 *   category = @Translation("Page components"),
 * )
 *
 * @package Drupal\mars_common\Plugin\Block
 */
class FullWidthCarouselBlock extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  use EntityBrowserFormTrait;

  /**
   * Lighthouse entity browser id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_ID = 'lighthouse_browser';

  /**
   * Lighthouse entity browser video id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_VIDEO_ID = 'lighthouse_video_browser';

  /**
   * Key option video.
   */
  const KEY_OPTION_VIDEO = 'video';

  /**
   * Key option image.
   */
  const KEY_OPTION_IMAGE = 'image';

  /**
   * Default background style.
   */
  const KEY_OPTION_OTHER_COLOR = 'other';

  /**
   * Default background style.
   */
  const KEY_OPTION_TEXT_COLOR_DEFAULT = 'color_e';

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * Theme configurator parser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    LanguageHelper $language_helper,
    MediaHelper $media_helper,
    ThemeConfiguratorParser $theme_configurator_parser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->languageHelper = $language_helper;
    $this->mediaHelper = $media_helper;
    $this->themeConfiguratorParser = $theme_configurator_parser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('mars_common.language_helper'),
      $container->get('mars_media.media_helper'),
      $container->get('mars_common.theme_configurator_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $items = [];
    $build = [];
    foreach ($config['carousel'] as $key => $item_value) {
      if ($item_value['item_type'] == self::KEY_OPTION_IMAGE) {
        $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($item_value['image']);
      }
      elseif ($item_value['item_type'] == self::KEY_OPTION_VIDEO) {
        $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($item_value['video']);
      }
      $media_params = $this->mediaHelper->getMediaParametersById($media_id);
      if (!($media_params['error'] ?? FALSE) && ($media_params['src'] ?? FALSE)) {
        $item = [
          'src' => $media_params['src'],
          'content' => $this->languageHelper->translate($item_value['description']),
          'video' => ($item_value['item_type'] == self::KEY_OPTION_VIDEO),
          'image' => ($item_value['item_type'] == self::KEY_OPTION_IMAGE),
          'alt' => $media_params['alt'] ?? '',
          'title' => $media_params['title'] ?? '',
          'hide_volume' => !empty($item_value['hide_volume']) ? TRUE : FALSE,
          'eyebrow' => $item_value['eyebrow'] ?? '',
          'title_url' => $item_value['url'] ?? '',
          'title_label' => $this->languageHelper->translate($item_value['label']) ?? '',
          'cta_url' => ['href' => $item_value['cta']['url']] ?? '',
          'cta_title' => $this->languageHelper->translate($item_value['cta']['title']) ?? '',
          'stop_autoplay' => !empty($item_value['stop_autoplay']) ? TRUE : FALSE,
          'use_dark_overlay' => $item_value['use_dark_overlay'] ? TRUE : FALSE,
          'text_alignment' => $this->languageHelper->translate($item_value['text_alignment']) ?? '',
          'block_type' => $this->languageHelper->translate($item_value['block_type']) ?? '',
          'styles' => 'color:' . $this->getTextColor($key),
          'graphic_divider' => $this->themeConfiguratorParser->getGraphicDivider(),
        ];
        $items[] = $item;
      }
    }

    $build['#brand_borders'] = $this->themeConfiguratorParser->getBrandBorder();

    $build['#title'] = $this->languageHelper->translate($config['carousel_label'] ?? '');
    $build['#items'] = $items;
    $build['#theme'] = 'fullwidth_carousel_component';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();
    $character_limit_config = $this->configFactory->getEditable('mars_common.character_limit_page');

    $form['carousel_label'] = [
      '#title'         => $this->t('Carousel title'),
      '#type'          => 'textfield',
      '#default_value' => $config['carousel_label'],
      '#maxlength' => !empty($character_limit_config->get('carousel_component_title')) ? $character_limit_config->get('carousel_component_title') : 55,
    ];

    $form['carousel'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => $this->t('Carousel items'),
      '#prefix' => '<div id="carousel-wrapper">',
      '#suffix' => '</div>',
    ];

    $submitted_input = $form_state->getUserInput()['settings'] ?? [];
    $saved_items = !empty($config['carousel']) ? $config['carousel'] : [];
    $submitted_items = $submitted_input['carousel'] ?? [];
    $current_items_state = $form_state->get('carousel_storage');

    if (empty($current_items_state)) {
      if (!empty($submitted_items)) {
        $current_items_state = $submitted_items;
      }
      else {
        $current_items_state = $saved_items;
      }
    }

    $form_state->set('carousel_storage', $current_items_state);

    foreach ($current_items_state as $key => $value) {
      $form['carousel'][$key] = [
        '#type' => 'details',
        '#title' => $this->t('Carousel items'),
        '#open' => TRUE,
      ];

      $form['carousel'][$key]['item_type'] = [
        '#title' => $this->t('Carousel item type'),
        '#type' => 'select',
        '#required' => TRUE,
        '#default_value' => $config['carousel'][$key]['item_type'] ?? self::KEY_OPTION_IMAGE,
        '#options' => [
          self::KEY_OPTION_IMAGE => $this->t('Image'),
          self::KEY_OPTION_VIDEO => $this->t('Video'),
        ],
      ];
      $form['carousel'][$key]['eyebrow'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Eyebrow'),
        '#maxlength' => !empty($character_limit_config->get('hero_block_eyebrow')) ? $character_limit_config->get('hero_block_eyebrow') : 15,
        '#default_value' => $config['carousel'][$key]['eyebrow'] ?? '',
        '#states' => [
          'required' => [
            [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
            'or',
            [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
          ],
        ],
      ];
      $form['carousel'][$key]['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title label'),
        '#maxlength' => 55,
        '#description' => $this->t('Default lable for title, if choose override title label option make this field empty.'),
        '#default_value' => $config['carousel'][$key]['label'] ?? '',
        '#states' => [
          'visible' => [
            [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
            'or',
            [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
          ],
        ],
      ];
      $form['carousel'][$key]['url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title Link URL'),
        '#description' => $this->t('Please check if string starts with: "/", "http://", "https://".'),
        '#maxlength' => 2048,
        '#default_value' => $config['carousel'][$key]['url'] ?? '',
        '#states' => [
          'required' => [
            [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
            'or',
            [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
          ],
        ],
      ];
      $form['carousel'][$key]['description'] = [
        '#title' => $this->t('Carousel item description'),
        '#type' => 'textarea',
        '#default_value' => $config['carousel'][$key]['description'] ?? NULL,
        '#maxlength' => !empty($character_limit_config->get('carousel_item_description')) ? $character_limit_config->get('carousel_item_description') : 255,
      ];
      $form['carousel'][$key]['cta'] = [
        '#type' => 'details',
        '#title' => $this->t('CTA'),
        '#open' => TRUE,
      ];
      $form['carousel'][$key]['cta']['url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('CTA Link URL'),
        '#description' => $this->t('Please check if string starts with: "/", "http://", "https://".'),
        '#maxlength' => !empty($character_limit_config->get('hero_block_cta_link_url')) ? $character_limit_config->get('hero_block_cta_link_url') : 2048,
        '#default_value' => $config['carousel'][$key]['cta']['url'] ?? '',
        '#states' => [
          'required' => [
            [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
            'or',
            [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
          ],
        ],
      ];
      $form['carousel'][$key]['cta']['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('CTA Link Title'),
        '#maxlength' => !empty($character_limit_config->get('hero_block_cta_link_title')) ? $character_limit_config->get('hero_block_cta_link_title') : 15,
        '#default_value' => $config['carousel'][$key]['title'] ?? '',
        '#states' => [
          'required' => [
            [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
            'or',
            [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
          ],
        ],
      ];

      /*
       * BC fix: There could be wrong array values stored under this key.
       * Currently the only valid value is a string, if it's not it then we
       * throw away this value.
       */
      $current_image_selection = $config['carousel'][$key]['image'] ?? NULL;
      if (!is_string($current_image_selection)) {
        $current_image_selection = NULL;
      }
      $form['carousel'][$key]['image'] = $this->getEntityBrowserForm(
        self::LIGHTHOUSE_ENTITY_BROWSER_ID,
        $current_image_selection,
        $form_state,
        1,
        'thumbnail',
        function ($form_state) use ($key) {
          $type = $form_state->getValue([
            'settings',
            'carousel',
            $key,
            'item_type',
          ]);
          return $type === self::KEY_OPTION_IMAGE;
        }
      );
      $form['carousel'][$key]['image']['#type'] = 'details';
      $form['carousel'][$key]['image']['#title'] = $this->t('List item image');
      $form['carousel'][$key]['image']['#open'] = TRUE;
      $form['carousel'][$key]['image']['#states'] = [
        'visible' => [
          [':input[name="settings[carousel][' . $key . '][item_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
        ],
        'required' => [
          [':input[name="settings[carousel][' . $key . '][item_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
        ],
      ];

      /*
       * BC fix: There could be wrong array values stored under this key.
       * Currently the only valid value is a string, if it's not it then we
       * throw away this value.
       */
      $current_video_selection = $config['carousel'][$key]['video'] ?? NULL;
      if (!is_string($current_video_selection)) {
        $current_video_selection = NULL;
      }
      $form['carousel'][$key]['video'] = $this->getEntityBrowserForm(
        self::LIGHTHOUSE_ENTITY_BROWSER_VIDEO_ID,
        $current_video_selection,
        $form_state,
        1,
        'default',
        function ($form_state) use ($key) {
          $type = $form_state->getValue([
            'settings',
            'carousel',
            $key,
            'item_type',
          ]);
          return $type === self::KEY_OPTION_VIDEO;
        }
      );
      $form['carousel'][$key]['video']['#type'] = 'details';
      $form['carousel'][$key]['video']['#title'] = $this->t('List item video');
      $form['carousel'][$key]['video']['#open'] = TRUE;
      $form['carousel'][$key]['video']['#states'] = [
        'visible' => [
          [':input[name="settings[carousel][' . $key . '][item_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
        ],
      ];
      if ($config['carousel'][$key]['item_type'] === self::KEY_OPTION_VIDEO) {
        $form['carousel'][$key]['hide_volume'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Hide Volume'),
          '#default_value' => $config['carousel'][$key]['hide_volume'] ?? FALSE,
        ];
        $form['carousel'][$key]['stop_autoplay'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Stop Autoplay'),
          '#default_value' => $config['carousel'][$key]['stop_autoplay'] ?? FALSE,
        ];
      }
      $form['carousel'][$key]['text_color'] = [
        '#type' => 'radios',
        '#title' => $this->t('Text color'),
        '#options' => $this->getTextColorOptions(),
        '#default_value' => $config['carousel'][$key]['text_color'] ?? NULL,
      ];

      $form['carousel'][$key]['text_color_other'] = [
        '#type' => 'jquery_colorpicker',
        '#title' => $this->t('Custom text color'),
        '#default_value' => $config['carousel'][$key]['text_color_other'] ?? NULL,
        '#states' => [
          'visible' => [
            [':input[name="settings[carousel][' . $key . '][text_color]"]' => ['value' => self::KEY_OPTION_OTHER_COLOR]],
          ],
        ],
      ];
      $form['carousel'][$key]['text_alignment'] = [
        '#type' => 'radios',
        '#title' => $this->t('Text alignment'),
        '#default_value' => $config['carousel'][$key]['text_alignment'] ?? 'left',
        '#options' => [
          'left' => $this->t('Left'),
          'center' => $this->t('Center'),
          'right' => $this->t('Right'),
        ],
      ];
      $form['carousel'][$key]['block_type'] = [
        '#type' => 'radios',
        '#title' => $this->t('Block type'),
        '#default_value' => $config['carousel'][$key]['block_type'] ?? 'homepage_hero',
        '#options' => [
          'homepage_hero' => $this->t('Homepage Hero'),
          'parent_page' => $this->t('Parent page'),
        ],
      ];
      $form['carousel'][$key]['use_dark_overlay'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Use dark overlay'),
        '#default_value' => $config['carousel'][$key]['use_dark_overlay'] ?? FALSE,
      ];
      $form['carousel'][$key]['remove_item'] = [
        '#type' => 'submit',
        '#name' => 'carousel_' . $key,
        '#value' => $this->t('Remove carousel item'),
        '#ajax' => [
          'callback' => [$this, 'ajaxRemoveCarouselItemCallback'],
          'wrapper' => 'carousel-wrapper',
        ],
        '#limit_validation_errors' => [],
        '#submit' => [[$this, 'removeCarouselItemSubmitted']],
      ];
    }

    $form['carousel']['add_item'] = [
      '#type'  => 'submit',
      '#name'  => 'carousel_add_item',
      '#value' => $this->t('Add new carousel item'),
      '#ajax'  => [
        'callback' => [$this, 'ajaxAddCarouselItemCallback'],
        'wrapper'  => 'carousel-wrapper',
      ],
      '#limit_validation_errors' => [],
      '#submit' => [[$this, 'addCarouselItemSubmitted']],
    ];

    return $form;
  }

  /**
   * Add new carousel item callback.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   *
   * @return array
   *   List container of configuration settings.
   */
  public function ajaxAddCarouselItemCallback(array $form, FormStateInterface $form_state) {
    return $form['settings']['carousel'];
  }

  /**
   * Add remove carousel item callback.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   *
   * @return array
   *   List container of configuration settings.
   */
  public function ajaxRemoveCarouselItemCallback(array $form, FormStateInterface $form_state) {
    return $form['settings']['carousel'];
  }

  /**
   * Custom submit carousel configuration settings form.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   */
  public function addCarouselItemSubmitted(
    array $form,
    FormStateInterface $form_state
  ) {
    $storage = $form_state->get('carousel_storage');
    array_push($storage, 1);
    $form_state->set('carousel_storage', $storage);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Custom submit carousel configuration settings form.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   */
  public function removeCarouselItemSubmitted(
    array $form,
    FormStateInterface $form_state
  ) {
    $triggered = $form_state->getTriggeringElement();
    if (isset($triggered['#parents'][3]) && $triggered['#parents'][3] == 'remove_item') {
      $carousel_storage = $form_state->get('carousel_storage');
      $id = $triggered['#parents'][2];
      unset($carousel_storage[$id]);
      $form_state->set('carousel_storage', $carousel_storage);
      $form_state->setRebuild(TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    unset($values['carousel']['add_item']);
    $this->setConfiguration($values);
    if (isset($values['carousel']) && !empty($values['carousel'])) {
      foreach ($values['carousel'] as $key => $item) {

        unset(
          $this->configuration['carousel'][$key][self::KEY_OPTION_VIDEO],
          $this->configuration['carousel'][$key][self::KEY_OPTION_IMAGE]
        );

        $this->configuration['carousel'][$key][$item['item_type']] = $this->getEntityBrowserValue($form_state, [
          'carousel',
          $key,
          $item['item_type'],
        ]);
      }
    }
  }

  /**
   * Get text color.
   *
   * @return string
   *   Color hex value.
   */
  private function getTextColor($key) {
    $color_option = $this->configuration['carousel'][$key]['text_color'];
    if ($color_option == self::KEY_OPTION_OTHER_COLOR) {
      return $this->configuration['carousel'][$key]['text_color_other'];
    }
    $color_option = !empty($color_option) ? $color_option : self::KEY_OPTION_TEXT_COLOR_DEFAULT;

    return $this->themeConfiguratorParser
      ->getSettingValue($color_option);
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

}
