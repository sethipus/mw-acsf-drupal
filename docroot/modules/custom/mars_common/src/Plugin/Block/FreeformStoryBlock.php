<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_common\Traits\OverrideThemeTextColorTrait;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'MARS: Freeform Story Block' Block.
 *
 * @Block(
 *   id = "freeform_story_block",
 *   admin_label = @Translation("MARS: Freeform Story Block"),
 *   category = @Translation("Mars Common"),
 * )
 */
class FreeformStoryBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use EntityBrowserFormTrait;
  use OverrideThemeTextColorTrait;

  /**
   * Lighthouse entity browser id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_ID = 'lighthouse_browser';

  /**
   * Aligned by left side.
   */
  const LEFT_ALIGNED = 'left';

  /**
   * Aligned by right side.
   */
  const RIGHT_ALIGNED = 'right';

  /**
   * Aligned by center.
   */
  const CENTER_ALIGNED = 'center';

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Theme configurator parser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

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
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    ThemeConfiguratorParser $theme_configurator_parser,
    LanguageHelper $language_helper,
    MediaHelper $media_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->themeConfiguratorParser = $theme_configurator_parser;
    $this->languageHelper = $language_helper;
    $this->mediaHelper = $media_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('mars_common.language_helper'),
      $container->get('mars_media.media_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $character_limit_config = \Drupal::config('mars_common.character_limit_page');

    $form['block_aligned'] = [
      '#type' => 'select',
      '#title' => $this->t('Block aligned'),
      '#default_value' => $this->configuration['block_aligned'],
      '#options' => [
        self::LEFT_ALIGNED => $this->t('Left aligned'),
        self::RIGHT_ALIGNED => $this->t('Right aligned'),
        self::CENTER_ALIGNED => $this->t('Center aligned'),
      ],
    ];
    $form['header_1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header 1'),
      '#default_value' => $this->configuration['header_1'],
      '#maxlength' => !empty($character_limit_config->get('freeform_story_block_header_1')) ? $character_limit_config->get('freeform_story_block_header_1') : 60,
    ];
    $form['header_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header 2'),
      '#default_value' => $this->configuration['header_2'],
      '#maxlength' => !empty($character_limit_config->get('freeform_story_block_header_2')) ? $character_limit_config->get('freeform_story_block_header_2') : 60,
    ];
    // Entity Browser element for background image.
    $form['image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_ID,
      $this->configuration['image'], $form_state, 1, 'thumbnail', FALSE);
    // Convert the wrapping container to a details element.
    $form['image']['#type'] = 'details';
    $form['image']['#title'] = $this->t('Image');
    $form['image']['#open'] = TRUE;
    $form['body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Description'),
      '#default_value' => $this->configuration['body']['value'] ?? '',
      '#format' => $this->configuration['body']['format'] ?? 'rich_text',
      '#maxlength' => !empty($character_limit_config->get('freeform_story_block_description')) ? $character_limit_config->get('freeform_story_block_description') : 1000,
    ];
    $form['background_shape'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Background shape'),
      '#default_value' => $this->configuration['background_shape'] ?? FALSE,
    ];
    $form['use_custom_color'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use custom color'),
      '#default_value' => $this->configuration['use_custom_color'] ?? FALSE,
    ];
    $form['custom_background_color'] = [
      '#type' => 'jquery_colorpicker',
      '#title' => $this->t('Background Color Override'),
      '#default_value' => $this->configuration['custom_background_color'] ?? '',
    ];
    $form['use_original_image'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use original image'),
      '#default_value' => $this->configuration['use_original_image'] ?? FALSE,
    ];

    $this->buildOverrideColorElement($form, $this->configuration);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#block_aligned'] = $this->configuration['block_aligned'];
    $build['#header_1'] = $this->languageHelper->translate($this->configuration['header_1']);
    $build['#header_2'] = $this->languageHelper->translate($this->configuration['header_2']);
    $build['#body'] = $this->languageHelper->translate($this->configuration['body']['value']);
    $build['#background_shape'] = $this->configuration['background_shape'] == 1 ? 'true' : 'false';
    if (!empty($this->configuration['image'])) {
      $mediaId = $this->mediaHelper->getIdFromEntityBrowserSelectValue($this->configuration['image']);
      $mediaParams = $this->mediaHelper->getMediaParametersById($mediaId);
      if (!($mediaParams['error'] ?? FALSE) && ($mediaParams['src'] ?? FALSE)) {
        $build['#image'] = $mediaParams['src'];
        $build['#image_alt'] = $mediaParams['alt'];
      }
    }

    if ($this->configuration['background_shape'] == 1) {
      $build['#brand_shape'] = $this->themeConfiguratorParser->getBrandShapeWithoutFill();
    }
    $build['#custom_background_color'] = $this->configuration['custom_background_color'];
    $build['#use_custom_color'] = (bool) $this->configuration['use_custom_color'];
    $build['#use_original_image'] = (bool) $this->configuration['use_original_image'];

    $build['#text_color_override'] = FALSE;
    if (!empty($this->configuration['override_text_color']['override_color'])) {
      $build['#text_color_override'] = static::$overrideColor;
    }

    $build['#theme'] = 'freeform_story_block';

    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * This method processes the blockForm() form fields when the block
   * configuration form is submitted.
   *
   * The blockValidate() method can be used to validate the form submission.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues());
    $this->configuration['image'] = $this->getEntityBrowserValue($form_state, 'image');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = $this->getConfiguration();
    return [
      'block_aligned' => $config['block_aligned'] ?? '',
      'header_1' => $config['header_1'] ?? $this->t('Header 1'),
      'header_2' => $config['header_2'] ?? '',
      'body' => $config['body']['value'] ?? '',
      'background_shape' => $config['background_shape'] ?? '',
      'image' => $config['image'] ?? '',
      'custom_background_color' => $config['custom_background_color'] ?? '',
      'use_custom_color' => $config['use_custom_color'] ?? '',
      'use_original_image' => $config['use_original_image'] ?? 0,
    ];
  }

}
