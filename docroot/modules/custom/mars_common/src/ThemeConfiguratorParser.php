<?php

namespace Drupal\mars_common;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\Entity\File;

/**
 * Class ThemeConfiguratorParser.
 *
 * @package Drupal\mars_common
 */
class ThemeConfiguratorParser {

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * File storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * Theme settings.
   *
   * @var array
   */
  protected $themeSettings;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory
  ) {
    $this->configFactory = $config_factory;
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->themeSettings = $this->configFactory->get('emulsifymars.settings')->get();
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
    if (!empty($file)) {
      $filePath = file_create_url($file->uri->value);
      return !empty($filePath) && file_exists($filePath) ? file_get_contents($filePath) : '';
    }

    return '';
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
      if (!$social_settings['name']) {
        continue;
      }
      $social_menu_items[$key]['title'] = $social_settings['name'];
      $social_menu_items[$key]['url'] = $social_settings['link'];
      if (!empty($social_settings['icon']) && is_array($social_settings['icon'])) {
        $fid = reset($social_settings['icon']);
        $file = $this->fileStorage->load($fid);
      }
      $social_menu_items[$key]['icon'] = !empty($file) ? $file->createFileUrl() : '';
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
  public function getFileFromTheme(string $field): ?File {
    if (!isset($this->themeSettings[$field][0])) {
      return NULL;
    }

    $configField = $this->themeSettings[$field][0];
    return $this->fileStorage->load($configField);
  }

}
