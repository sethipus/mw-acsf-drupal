<?php

namespace Drupal\mars_banners\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Homepage hero Block.
 *
 * @Block(
 *   id = "homepage_hero_block",
 *   admin_label = @Translation("Homepage Hero block"),
 *   category = @Translation("Global elements"),
 * )
 */
class HomepageHeroBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $build['#label'] = $config['label'];
    $build['#eyebrow'] = $config['eyebrow'];
    $build['#title_url'] = $config['title']['url'];
    $build['#title_label'] = $config['title']['label'];
    $build['#cta_url'] = ['href' => $config['cta']['url']];
    $build['#cta_title'] = $config['cta']['title'];
    $build['#block_type'] = $config['block_type'];
    $build['#background_default'] = $config['background_default'];
    $build['#background_image'] = $config['background_image'];
    $build['#background_video'] = $config['background_video'];

    $build['#theme'] = 'homepage_hero_block';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();
    $theme_settings = $this->config->get('emulsifymars.settings')->get();

    $form['eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#maxlength' => 15,
      '#required' => TRUE,
      '#default_value' => $config['eyebrow'] ?? '',
    ];
    $form['title'] = [
      '#type' => 'details',
      '#title' => $this->t('Title'),
      '#open' => TRUE,
    ];
    $form['title']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Title Link URL'),
      '#maxlength' => 2048,
      '#required' => TRUE,
      '#default_value' => $config['title']['url'] ?? '',
    ];
    $form['title']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title label'),
      '#maxlength' => 50,
      '#required' => TRUE,
      '#default_value' => $config['title']['label'] ?? '',
    ];
    $form['cta'] = [
      '#type' => 'details',
      '#title' => $this->t('CTA'),
      '#open' => TRUE,
    ];
    $form['cta']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('CTA Link URL'),
      '#maxlength' => 2048,
      '#required' => TRUE,
      '#default_value' => $config['cta']['url'] ?? '',
    ];
    $form['cta']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Link Title'),
      '#maxlength' => 15,
      '#required' => TRUE,
      '#default_value' => $config['cta']['title'] ?? 'Explore',
    ];
    $form['block_type'] = [
      '#title' => $this->t('Choose block type'),
      '#type' => 'select',
      '#options' => [
        'default' => $this->t('Default'),
        'image' => $this->t('Image'),
        'video' => $this->t('Video'),
      ],
      '#default_value' => $config['block_type'] ?? 'default',
    ];
    $form['background_default'] = [
      '#type' => 'hidden',
      '#value' => $theme_settings['brand_shape'] ?? '',
    ];
    $form['background_image'] = [
      '#title'           => $this->t('Background Image'),
      '#type'            => 'managed_file',
      '#process'         => [
        ['\Drupal\mars_banners\Element\MarsManagedFile', 'processManagedFile'],
        'mars_banners_process_image_widget',
      ],
      '#upload_validators' => [
        'file_validate_extensions' => ['gif png jpg jpeg svg'],
      ],
      '#theme' => 'image_widget',
      '#upload_location' => 'public://',
      '#preview_image_style' => 'medium',
      '#default_value'       => $config['background_image'] ?? '',
      '#states' => [
        'visible' => [
          ':input[name="settings[block_type]"]' => ['value' => 'image'],
        ],
        'required' => [
          ':input[name="settings[block_type]"]' => ['value' => 'image'],
        ],
      ],
    ];
    $form['background_video'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Background video link'),
      '#maxlength' => 2048,
      '#default_value' => $config['background_video'] ?? '',
      '#states' => [
        'visible' => [
          ':input[name="settings[block_type]"]' => ['value' => 'video'],
        ],
        'required' => [
          ':input[name="settings[block_type]"]' => ['value' => 'video'],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues());
  }

}
