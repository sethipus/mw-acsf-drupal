<?php

namespace Drupal\mars_common;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

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
   * Default constructor for ThemeConfiguratorParser.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory
  ) {
    $this->config = $config_factory->get('emulsifymars.settings');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->themeSettings = $this->config->get();
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
   * Returns file entity content.
   *
   * @param string $field
   *   Config field name.
   *
   * @return string
   *   File contents.
   */
  public function getFileContentFromTheme(string $field): string {
    $file = $this->getFileFromTheme($field);
    return $this->readContentFromFile($file);
  }

  /**
   * Returns file entity content with selected color.
   *
   * @param string $field
   *   Config field name.
   * @param string $id
   *   Element id.
   *
   * @return string
   *   File contents.
   */
  public function getFileWithId(string $field, string $id): string {
    $fileContent = $this->getFileContentFromTheme($field);
    $fileContent = preg_replace('/\S*(fill=[\'"]url\(#\S*\)[\'"])/', 'fill="url(#' . $id . ')"', $fileContent);
    $fileContent = preg_replace('/\S*(id=[\'"]\S*[\'"])\S*/', 'id="' . $id . '"', $fileContent);
    return $fileContent;
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
      $file = $this->fileStorage->load($fid);
      if (!empty($file)) {
        $social_menu_items[$key]['title'] = $social_settings['name'];
        $social_menu_items[$key]['url'] = $social_settings['link'];
        $social_menu_items[$key]['icon'] = $this->readContentFromFile($file);
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
  private function getFileFromTheme(string $field): ?File {
    if (!isset($this->themeSettings[$field][0])) {
      return NULL;
    }

    $configField = $this->themeSettings[$field][0];
    return $this->fileStorage->load($configField);
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
    $pngAssetFile = $this->getFileFromTheme($field);
    if ($pngAssetFile instanceof File) {
      $pngAssetUri = $pngAssetFile->getFileUri();
      return Url::fromUri(file_create_url($pngAssetUri));
    }
    return NULL;
  }

  /**
   * Reads the content of a file entity.
   *
   * @param \Drupal\file\Entity\File|null $file
   *   File entity.
   *
   * @return string
   *   The content of the file or empty on error.
   */
  private function readContentFromFile(?File $file) {
    $content = '';
    if ($file !== NULL) {
      $filePath = $file->getFileUri();
      $content = !empty($filePath) && file_exists($filePath) ? file_get_contents($filePath) : '';
    }
    return (string) $content;
  }

}
