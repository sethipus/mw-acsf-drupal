<?php

namespace Drupal\mars_common;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class SocialLinks.
 *
 * @package Drupal\mars_common
 */
class SocialLinks {

  /**
   * Media storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

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
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory
  ) {
    $this->configFactory = $config_factory;
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->fileStorage = $entity_type_manager->getStorage('file');
  }

  /**
   * Prepare social links data.
   *
   * @return array
   *   Rendered menu.
   */
  public function getRenderedItems() {
    $social_menu_items = [];
    $theme_settings = $this->configFactory->get('emulsifymars.settings')->get();

    foreach ($theme_settings['social'] as $key => $social_settings) {
      $social_menu_items[$key]['title'] = $social_settings['name'];
      $social_menu_items[$key]['url'] = $social_settings['link'];
      if (!empty($social_settings['icon'])) {
        $fid = reset($social_settings['icon']);
        $file = $this->fileStorage->load($fid);
      }
      $social_menu_items[$key]['icon'] = !empty($file) ? $file->createFileUrl() : '';
    }
    return $social_menu_items;
  }

}
