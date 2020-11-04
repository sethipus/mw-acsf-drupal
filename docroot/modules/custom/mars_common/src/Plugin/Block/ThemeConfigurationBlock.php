<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\mars_common\ThemeConfiguratorService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides theme configuration block.
 *
 * @Block(
 *   id = "theme_configuration_block",
 *   admin_label = @Translation("MARS: Theme Configuration Block"),
 *   category = @Translation("Mars Common")
 * )
 */
class ThemeConfigurationBlock extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  /**
   * Theme configuration service.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorService
   */
  protected $themeConfiguratorService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_common.theme_configurator_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ThemeConfiguratorService $theme_configurator_service
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->themeConfiguratorService = $theme_configurator_service;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'color_settings' => [
        'color_a' => NULL,
        'color_b' => NULL,
        'color_c' => NULL,
        'color_d' => NULL,
        'color_e' => NULL,
        'color_f' => NULL,
        'top_nav' => NULL,
        'top_nav_gradient' => NULL,
        'bottom_nav' => NULL,
        'card_background' => NULL,
      ],
      'social' => NULL,
      'icons_settings' => NULL,
      'product_layout' => NULL,
      'font_settings' => [
        'headline_font' => NULL,
        'headline_font_path' => NULL,
        'primary_font' => NULL,
        'primary_font_path' => NULL,
        'secondary_font' => NULL,
        'secondary_font_path' => NULL,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['#class_provider'] = $this;
    $form_theme_configurator = $this->themeConfiguratorService->getThemeConfiguratorForm($form, $form_state, $config);
    return $form_theme_configurator;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->themeConfiguratorService::formSystemThemeSettingsSubmit($form, $form_state);
    $font_fields = $this->themeConfiguratorService::getFontFields();
    $form_state_values = $form_state->getValues();
    foreach ($font_fields as $field) {
      if ($form_state->hasValue($field . '_path')) {
        $form_state_values['font_settings'][$field . '_path'] = $form_state->getValue($field . '_path');
      }
    }
    if (isset($form_state_values['social']) && isset($form_state_values['social']['add_social'])) {
      unset($form_state_values['social']['add_social']);
    }
    $this->setConfiguration($form_state_values);

  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $this->themeConfiguratorService::formSystemThemeSettingsValidate($form, $form_state);
  }

}
