<?php

namespace Drupal\mars_common;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\mars_common\SVG\SVG;
use Drupal\mars_common\SVG\SVGException;
use Drupal\mars_common\SVG\SVGFactory;
use Psr\Log\LoggerInterface;

/**
 * Class ThemeConfiguratorParser.
 *
 * @package Drupal\mars_common
 */
class ThemeConfiguratorParser {

  /**
   * File storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $fileStorage;

  /**
   * Theme settings.
   *
   * @var array
   */
  private $themeSettings;

  /**
   * The theme config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * The svg factory service.
   *
   * @var \Drupal\mars_common\SVG\SVGFactory
   */
  private $svgFactory;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * Default constructor for ThemeConfiguratorParser.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\mars_common\SVG\SVGFactory $svg_factory
   *   The svg factory service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    SVGFactory $svg_factory,
    LoggerInterface $logger
  ) {
    $this->config = $config_factory->get('emulsifymars.settings');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->themeSettings = $this->config->get();
    $this->svgFactory = $svg_factory;
    $this->logger = $logger;
  }

  /**
   * Returns the cache metadata for the theme configurator.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cache metadata for theme configurator.
   */
  public function getCacheMetadataForThemeConfigurator(): CacheableMetadata {
    $cache_metadata = new CacheableMetadata();
    return $cache_metadata->addCacheableDependency($this->config);
  }

  /**
   * Returns logo file path.
   *
   * @return string
   *   File path.
   */
  public function getLogoFromTheme(): string {
    if (!empty($this->themeSettings['logo']) && !empty($this->themeSettings['logo']['path'])) {
      return $this->themeSettings['logo']['path'];
    }
    return '';
  }

  /**
   * Returns logo alt text.
   *
   * @return string
   *   Alt text path.
   */
  public function getLogoAltFromTheme(): string {
    if (!empty($this->themeSettings['logo_alt'])) {
      return $this->themeSettings['logo_alt'];
    }
    return '';
  }

  /**
   * Returns the brand border svg if there are any.
   *
   * @return \Drupal\mars_common\SVG\SVG|null
   *   The brand border or null.
   */
  public function getBrandBorder(): ?SVG {
    $svg = $this->getSVGFor('brand_borders');

    if ($svg !== NULL) {
      $svg = $svg->withoutFillInfo();
      $style = $this->getSettingValue(
        'brand_border_style',
        ThemeConfiguratorService::BORDER_STYLE_REPEAT
      );

      if ($style === ThemeConfiguratorService::BORDER_STYLE_REPEAT) {
        $svg = $svg->repeated();
      }
      else {
        $svg = $svg->stretched();
      }
    }

    return $svg;
  }

  /**
   * Returns the brand border 2 svg if there are any.
   *
   * @return \Drupal\mars_common\SVG\SVG|null
   *   The brand border 2 or null.
   */
  public function getBrandBorder2(): ?SVG {
    $svg = $this->getSvgFor('brand_borders_2');
    if ($svg !== NULL) {
      $svg = $svg
        ->withoutFillInfo()
        ->scaleWhileKeepingAspectRatio();
    }
    return $svg;
  }

  /**
   * Returns the graphic divider if there are any.
   *
   * @return \Drupal\mars_common\SVG\SVG|null
   *   The graphic divider or null.
   */
  public function getGraphicDivider(): ?SVG {
    return $this->getSvgFor('graphic_divider');
  }

  /**
   * Returns the current brand shape without fill info if there are any.
   *
   * @return \Drupal\mars_common\SVG\SVG|null
   *   The brand shape or null.
   */
  public function getBrandShapeWithoutFill(): ?SVG {
    $svg = $this->getSvgFor('brand_shape');
    if ($svg !== NULL) {
      $svg = $svg
        ->withoutFillInfo()
        ->withoutSizeInfo();
    }
    return $svg;
  }

  /**
   * Creates an SVG object for the given file field.
   *
   * @param string $field_name
   *   The field name.
   *
   * @return \Drupal\mars_common\SVG\SVG|null
   *   The svg or null if something went wrong or missing.
   */
  private function getSvgFor(string $field_name): ?SVG {
    $file_id = $this->getFileId($field_name);
    if (!$file_id) {
      return NULL;
    }

    try {
      $svg = $this->svgFactory->createSvgFromFileId($file_id);
    }
    catch (SVGException $e) {
      $this->logger->error($e->getMessage());
      return NULL;
    }
    return $svg;
  }

  /**
   * Prepare social links data.
   *
   * @return array
   *   Rendered menu.
   */
  public function socialLinks(): array {
    $social_menu_items = [];

    foreach ($this->themeSettings['social'] as $key => $social_settings) {
      if (!$social_settings['name'] ||
        !$social_settings['icon'] ||
        !is_array($social_settings['icon']) ||
        !$social_settings['link']) {
        continue;
      }

      $fid = reset($social_settings['icon']);

      try {
        $social_menu_items[$key]['title'] = $social_settings['name'];
        $social_menu_items[$key]['url'] = $social_settings['link'];
        $social_menu_items[$key]['icon'] = $this->svgFactory->createSvgFromFileId($fid);
      }
      catch (SVGException $e) {
        $this->logger->error($e->getMessage());
        unset($social_menu_items[$key]);
      }
    }

    return $social_menu_items;
  }

  /**
   * Return settings from theme configurator.
   *
   * @param string $setting
   *   Config setting name.
   * @param string $default
   *   Default setting value.
   *
   * @return string
   *   File contents.
   */
  public function getSettingValue(string $setting, string $default = '') {
    return $this->themeSettings[$setting] ?? $default;
  }

  /**
   * Returns file entity.
   *
   * @param string $field
   *   Config field name.
   *
   * @return \Drupal\file\Entity\File|null
   *   File entity.
   */
  private function getFileId(string $field): ?string {
    return $this->themeSettings[$field][0] ?? NULL;
  }

  /**
   * Creates an URL for a field if it's a File.
   *
   * @param string $field
   *   The name of the config field.
   *
   * @return \Drupal\Core\Url|null
   *   The url for the file, or NULL if it's not a file or not set.
   */
  public function getUrlForFile(string $field): ?Url {
    $file_id = $this->getFileId($field);
    $file = $this->fileStorage->load($file_id);
    if ($file instanceof File) {
      $pngAssetUri = $file->getFileUri();
      return Url::fromUri(file_create_url($pngAssetUri));
    }
    return NULL;
  }

}
