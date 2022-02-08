<?php

namespace Drupal\mars_common\ThemeOverride;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\ConfigInstallerInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Installer\InstallerKernel;

/**
 * Class that applies the ThemeOverride values as overrides on the configs.
 */
class ConfigFactoryOverride implements ConfigFactoryOverrideInterface {

  /**
   * Theme config name.
   */
  const THEME_CONFIG = 'emulsifymars.settings';

  /**
   * Social icons field name.
   */
  const SOCIAL_LINKS = 'social';

  /**
   * Needed config override from general theme.
   */
  const NEEDED_CONFIG_OVERRIDE = [
    'color_a',
    'color_b',
    'color_c',
    'color_d',
    'color_e',
    'color_f',
    'top_nav',
    'top_nav_gradient',
    'footer_top',
    'footer_top_gradient',
    'bottom_nav',
    'language_region_selector_text_color',
    'product_filter_arrow_color',
    'product_filter_clearall_color',
    'product_filter_tickmark_color',
    'entrygate_background_color',
    'entrygate_title_color',
    'entrygate_text_color',
    'entrygate_date_color',
    'entrygate_alert_color',
    'cookie_banner',
    'cookie_banner_gradient',
    'cookie_banner_text',
    'cookie_banner_close',
    'cookie_banner_brand_border',
    'cookie_banner_override',
    'card_background',
    'card_title',
    'card_eyebrow',
    'headline_font_path',
    'headline_font_mobile_letterspacing',
    'headline_font_tablet_letterspacing',
    'headline_font_desktop_letterspacing',
    'primary_font_path',
    'primary_font_mobile_letterspacing',
    'primary_font_tablet_letterspacing',
    'primary_font_desktop_letterspacing',
    'secondary_font_path',
    'secondary_font_mobile_letterspacing',
    'secondary_font_tablet_letterspacing',
    'secondary_font_desktop_letterspacing',
    'graphic_divider',
    'brand_shape',
    'brand_borders',
    'brand_border_style',
    'brand_borders_2',
    'png_asset',
    'button_style',
    'social',
  ];

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * The config installer.
   *
   * @var \Drupal\Core\Config\ConfigInstallerInterface
   */
  private $configInstaller;

  /**
   * The theme override service.
   *
   * @var \Drupal\mars_common\ThemeOverride\ThemeOverrideService
   */
  private $themeOverrideService;

  /**
   * Set config manager as protected property.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Config\ConfigInstallerInterface $config_installer
   *   The config installer.
   * @param \Drupal\mars_common\ThemeOverride\ThemeOverrideService $theme_override_service
   *   Theme override service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    ConfigInstallerInterface $config_installer,
    ThemeOverrideService $theme_override_service
  ) {
    $this->configFactory = $config_factory;
    $this->configInstaller = $config_installer;
    $this->themeOverrideService = $theme_override_service;
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];
    if (!$this->isApplicableForConfigs($names)) {
      return $overrides;
    }
    $override = $this->themeOverrideService->getCurrentOverride();

    if (!$override->isEmpty()) {
      $overrides[self::THEME_CONFIG] = $this->getConfigOverrides($override);
    }

    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'ThemeOverride';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    $cacheableMetadata = new CacheableMetadata();

    if ($this->isApplicableForConfigs([$name])) {
      $override = $this->themeOverrideService->getCurrentOverride();
      return $cacheableMetadata->addCacheableDependency($override);
    }

    return $cacheableMetadata;
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject(
    $name,
    $collection = StorageInterface::DEFAULT_COLLECTION
  ) {
    return NULL;
  }

  /**
   * Get override config options.
   *
   * @param ThemeOverride $override
   *   Theme override object.
   *
   * @return array
   *   Resulting config override values.
   */
  private function getConfigOverrides(ThemeOverride $override): array {
    $config_overrides = [];

    foreach (self::NEEDED_CONFIG_OVERRIDE as $field_name) {
      if ($override->valueExists($field_name)) {
        $override_values = $override->getValue($field_name);
        if ($field_name === self::SOCIAL_LINKS) {
          $this->fillOutSocialLinks($override_values);
        }
        $config_overrides[$field_name] = $override_values;
      }
    }
    // Override logo.
    if ($override->valueExists('logo_path')) {
      $config_overrides['logo']['path'] = $override->getValue('logo_path');
    }
    return $config_overrides;
  }

  /**
   * Checks if the override should run for the given config names.
   *
   * @param string[] $names
   *   The config names.
   *
   * @return bool
   *   The result.
   */
  private function isApplicableForConfigs(array $names): bool {
    return in_array(self::THEME_CONFIG, $names) &&
      !$this->configInstaller->isSyncing() &&
      !InstallerKernel::installationAttempted();
  }

  /**
   * Fill up social links with empty values.
   *
   * @param array $social_link_override_values
   *   The theme override social link field values.
   */
  private function fillOutSocialLinks(array &$social_link_override_values) {
    // Social links from default theme.
    $social_theme_origin = $this->configFactory
      ->getEditable(self::THEME_CONFIG)
      ->getOriginal(self::SOCIAL_LINKS, FALSE);
    foreach ($social_theme_origin as $key => $item) {
      if (!isset($social_link_override_values[$key])) {
        $social_link_override_values[$key] = NULL;
      }
    }
  }

}
