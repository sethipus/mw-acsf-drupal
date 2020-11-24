<?php

namespace Drupal\mars_sso\MarsSsoUserSyncSubscriber;

use Drupal\samlauth\Event\SamlauthUserSyncEvent;

/**
 * Overriding the event subscriber that syncs user props on a user_sync event.
 */
class MarsSsoUserSyncSubscriber extends SamlauthUserSyncSubscriber {

  /**
   * Overriding the parent method.
   */
  public function onUserSync(SamlauthUserSyncEvent $event) {

  }

}
