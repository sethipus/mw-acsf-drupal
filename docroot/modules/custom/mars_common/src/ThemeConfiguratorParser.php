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
    if (!$this->themeSettings) {
      $this->themeSettings = $this->configFactory->get('emulsifymars.settings')
        ->get();
    }

    if (!isset($this->themeSettings[$field][0])) {
      return '';
    }

    $configField = $this->themeSettings[$field][0];
    $file = $this->fileStorage->load($configField);
    if ($file !== NULL) {
      $filePath = file_create_url($file->uri->value);
      return file_get_contents($filePath);
    }

    return '';
  }

}
