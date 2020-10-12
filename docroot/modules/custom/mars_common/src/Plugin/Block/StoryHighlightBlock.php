<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
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

  const STORY_ITEMS_COUNT = 3;
  const SVG_ASSETS_COUNT = 3;

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
    ThemeConfiguratorParser $theme_configurator_parser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->mediaStorage = $entity_storage;
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

    $build['#title'] = $conf['story_block_title'];
    $build['#brand_border'] = $this->themeConfiguratorParser->getFileContentFromTheme('brand_borders_2');
    $build['#graphic_divider'] = $this->themeConfiguratorParser->getFileContentFromTheme('graphic_divider');
    $build['#story_description'] = $conf['story_block_description'];

    $build['#story_items'] = array_map(function ($value) {
      $item = $this->mediaHelper->getMediaParametersById($value['media']);

      if (!empty($item['error'])) {
        return [];
      }

      $item['content'] = $value['title'];

      return $item;
    }, $conf['items']);

    for ($i = 1; $i <= self::SVG_ASSETS_COUNT; $i++) {
      $asset_key = 'svg_asset_' . $i;
      $item = $this->mediaHelper->getMediaParametersById($conf['svg_assets'][$asset_key] ?? NULL);

      if (!empty($item['error'])) {
        $build['#svg_asset_src_' . $i] = $build['#svg_asset_alt_' . $i] = NULL;
        continue;
      }

      $build['#svg_asset_src_' . $i] = $item['src'];
      $build['#svg_asset_alt_' . $i] = $item['alt'];
    }

    if (!empty($conf['view_more']['url'])) {
      $build['#view_more_cta_url'] = $conf['view_more']['url'];
      $build['#view_more_cta_label'] = !empty($conf['view_more']['label']) ? $conf['view_more']['label'] : $this->t('View More');
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['story_block_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
      '#maxlength' => 45,
      '#default_value' => $this->configuration['story_block_title'] ?? NULL,
    ];

    $form['story_block_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Story description'),
      '#maxlength' => 150,
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

      $form['items'][$i]['media'] = [
        '#type' => 'entity_autocomplete',
        '#title' => $this->t('Media'),
        '#target_type' => 'media',
        '#required' => TRUE,
        '#required_error' => $this->t('<em>Media</em> from <em>Story Item @index</em> is required.', ['@index' => $i + 1]),
        '#default_value' => ($media_id = $this->configuration['items'][$i]['media'] ?? NULL) ? $this->mediaStorage->load($media_id) : NULL,
      ];
    }

    $form['svg_assets'] = [
      '#type' => 'fieldset',
    ];
    for ($i = 0; $i < self::SVG_ASSETS_COUNT; $i++) {
      $asset_key = 'svg_asset_' . ($i + 1);

      $form['svg_assets'][$asset_key] = [
        '#type' => 'entity_autocomplete',
        '#title' => $this->t('SVG asset @index', ['@index' => $i + 1]),
        '#target_type' => 'media',
        '#required' => TRUE,
        '#default_value' => ($media_id = $this->configuration['svg_assets'][$asset_key] ?? NULL) ? $this->mediaStorage->load($media_id) : NULL,
      ];
    }

    $form['view_more'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('View more link'),
      'url' => [
        '#type' => 'url',
        '#title' => $this->t('URL'),
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
  }

}
