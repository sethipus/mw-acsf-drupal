<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a product feature block.
 *
 * @Block(
 *   id = "product_feature",
 *   admin_label = @Translation("MARS: Product Feature Block"),
 *   category = @Translation("Mars Common")
 * )
 */
class ProductFeatureBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use EntityBrowserFormTrait;

  /**
   * Lighthouse entity browser image id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID = 'lighthouse_browser';

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
   * Theme configurator parser service.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  private $themeConfiguratorParser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_common.language_helper'),
      $container->get('mars_media.media_helper'),
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
    LanguageHelper $language_helper,
    MediaHelper $media_helper,
    ThemeConfiguratorParser $theme_configurator_parser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageHelper = $language_helper;
    $this->mediaHelper = $media_helper;
    $this->themeConfiguratorParser = $theme_configurator_parser;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();

    $build['#eyebrow'] = $this->languageHelper->translate($conf['eyebrow'] ?? '');
    $build['#title'] = $this->languageHelper->translate($conf['title'] ?? '');
    $build['#background_color'] = $conf['background_color'] ?? '';
    if (!empty($conf['image'])) {
      $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($conf['image']);
      $media_params = $this->mediaHelper->getMediaParametersById($media_id);
      if (!($media_params['error'] ?? FALSE) && ($media_params['src'] ?? FALSE)) {
        $build['#image_src'] = $media_params['src'];
        $build['#image_alt'] = $media_params['alt'];
      }
    }
    $build['#explore_cta'] = $this->languageHelper->translate($conf['explore_cta'] ?? '');
    $build['#explore_cta_link'] = $conf['explore_cta_link'] ?? '';
    $new_window = $conf['new_window'] ?? NULL;
    $build['#new_window'] = ($new_window == TRUE) ? '_blank' : '_self';
    $build['#brand_shape'] = $this->themeConfiguratorParser->getBrandShapeWithoutFill();

    $build['#theme'] = 'product_feature_block';

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
    $character_limit_config = \Drupal::config('mars_common.character_limit_page');

    $form['eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#maxlength' => !empty($character_limit_config->get('product_feature_block_eyebrow')) ? $character_limit_config->get('product_feature_block_eyebrow') : 15,
      '#default_value' => $this->configuration['eyebrow'] ?? '',
      '#required' => TRUE,
    ];
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => !empty($character_limit_config->get('product_feature_block_title')) ? $character_limit_config->get('product_feature_block_title') : 55,
      '#default_value' => $this->configuration['title'] ?? '',
      '#required' => TRUE,
    ];
    $form['background_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Background Color Override'),
      '#maxlength' => !empty($character_limit_config->get('product_feature_block_background_color_override')) ? $character_limit_config->get('product_feature_block_background_color_override') : 7,
      '#default_value' => $this->configuration['background_color'] ?? '',
      '#required' => FALSE,
      '#description' => $this->t('Must be AA compliant. Note that the Secondary Color / Color B will be used for CTAs background color.'),
    ];

    $image_default = $config['image'] ?? NULL;
    // Entity Browser element for background image.
    $form['image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_IMAGE_ID,
      $image_default, $form_state, 1, 'thumbnail');
    // Convert the wrapping container to a details element.
    $form['image']['#type'] = 'details';
    $form['image']['#title'] = $this->t('Image');
    $form['image']['#open'] = TRUE;

    $form['explore_group'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Explore CTA'),
      'explore_cta' => [
        '#type' => 'textfield',
        '#title' => $this->t('Button Label'),
        '#maxlength' => !empty($character_limit_config->get('product_feature_block_button_label')) ? $character_limit_config->get('product_feature_block_button_label') : 15,
        '#default_value' => $this->configuration['explore_cta'],
        '#required' => TRUE,
      ],
      'explore_cta_link' => [
        '#type' => 'textfield',
        '#title' => $this->t('URL'),
        '#default_value' => $this->configuration['explore_cta_link'] ?? '',
        '#required' => TRUE,
      ],
      'new_window' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Open CTA link in a new tab'),
        '#default_value' => $this->configuration['new_window'] ?? FALSE,
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
    $this->configuration['background_color'] = $form_state->getValue('background_color');
    $this->configuration['image'] = $this->getEntityBrowserValue($form_state, 'image');
    $this->configuration['explore_cta'] = $form_state->getValue('explore_group')['explore_cta'];
    $this->configuration['explore_cta_link'] = $form_state->getValue('explore_group')['explore_cta_link'];
    $this->configuration['new_window'] = $form_state->getValue('explore_group')['new_window'];
  }

}
