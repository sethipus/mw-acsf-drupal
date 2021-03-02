<?php

namespace Drupal\mars_sso\MarsSsoRequestSubscriber;

use Drupal\acsf_sso\EventSubscriber\SamlauthRequestSubscriber;

/**
 * Modifies the acsf_sso default config import.
 */
class MarsSsoRequestSubscriber extends SamlauthRequestSubscriber {

  /**
   * {@inheritdoc}
   */
  public function injectSamlConfig() {

    // Do this only when on an acsf environment.
    if (!isset($GLOBALS['gardens_site_settings'])) {
      return;
    }

    $GLOBALS['config']['samlauth.authentication'] = [];
  }

}
