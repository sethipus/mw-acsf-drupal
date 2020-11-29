<?php

namespace Drupal\mars_common;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\mars_common\DependencyInjection\DisableDrushConfigImportTransformCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

/**
 * Platform core module service provider class.
 */
class MarsCommonServiceProvider extends ServiceProviderBase {

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
