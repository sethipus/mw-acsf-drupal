<?php

namespace Drupal\mars_lighthouse\Commands;

use Drupal\mars_lighthouse\LighthouseSyncService;
use Drush\Commands\DrushCommands;

/**
 * A drush command file.
 *
 * @package Drupal\mars_lighthouse\Commands
 */
class LighthouseSyncCommands extends DrushCommands {

  /**
   * Lighthouse sync service.
   *
   * @var \Drupal\mars_lighthouse\LighthouseSyncService
   */
  protected $lighthouseSyncService;

  /**
   * LighthouseSyncCommands constructor.
   */
  public function __construct(LighthouseSyncService $lighthouse_sync_service) {
    parent::__construct();
    $this->lighthouseSyncService = $lighthouse_sync_service;
  }

  /**
   * Drush command that lighthouse sync.
   *
   * @command lighthouse:sync
   * @aliases lsync
   * @option onebyone
   *   One by one sync.
   * @option bulk
   *   Bulk sync.
   * @usage lighthouse:sync --bulk
   */
  public function message($options = ['onebyone ' => FALSE, 'bulk' => FALSE]) {
    if ($options['onebyone']) {
      $this->lighthouseSyncService->syncLighthouseSite(TRUE);
      $this->output()->writeln('Finish sync.');
    }
    if ($options['bulk']) {
      $this->lighthouseSyncService->syncLighthouseSiteBulk();
      $this->output()->writeln('Finish sync.');
    }
    if (!$options['bulk'] && !$options['onebyone']) {
      $this->output()->writeln('Choose sync option "bulk" or "one by one"');
    }
  }

}
