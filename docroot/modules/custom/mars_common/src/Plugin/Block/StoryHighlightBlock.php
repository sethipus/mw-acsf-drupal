<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_common\Traits\OverrideThemeTextColorTrait;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Story Highlight block.
 *
 * @Block(
 *   id = "story_highlight",
 *   admin_label = @Translation("MARS: Story Highlight"),
 *   category = @Translation("Mars Common"),
 * )
 */
class StoryHighlightBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use EntityBrowserFormTrait;
  use OverrideThemeTextColorTrait;

  const STORY_ITEMS_COUNT = 3;
  const SVG_ASSETS_COUNT = 3;

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
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Media storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

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
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('media'),
      $container->get('mars_media.media_helper'),
      $container->get('mars_common.language_helper'),
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
    EntityStorageInterface $entity_storage,
    MediaHelper $media_helper,
    LanguageHelper $language_helper,
    ThemeConfiguratorParser $theme_configurator_parser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->mediaStorage = $entity_storage;
    $this->languageHelper = $language_helper;
    $this->mediaHelper = $media_helper;
    $this->themeConfiguratorParser = $theme_configurator_parser;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = $this->getConfiguration();
    return [
      'label_display' => FALSE,
      'with_brand_borders' => $config['with_brand_borders'] ?? FALSE,
      'overlaps_previous' => $config['overlaps_previous'] ?? FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();

    $build['#theme'] = 'story_highlight_block';

    $build['#title'] = $this->languageHelper->translate($conf['story_block_title']);
    $build['#brand_border'] = ($conf['with_brand_borders']) ? $this->themeConfiguratorParser->getBrandBorder2() : NULL;
    $build['#graphic_divider'] = $this->themeConfiguratorParser->getGraphicDivider();
    $build['#story_description'] = $this->languageHelper->translate($conf['story_block_description']);
    $build['#overlaps_previous'] = $conf['overlaps_previous'] ?? NULL;

    $build['#story_items'] = array_map(function ($value) {
      if ($value['item_type'] == self::KEY_OPTION_IMAGE) {
        $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($value['image']);
      }
      elseif ($value['item_type'] == self::KEY_OPTION_VIDEO) {
        $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($value['video']);
      }
      $item = $this->mediaHelper->getMediaParametersById($media_id);
      $item['video'] = ($value['item_type'] == self::KEY_OPTION_VIDEO);
      $item['image'] = ($value['item_type'] == self::KEY_OPTION_IMAGE);

      if (!empty($item['error'])) {
        return [];
      }

      $item['content'] = $this->languageHelper->translate($value['title']);
      $item['hide_volume'] = !empty($value['hide_volume']) ? TRUE : FALSE;

      return $item;
    }, $conf['items']);

    for ($i = 1; $i <= self::SVG_ASSETS_COUNT; $i++) {
      $asset_key = 'svg_asset_' . $i;
      $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($conf['svg_assets'][$asset_key]);
      $item = $this->mediaHelper->getMediaParametersById($media_id);

      if (!empty($item['error'])) {
        $build['#svg_asset_src_' . $i] = $build['#svg_asset_alt_' . $i] = NULL;
        continue;
      }

      $build['#svg_asset_src_' . $i] = $item['src'];
      $build['#svg_asset_alt_' . $i] = $item['alt'];
    }

    if (!empty($conf['view_more']['url'])) {
      $build['#view_more_cta_url'] = $conf['view_more']['url'];
      $build['#view_more_cta_label'] = !empty($conf['view_more']['label']) ? $this->languageHelper->translate($conf['view_more']['label']) : $this->languageHelper->translate('View More');
    }

    $build['#text_color_override'] = FALSE;
    if (!empty($conf['override_text_color']['override_color'])) {
      $build['#text_color_override'] = static::$overrideColor;
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();
    $character_limit_config = \Drupal::config('mars_common.character_limit_page');

    $form['story_block_title'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
      '#maxlength' => !empty($character_limit_config->get('story_highlight_title')) ? $character_limit_config->get('story_highlight_title') : 55,
      '#default_value' => $this->configuration['story_block_title'] ?? NULL,
    ];

    $form['story_block_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Story description'),
      '#maxlength' => !empty($character_limit_config->get('story_highlight_description')) ? $character_limit_config->get('story_highlight_description') : 255,
      '#default_value' => $this->configuration['story_block_description'] ?? NULL,
    ];

    $form['items'] = [
      '#type' => 'fieldset',
    ];

    for ($i = 0; $i < self::STORY_ITEMS_COUNT; $i++) {
      $form['items'][$i] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Story Item @index', ['@index' => $i + 1]),
      ];

      $form['items'][$i]['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title'),
        '#maxlength' => !empty($character_limit_config->get('story_highlight_item_title')) ? $character_limit_config->get('story_highlight_item_title') : 300,
        '#required' => TRUE,
        '#required_error' => $this->t('<em>Title</em> from <em>Story Item @index</em> is required.', ['@index' => $i + 1]),
        '#default_value' => $this->configuration['items'][$i]['title'] ?? NULL,
      ];

      $form['items'][$i]['item_type'] = [
        '#title' => $this->t('Item type'),
        '#type' => 'select',
        '#required' => TRUE,
        '#default_value' => $config['items'][$i]['item_type'] ?? self::KEY_OPTION_IMAGE,
        '#options' => [
          self::KEY_OPTION_IMAGE => $this->t('Image'),
          self::KEY_OPTION_VIDEO => $this->t('Video'),
        ],
      ];

      $image_default = $config['items'][$i]['image'] ?? NULL;
      if (!is_string($image_default)) {
        $image_default = NULL;
      }
      $form['items'][$i]['image'] = $this->getEntityBrowserForm(
        self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID,
        $image_default,
        $form_state,
        1,
        'thumbnail',
        function ($form_state) use ($i) {
          return $form_state->getValue([
            'settings',
            'items',
            $i,
            'image',
          ]) === self::KEY_OPTION_IMAGE;
        }
      );
      // Convert the wrapping container to a details element.
      $form['items'][$i]['image']['#type'] = 'details';
      $form['items'][$i]['image']['#title'] = $this->t('Image');
      $form['items'][$i]['image']['#open'] = TRUE;
      $form['items'][$i]['image']['#states'] = [
        'visible' => [
          [':input[name="settings[items][' . $i . '][item_type]"]' => ['value' => self::KEY_OPTION_IMAGE]],
        ],
      ];

      $video_default = $config['items'][$i]['video'] ?? NULL;
      if (!is_string($video_default)) {
        $video_default = NULL;
      }
      $form['items'][$i]['video'] = $this->getEntityBrowserForm(
        self::LIGHTHOUSE_ENTITY_BROWSER_VIDEO_ID,
        $video_default,
        $form_state,
        1,
        'default',
        function ($form_state) use ($i) {
          return $form_state->getValue([
            'settings',
            'items',
            $i,
            'video',
          ]) === self::KEY_OPTION_VIDEO;
        }
      );
      // Convert the wrapping container to a details element.
      $form['items'][$i]['video']['#type'] = 'details';
      $form['items'][$i]['video']['#title'] = $this->t('Video');
      $form['items'][$i]['video']['#open'] = TRUE;
      $form['items'][$i]['video']['#states'] = [
        'visible' => [
          [':input[name="settings[items][' . $i . '][item_type]"]' => ['value' => self::KEY_OPTION_VIDEO]],
        ],
      ];
      if ($this->configuration['items'][$i]['item_type'] === self::KEY_OPTION_VIDEO) {
          $form['items'][$i]['hide_volume'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Hide Volume'),
            '#default_value' => $this->configuration['items'][$i]['hide_volume'] ?? FALSE,
          ];
      }
    }

    $form['svg_assets'] = [
      '#type' => 'fieldset',
    ];
    for ($i = 0; $i < self::SVG_ASSETS_COUNT; $i++) {
      $asset_key = 'svg_asset_' . ($i + 1);

      $svg_assets_default = isset($config['svg_assets'][$asset_key]) ? $config['svg_assets'][$asset_key] : NULL;
      $form['svg_assets'][$asset_key] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID,
        $svg_assets_default, $form_state, 1, 'thumbnail');
      // Convert the wrapping container to a details element.
      $form['svg_assets'][$asset_key]['#type'] = 'details';
      $form['svg_assets'][$asset_key]['#title'] = $this->t('SVG asset @index', ['@index' => $i + 1]);
      $form['svg_assets'][$asset_key]['#open'] = TRUE;
    }

    $form['view_more'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('View more link'),
      'url' => [
        '#type' => 'textfield',
        '#title' => $this->t('URL'),
        '#description' => $this->t('Please check if string starts with: "/", "http://", "https://".'),
        '#default_value' => $this->configuration['view_more']['url'] ?? NULL,
      ],
      'label' => [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#placeholder' => $this->t('View More'),
        '#description' => $this->t('Defaults to <em>View More</em>.'),
        '#default_value' => $this->configuration['view_more']['label'] ?? NULL,
      ],
    ];

    $form['with_brand_borders'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('With/without brand border'),
      '#default_value' => $this->configuration['with_brand_borders'] ?? FALSE,
    ];

    $form['overlaps_previous'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('With/without overlaps previous'),
      '#default_value' => $this->configuration['overlaps_previous'] ?? FALSE,
    ];

    $this->buildOverrideColorElement($form, $this->configuration);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->configuration['story_block_title'] = $form_state->getValue('story_block_title');
    $this->configuration['story_block_description'] = $form_state->getValue('story_block_description');
    $this->configuration['items'] = $form_state->getValue('items');
    $this->configuration['svg_assets'] = $form_state->getValue('svg_assets');
    $this->configuration['view_more'] = $form_state->getValue('view_more');
    $this->configuration['with_brand_borders'] = $form_state->getValue('with_brand_borders');
    $this->configuration['overlaps_previous'] = $form_state->getValue('overlaps_previous');
    $this->configuration['override_text_color'] = $form_state->getValue('override_text_color');

    $svg_assets = $form_state->getValue('svg_assets');
    if (!empty($svg_assets)) {
      foreach ($svg_assets as $key => $svg_asset) {
        $this->configuration['svg_assets'][$key] = $this->getEntityBrowserValue($form_state, [
          'svg_assets',
          $key,
        ]);
      }
    }
    $items = $form_state->getValue('items');
    if (!empty($items)) {
      foreach ($items as $key => $item) {
        unset(
          $this->configuration['carousel'][$key][self::KEY_OPTION_VIDEO],
          $this->configuration['carousel'][$key][self::KEY_OPTION_IMAGE]
        );

        $this->configuration['items'][$key][$item['item_type']] = $this->getEntityBrowserValue($form_state, [
          'items',
          $key,
          $item['item_type'],
        ]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $view_more_url = $form_state->getValue('view_more')['url'];
    if (!empty($view_more_url) && !(UrlHelper::isValid($view_more_url) && preg_match('/^(http:\/\/|https:\/\/|\/)/', $view_more_url))) {
      $form_state->setErrorByName('view_more][url', $this->t('The URL is not valid.'));
    }
  }

}
