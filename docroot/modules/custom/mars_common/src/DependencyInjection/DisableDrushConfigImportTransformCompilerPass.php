<?php

namespace Drupal\mars_common\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass for disabling import transformer in drush config import.
 */
class DisableDrushConfigImportTransformCompilerPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    /*
     * Have to disable to make config_ignore work on samlauth.authentication
     * config. Because of a bug in drush config import, ignored configurations
     * added during module install in config import will be overwritten if
     * import transformer is used in it. ImportTransformer will work on the
     * original activeConfigStore where the config_ignore module will set the
     * value of the ignored config based on the old configuration, but by
     * the time the actual import will happen the active configuration value is
     * changed and will be overwritten with the old value.
     */
    if ($container->hasDefinition('config.import.commands')) {
      $config_import_command = $container->getDefinition('config.import.commands');
      $config_import_command->removeMethodCall('setImportTransformer');
    }
  }

}
