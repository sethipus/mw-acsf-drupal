<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
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
   * @var \Drupal\mars_common\MediaHelper
   */
  protected $mediaHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    ThemeConfiguratorParser $theme_configurator_parser,
    MediaHelper $media_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->themeConfiguratorParser = $theme_configurator_parser;
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
      $container->get('mars_common.media_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

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
      '#maxlength' => 60,
    ];
    $form['header_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header 2'),
      '#default_value' => $this->configuration['header_2'],
      '#maxlength' => 60,
      '#required' => TRUE,
    ];
    // Entity Browser element for background image.
    $form['image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_ID, $this->configuration['image'], 1, 'thumbnail');
    // Convert the wrapping container to a details element.
    $form['image']['#type'] = 'details';
    $form['image']['#title'] = $this->t('Image');
    $form['image']['#open'] = TRUE;

    $form['image_alt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Image Alt'),
      '#default_value' => $this->configuration['image_alt'],
    ];
    $form['body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Description'),
      '#default_value' => $this->configuration['body']['value'] ?? '',
      '#format' => $this->configuration['body']['format'] ?? 'rich_text',
      '#maxlength' => 1000,
      '#required' => TRUE,
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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#block_aligned'] = $this->configuration['block_aligned'];
    $build['#header_1'] = $this->configuration['header_1'];
    $build['#header_2'] = $this->configuration['header_2'];
    $build['#body'] = $this->configuration['body']['value'];
    $build['#background_shape'] = $this->configuration['background_shape'] == 1 ? 'true' : 'false';
    if (!empty($this->configuration['image'])) {
      $mediaId = $this->mediaHelper->getIdFromEntityBrowserSelectValue($this->configuration['image']);
      $mediaParams = $this->mediaHelper->getMediaParametersById($mediaId);
      if (!isset($mediaParams['error']) && ($mediaParams['src'] ?? NULL)) {
        $build['#image'] = file_create_url($mediaParams['src']);
      }
    }

    $build['#image_alt'] = $this->configuration['image_alt'];
    if ($this->configuration['background_shape'] == 1) {
      $build['#brand_shape'] = $this->themeConfiguratorParser->getFileContentFromTheme('brand_shape');
    }
    $build['#custom_background_color'] = $this->configuration['custom_background_color'];
    $build['#use_custom_color'] = (bool) $this->configuration['use_custom_color'];
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
      'image_alt' => $config['image_alt'] ?? '',
      'custom_background_color' => $config['custom_background_color'] ?? '',
      'use_custom_color' => $config['use_custom_color'] ?? '',
    ];
  }

}
