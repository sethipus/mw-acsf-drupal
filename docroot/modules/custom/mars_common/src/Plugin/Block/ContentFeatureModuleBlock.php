<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a content feature module block.
 *
 * @Block(
 *   id = "mars_common_content_feature_module",
 *   admin_label = @Translation("MARS: Content Feature Module"),
 *   category = @Translation("Custom")
 * )
 */
class ContentFeatureModuleBlock extends BlockBase implements ContainerFactoryPluginInterface {


  use EntityBrowserFormTrait;

  /**
   * Lighthouse entity browser image id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID = 'lighthouse_browser';

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * Mars Media Helper service.
   *
   * @var \Drupal\mars_media\MediaHelper
   */
  protected $mediaHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('mars_common.language_helper'),
      $container->get('mars_media.media_helper')
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
    ThemeConfiguratorParser $themeConfiguratorParser,
    LanguageHelper $language_helper,
    MediaHelper $media_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->languageHelper = $language_helper;
    $this->mediaHelper = $media_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();

    $build['#eyebrow'] = $this->languageHelper->translate($conf['eyebrow'] ?? '');
    $build['#title'] = $this->languageHelper->translate($conf['title'] ?? '');
    $build['#description'] = $this->languageHelper->translate($conf['description'] ?? '');
    $build['#explore_cta'] = $this->languageHelper->translate($conf['explore_cta'] ?? '');
    $build['#explore_cta_link'] = $conf['explore_cta_link'] ?? '';
    $build['#border_radius'] = $this->themeConfiguratorParser->getSettingValue('button_style');
    $build['#graphic_divider'] = $this->themeConfiguratorParser->getGraphicDivider();
    $build['#dark_overlay'] = $this->configuration['use_dark_overlay'] ?? TRUE;
    $build['#remove_space'] = $this->configuration['remove_space'] ?? FALSE;
    $build['#background_images'] = [];

    foreach (MediaHelper::LIST_IMAGE_RESOLUTIONS as $resolution) {
      $name = 'background';

      if ($resolution != 'desktop') {
        $name = 'background_' . $resolution;
      }

      if (!empty($conf[$name])) {
        $mediaId = $this->mediaHelper->getIdFromEntityBrowserSelectValue($conf[$name]);
        $mediaParams = $this->mediaHelper->getMediaParametersById($mediaId);
        if (!($mediaParams['error'] ?? FALSE) && ($mediaParams['src'] ?? FALSE)) {
          $build['#background_images'][$resolution] = $mediaParams;
        }
      }

      // Set value from previous resolution.
      if (empty($build['#background_images'][$resolution])) {
        $build['#background_images'][$resolution] = end($build['#background_images']);
      }
    }

    $build['#theme'] = 'content_feature_module_block';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $conf = $this->getConfiguration();

    return [
      'label_display' => FALSE,
      'use_dark_overlay' => TRUE,
      'remove_space' => FALSE,
      'explore_cta' => $conf['explore_cta'] ?? $this->t('Explore'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $character_limit_config = $this->configFactory->getEditable('mars_common.character_limit_page');

    $config = $this->getConfiguration();

    $form['eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#maxlength' => !empty($character_limit_config->get('content_feature_module_eyebrow')) ? $character_limit_config->get('content_feature_module_eyebrow') : 15,
      '#default_value' => $this->configuration['eyebrow'] ?? '',
      '#required' => TRUE,
    ];
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => !empty($character_limit_config->get('content_feature_module_title')) ? $character_limit_config->get('content_feature_module_title') : 55,
      '#default_value' => $this->configuration['title'] ?? '',
      '#required' => TRUE,
    ];

    foreach (MediaHelper::LIST_IMAGE_RESOLUTIONS as $resolution) {
      $name = 'background';
      $required = TRUE;

      if ($resolution != 'desktop') {
        $name = 'background_' . $resolution;
        $required = FALSE;
      }

      $image_default = $config[$name] ?? NULL;
      // Entity Browser element for background image.
      $form[$name] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID,
        $image_default, $form_state, 1, 'thumbnail', $required);
      // Convert the wrapping container to a details element.
      $form[$name]['#type'] = 'details';
      $form[$name]['#title'] = $this->t('Background (@resolution)', ['@resolution' => ucfirst($resolution)]);
      $form[$name]['#open'] = TRUE;
      $form[$name]['#required'] = $required;

      if ($resolution != 'desktop') {
        $form[$name]['#description'] = $this->t('Image Alt and Title will be replaced by Desktop image.');
      }
    }

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#maxlength' => !empty($character_limit_config->get('content_feature_module_description')) ? $character_limit_config->get('content_feature_module_description') : 300,
      '#default_value' => $this->configuration['description'] ?? '',
      '#required' => TRUE,
    ];
    $form['explore_group'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Explore CTA'),
      'explore_cta' => [
        '#type' => 'textfield',
        '#title' => $this->t('Button Label'),
        '#maxlength' => !empty($character_limit_config->get('content_feature_module_button_label')) ? $character_limit_config->get('content_feature_module_button_label') : 15,
        '#default_value' => $this->configuration['explore_cta'],
        '#required' => TRUE,
      ],
      'explore_cta_link' => [
        '#type' => 'textfield',
        '#title' => $this->t('URL'),
        '#default_value' => $this->configuration['explore_cta_link'] ?? '',
        '#required' => TRUE,
      ],
    ];

    $form['use_dark_overlay'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use dark overlay'),
      '#default_value' => $this->configuration['use_dark_overlay'] ?? TRUE,
    ];

    $form['remove_space'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Remove whitespace'),
      '#default_value' => $this->configuration['remove_space'] ?? FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['eyebrow'] = $form_state->getValue('eyebrow');
    $this->configuration['title'] = $form_state->getValue('title');
    $this->configuration['background'] = $this->getEntityBrowserValue($form_state, 'background');
    $this->configuration['description'] = $form_state->getValue('description');
    $this->configuration['explore_cta'] = $form_state->getValue('explore_group')['explore_cta'];
    $this->configuration['explore_cta_link'] = $form_state->getValue('explore_group')['explore_cta_link'];
    $this->configuration['use_dark_overlay'] = ($form_state->getValue('use_dark_overlay'))
      ? TRUE
      : FALSE;
    $this->configuration['remove_space'] = ($form_state->getValue('remove_space'))
      ? TRUE
      : FALSE;

    foreach (MediaHelper::LIST_IMAGE_RESOLUTIONS as $resolution) {
      $name = 'background';

      if ($resolution != 'desktop') {
        $name = 'background_' . $resolution;
      }

      $this->configuration[$name] = $this->getEntityBrowserValue($form_state, $name);
    }
  }

}
