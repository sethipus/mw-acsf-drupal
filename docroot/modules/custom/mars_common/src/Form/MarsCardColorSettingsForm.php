<?php

namespace Drupal\mars_common\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_common\Traits\SelectBackgroundColorTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure color settings for the cards.
 */
class MarsCardColorSettingsForm extends ConfigFormBase {

  use SelectBackgroundColorTrait;

  /**
   * Pages data.
   *
   * @var array
   */
  private $pages = [
    'recipe' => [
      'name' => 'Recipe',
      'content_type' => 'recipe',
    ],
    'product' => [
      'name' => 'Product',
      'content_type' => 'product',
    ],
    'campaign' => [
      'name' => 'Campaign',
      'content_type' => 'campaign',
    ],
    'landing' => [
      'name' => 'Landing',
      'content_type' => 'landing_page',
    ],
    'hub_card' => [
      'name' => 'Hub card',
      'content_type' => 'content_hub_page',
    ],
    'article' => [
      'name' => 'Article',
      'content_type' => 'article',
    ],
  ];

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'mars_common.card_color_settings';

  /**
   * Theme configurator parser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * MarsCardColorSettingsForm constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ThemeConfiguratorParser $theme_configurator_parser) {
    parent::__construct($config_factory);
    $this->themeConfiguratorParser = $theme_configurator_parser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('mars_common.theme_configurator_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'card_color_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $color_c = $this->themeConfiguratorParser->getSettingValue('color_c');
    $color_d = $this->themeConfiguratorParser->getSettingValue('color_d');
    $color_e = $this->themeConfiguratorParser->getSettingValue('color_e');

    foreach ($this->pages as $key => $page_title) {
      $form['title_' . $key] = [
        '#type' => 'markup',
        '#markup' => 'Background setting for all <b>' . $page_title['name'] . '</b> cards.',
      ];
      $form['select_background_color_' . $key] = [
        '#type' => 'select',
        '#title' => $this->t('Select background color'),
        '#options' => [
          'default' => $this->t('Default'),
          'color_c' => 'Color C - ' . $color_c,
          'color_d' => 'Color D - ' . $color_d,
          'color_e' => 'Color E - ' . $color_e,
        ],
        '#default_value' => $config->get('select_background_color_' . $key),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $config = $this->configFactory->getEditable(static::SETTINGS);
    $values = $form_state->getValues();
    foreach ($this->pages as $key => $page) {
      $config->set('select_background_color_' . $key, $values['select_background_color_' . $key]);
    }
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
