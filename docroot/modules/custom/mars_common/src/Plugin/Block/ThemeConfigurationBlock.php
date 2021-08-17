<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
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
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_common.theme_configurator_service'),
      $container->get('file_system'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ThemeConfiguratorService $theme_configurator_service,
    FileSystemInterface $file_system,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->themeConfiguratorService = $theme_configurator_service;
    $this->fileSystem = $file_system;
    $this->configFactory = $config_factory;
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
        'headline_font_mobile_letterspacing' => ThemeConfiguratorService::LETTERSPACING_MOBILE_DEFAULT,
        'headline_font_tablet_letterspacing' => ThemeConfiguratorService::LETTERSPACING_TABLET_DEFAULT,
        'headline_font_desktop_letterspacing' => ThemeConfiguratorService::LETTERSPACING_DESKTOP_DEFAULT,
        'primary_font' => NULL,
        'primary_font_path' => NULL,
        'primary_font_mobile_letterspacing' => ThemeConfiguratorService::LETTERSPACING_MOBILE_DEFAULT,
        'primary_font_tablet_letterspacing' => ThemeConfiguratorService::LETTERSPACING_TABLET_DEFAULT,
        'primary_font_desktop_letterspacing' => ThemeConfiguratorService::LETTERSPACING_DESKTOP_DEFAULT,
        'secondary_font' => NULL,
        'secondary_font_path' => NULL,
        'secondary_font_mobile_letterspacing' => ThemeConfiguratorService::LETTERSPACING_MOBILE_DEFAULT,
        'secondary_font_tablet_letterspacing' => ThemeConfiguratorService::LETTERSPACING_TABLET_DEFAULT,
        'secondary_font_desktop_letterspacing' => ThemeConfiguratorService::LETTERSPACING_DESKTOP_DEFAULT,
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
    $this->themeConfiguratorService->formSystemThemeSettingsSubmit($form, $form_state);
    $font_fields = $this->themeConfiguratorService->getFontFields();
    $form_state_values = $form_state->getValues();

    // Collapse these values to match structure.
    if (!empty($form_state_values['logo']['settings'])) {
      foreach ($form_state_values['logo']['settings'] as $key => $value) {
        $form_state_values['logo'][$key] = $value;
      }
      unset($form_state_values['logo']['settings']);
    }

    if (isset($form_state_values['social']) && isset($form_state_values['social']['add_social'])) {
      unset($form_state_values['social']['add_social']);
    }
    $temp_form_state_values = $form_state_values;

    // Compare values to theme config.
    foreach ($form_state_values as $key => $value) {
      if (in_array($key, ['provider', 'label', 'label_display'])) {
        continue;
      }

      if ($key === 'social') {
        foreach ($value as $key2 => $value2) {
          unset($value2['remove_social']);
          if ($value2 === theme_get_setting('social.' . $key2)) {
            unset($temp_form_state_values[$key][$key2]);
          }
        }
      }
      elseif ($key === 'logo') {
        foreach ($value as $key2 => $value2) {
          $setting_path = $key2;
          if ($setting_path === 'logo_path') {
            $setting_path = 'logo.path';
          }
          if ($value2 === theme_get_setting($setting_path)) {
            unset($temp_form_state_values['logo'][$key2]);
          }
        }
      }
      // Color settings and font settings are flattened in the theme config,
      // but nested in this config.
      elseif (in_array($key, ['color_settings', 'font_settings'])) {
        foreach ($value as $key2 => $value2) {
          if ($value2 === theme_get_setting($key2)) {
            unset($temp_form_state_values[$key][$key2]);
          }
        }
      }
      else {
        if ($value === theme_get_setting($key)) {
          unset($temp_form_state_values[$key]);
        }
      }
    }

    $form_state_values = $temp_form_state_values;

    foreach ($font_fields as $field) {
      if ($form_state->hasValue($field . '_path')) {
        $form_state_values['font_settings'][$field . '_path'] = $form_state->getValue($field . '_path');
      }
    }

    // If the user uploaded a new logo, save it to a permanent location
    // and use it in place of the default theme-provided file.
    $default_scheme = $this->configFactory->get('system.file')->get('default_scheme');
    try {
      if (!empty($form_state_values['logo_upload'])) {
        $filename = $this->fileSystem->copy($form_state_values['logo_upload']->getFileUri(), $default_scheme . '://');
        $form_state_values['logo_path'] = $filename;
        unset($form_state_values['logo_upload']);
      }
    }
    catch (FileException $e) {
      // Ignore.
    }

    if (!empty($form_state_values['logo_path'])) {
      $form_state_values['logo_path'] = $this->validatePath($form_state_values['logo_path']);
    }

    $this->setConfiguration($form_state_values);

  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    if (isset($form['logo'])) {
      $file = _file_save_upload_from_form($form['logo']['settings']['logo_upload'], $form_state, 0);
      if ($file) {
        // Put the temporary file in form_values so we can save it on submit.
        $form_state->setValue('logo_upload', $file);
      }
    }
    $this->themeConfiguratorService->formSystemThemeSettingsValidate($form, $form_state);
  }

  /**
   * Helper function for validate path for image in block.
   *
   * @param string $path
   *   A path relative to the Drupal root or to the public files directory, or
   *   a stream wrapper URI.
   *
   * @return mixed
   *   A valid path that can be displayed through the theme system, or FALSE if
   *   the path could not be validated.
   */
  protected function validatePath($path) {
    // Absolute local file paths are invalid.
    if ($this->fileSystem->realpath($path) == $path) {
      return FALSE;
    }
    // A path relative to the Drupal root or a fully qualified URI is valid.
    if (is_file($path)) {
      return $path;
    }
    // Prepend 'public://' for relative file paths within public filesystem.
    if (StreamWrapperManager::getScheme($path) === FALSE) {
      $path = 'public://' . $path;
    }
    if (is_file($path)) {
      return $path;
    }
    return FALSE;
  }

}
