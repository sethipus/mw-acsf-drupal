<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a content feature module block.
 *
 * @Block(
 *   id = "mars_common_content_feature_module",
 *   admin_label = @Translation("Content Feature Module"),
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
   * ThemeConfiguratorParser.
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
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('mars_common.media_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ThemeConfiguratorParser $themeConfiguratorParser,
    MediaHelper $media_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->mediaHelper = $media_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();

    $build['#eyebrow'] = $conf['eyebrow'] ?? '';
    $build['#title'] = $conf['title'] ?? '';

    if (!empty($conf['background'])) {
      $mediaId = $this->mediaHelper->getIdFromEntityBrowserSelectValue($conf['background']);
      $mediaParams = $this->mediaHelper->getMediaParametersById($mediaId);
      if (!($mediaParams['error'] ?? FALSE) && ($mediaParams['src'] ?? FALSE)) {
        $build['#background'] = $mediaParams['src'];
      }
    }

    $build['#description'] = $conf['description'] ?? '';
    $build['#explore_cta'] = $conf['explore_cta'] ?? '';
    $build['#explore_cta_link'] = $conf['explore_cta_link'] ?? '';
    $build['#border_radius'] = $this->themeConfiguratorParser->getSettingValue('button_style');
    $build['#graphic_divider'] = $this->themeConfiguratorParser->getFileContentFromTheme('graphic_divider');

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
      'explore_cta' => $conf['explore_cta'] ?? $this->t('Explore'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#maxlength' => 15,
      '#default_value' => $this->configuration['eyebrow'] ?? '',
      '#required' => TRUE,
    ];
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 35,
      '#default_value' => $this->configuration['title'] ?? '',
      '#required' => TRUE,
    ];

    $image_default = isset($config['background']) ? $config['background'] : NULL;
    // Entity Browser element for background image.
    $form['background'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID, $image_default, 1, 'thumbnail');
    // Convert the wrapping container to a details element.
    $form['background']['#type'] = 'details';
    $form['background']['#title'] = $this->t('Background');
    $form['background']['#open'] = TRUE;

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#maxlength' => 65,
      '#default_value' => $this->configuration['description'] ?? '',
      '#required' => TRUE,
    ];
    $form['explore_group'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Explore CTA'),
      'explore_cta' => [
        '#type' => 'textfield',
        '#title' => $this->t('Button Label'),
        '#maxlength' => 15,
        '#default_value' => $this->configuration['explore_cta'],
        '#required' => FALSE,
      ],
      'explore_cta_link' => [
        '#type' => 'textfield',
        '#title' => $this->t('URL'),
        '#default_value' => $this->configuration['explore_cta_link'] ?? '',
        '#required' => FALSE,
      ],
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
  }

}
