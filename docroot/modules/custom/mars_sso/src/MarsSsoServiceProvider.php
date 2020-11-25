<?php

namespace Drupal\mars_sso;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the acsf_sso ServiceProviderBase.
 */
class MarsSsoServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Disabling the user sync.
    $definition = $container->getDefinition('acsf_sso.samlauth_request_subscriber');
    $definition->setClass('Drupal\mars_sso\MarsSsoRequestSubscriber');

    // Disabling the role mapping.
    $definition = $container->getDefinition('acsf_sso.samlauth_user_sync_subscriber');
    $definition->setClass('Drupal\mars_sso\MarsSsoUserSyncSubscriber');
  }

}
