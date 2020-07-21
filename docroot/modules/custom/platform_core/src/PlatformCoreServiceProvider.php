<?php

namespace Drupal\platform_core;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\platform_core\DependencyInjection\DisableDrushConfigImportTransformCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

/**
 * Platform core module service provider class.
 */
class PlatformCoreServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    $container->addCompilerPass(
      new DisableDrushConfigImportTransformCompilerPass(),
      PassConfig::TYPE_BEFORE_OPTIMIZATION,
      250
    );
  }

}
