<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
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

  const STORY_ITEMS_COUNT = 3;
  const SVG_ASSETS_COUNT = 3;

  /**
   * Lighthouse entity browser image id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID = 'lighthouse_browser';

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
      $container->get('mars_common.media_helper'),
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
    return [
      'label_display' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();

    $build['#theme'] = 'story_highlight_block';

    $build['#title'] = $this->languageHelper->translate($conf['story_block_title']);
    $build['#brand_border'] = $this->themeConfiguratorParser->getBrandBorder2();
    $build['#graphic_divider'] = $this->themeConfiguratorParser->getGraphicDivider();
    $build['#story_description'] = $this->languageHelper->translate($conf['story_block_description']);

    $build['#story_items'] = array_map(function ($value) {
      $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($value['media']);
      $item = $this->mediaHelper->getMediaParametersById($media_id);

      if (!empty($item['error'])) {
        return [];
      }

      $item['content'] = $this->languageHelper->translate($value['title']);

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
      $build['#text_color_override'] = '#FFFFFF';
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['story_block_title'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
      '#maxlength' => 300,
      '#default_value' => $this->configuration['story_block_title'] ?? NULL,
    ];

    $form['story_block_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Story description'),
      '#maxlength' => 255,
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
        '#maxlength' => 200,
        '#required' => TRUE,
        '#required_error' => $this->t('<em>Title</em> from <em>Story Item @index</em> is required.', ['@index' => $i + 1]),
        '#default_value' => $this->configuration['items'][$i]['title'] ?? NULL,
      ];

      $media_default = isset($config['items'][$i]['media']) ? $config['items'][$i]['media'] : NULL;
      $form['items'][$i]['media'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID,
        $media_default, $form_state, 1, 'thumbnail');
      // Convert the wrapping container to a details element.
      $form['items'][$i]['media']['#type'] = 'details';
      $form['items'][$i]['media']['#title'] = $this->t('Media');
      $form['items'][$i]['media']['#open'] = TRUE;
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

    $form['override_text_color'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Override theme text color'),
    ];

    $form['override_text_color']['override_color'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Override default theme text color configuration with white for the selected component'),
      '#default_value' => $config['override_text_color']['override_color'] ?? NULL,
    ];

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
        $this->configuration['items'][$key]['media'] = $this->getEntityBrowserValue($form_state, [
          'items',
          $key,
          'media',
        ]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $view_more_url = $form_state->getValue('view_more')['url'];
    if (!empty($view_more_url)) {
      if (!(UrlHelper::isValid($view_more_url) && preg_match('/^(http:\/\/|https:\/\/|\/)/', $view_more_url))) {
        $form_state->setErrorByName('view_more][url', $this->t('The URL is not valid.'));
      }
    }
  }

}
