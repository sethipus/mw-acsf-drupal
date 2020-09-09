<?php

namespace Drupal\mars_common;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

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
    return $this->themeSettings['logo']['path'];
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
    if (!isset($this->themeSettings[$field][0])) {
      return '';
    }

    $configField = $this->themeSettings[$field][0];
    $file = $this->fileStorage->load($configField);
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
    $svgContent = $this->getFileContentFromTheme($field);
    $svgContent = preg_replace('/\S*(fill=[\'"]url\(#\S*\)[\'"])\S*/', 'fill="url(#' . $id . ')"', $svgContent);
    $svgContent = preg_replace('/\S*(id=[\'"]\S*[\'"])\S*/', 'id="' . $id . '"', $svgContent);
    return $svgContent;
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
   *
   * @return string
   *   File contents.
   */
  public function getSettingValue(string $setting) {
    return $this->themeSettings[$setting] ?? '';
  }

}
