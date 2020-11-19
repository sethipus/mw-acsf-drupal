<?php

namespace Drupal\mars_common\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\mars_common\ThemeOverride\ThemeOverrideService;

/**
 * Defines the ThemeOverrideContext service.
 *
 * Cache context ID: 'theme_override'.
 */
class ThemeOverrideContext implements CacheContextInterface {

  /**
   * Cache context name for social links.
   */
  const NAME = 'theme_override';

  /**
   * Theme override service.
   *
   * @var \Drupal\mars_common\ThemeOverride\ThemeOverrideService
   */
  private $overrideService;

  /**
   * Constructs a new ThemeOverrideContext class.
   *
   * @param \Drupal\mars_common\ThemeOverride\ThemeOverrideService $override_service
   *   Theme override service.
   */
  public function __construct(ThemeOverrideService $override_service) {
    $this->overrideService = $override_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Theme override');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $override = $this->overrideService->getCurrentOverride();
    return $override->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    $override = $this->overrideService->getCurrentOverride();
    $cacheable_metadata = new CacheableMetadata();
    return $cacheable_metadata->addCacheableDependency($override);
  }

}
