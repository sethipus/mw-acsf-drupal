<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
      $container->get('config.factory'),
      $container->get('entity_type.manager')->getStorage('media'),
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
    ConfigFactoryInterface $config_factory,
    EntityStorageInterface $entity_storage,
    ThemeConfiguratorParser $theme_configurator_parser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configFactory = $config_factory;
    $this->mediaStorage = $entity_storage;
    $this->themeConfiguratorParser = $theme_configurator_parser;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_url;

    $conf = $this->getConfiguration();
    $theme_settings = $this->configFactory->get('emulsifymars.settings')->get();

    $build['#theme'] = 'story_highlight_block';

    // Get brand border path.
    if (!empty($theme_settings['brand_borders']) && count($theme_settings['brand_borders']) > 0) {
      $border_file = $this->fileStorage->load($theme_settings['brand_borders'][0]);
      $build['#brand_border'] = !empty($border_file) ? file_get_contents($base_url . $border_file->createFileUrl()) : '';
    }

    $build['#title'] = $conf['story_block_title'];
    $build['#graphic_divider'] = $this->themeConfiguratorParser->getFileContentFromTheme('graphic_divider');
    $build['#story_description'] = $conf['story_block_description'];

    $build['#story_items'] = array_map(function ($value) {
      $value['media'] = $this->getMediaUriById($value['media'] ?? NULL);
      return $value;
    }, $conf['items']);

    for ($i = 1; $i <= self::SVG_ASSETS_COUNT; $i++) {
      $asset_key = 'svg_asset_' . $i;
      $build['#' . $asset_key] = $this->getMediaUriById($conf['svg_assets'][$asset_key] ?? NULL);
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
        '#maxlength' => 20,
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

  /**
   * Helper method that loads Media file URL using Media id.
   *
   * @param int $media_id
   *   Media ID.
   *
   * @return string|null
   *   File URI or NULL if URI cannot be defined.
   */
  protected function getMediaUriById($media_id) {
    if (empty($media_id) || !($entity = $this->mediaStorage->load($media_id))) {
      return NULL;
    }

    if (!$entity->image || !$entity->image->target_id) {
      return NULL;
    }

    return $entity->image->entity->uri->value ?? NULL;
  }

}
