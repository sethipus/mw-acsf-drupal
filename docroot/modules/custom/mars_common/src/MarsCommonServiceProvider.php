<?php

namespace Drupal\mars_common;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\mars_common\DependencyInjection\DisableDrushConfigImportTransformCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Reference;

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

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('poll.post_render_cache');
    $definition->setClass('Drupal\mars_common\MarsPollPostRenderCache');
    $definition->setArguments([
      new Reference('entity_type.manager'),
      new Reference('class_resolver'),
      new Reference('form_builder'),
    ]);
  }

}
