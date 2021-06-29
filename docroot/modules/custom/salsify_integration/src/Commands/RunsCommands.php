<?php

namespace Drupal\salsify_integration\Commands;

use Drupal\salsify_integration\Run\RunResource;
use Drush\Commands\DrushCommands;

/**
 * Request Salsify for the new export.
 */
class RunsCommands extends DrushCommands {

  /**
   * Runs ID mesasge.
   */
  protected const MSG_RUNS_ID = 'Runs ID: %s';

  /**
   * Runs status mesasge.
   */
  protected const MSG_RUNS_STATUS = 'Runs status: %s';

  /**
   * Runs status mesasge.
   */
  protected const MSG_RUNS_URL = 'Export URL: %s';

  /**
   * Run resource.
   *
   * @var \Drupal\salsify_integration\Run\RunResource
   */
  protected $runResource;

  /**
   * Constructor.
   */
  public function __construct(
    RunResource $runResource
  ) {
    $this->runResource = $runResource;
  }

  /**
   * Drush command that displays the given text.
   *
   * @command salsify:runs:request
   * @aliases srreq
   * @usage salsify:runs:request or salsify:runs:request --langcode=en
   */
  public function request(array $options = ['json-output' => FALSE, 'langcode' => '']) {
    if ($run = $this->runResource->create($options['langcode'])) {
      if ($options['json-output']) {
        $output = json_encode([
          static::MSG_RUNS_ID => $run->id,
          static::MSG_RUNS_STATUS => $run->status,
        ]);
      }
      else {
        $output = [
          sprintf(static::MSG_RUNS_ID, $run->id),
          sprintf(static::MSG_RUNS_STATUS, $run->status),
        ];

      }
      $this->output()->writeln($output);
    }
  }

  /**
   * Drush command that displays the given text.
   *
   * @command salsify:runs:read
   * @aliases srread
   * @usage salsify:runs:read or salsify:runs:read --langcode=en
   */
  public function read(array $options = ['json-output' => FALSE, 'langcode' => '']) {
    if ($run = $this->runResource->read($options['langcode'])) {
      if ($options['json-output']) {
        $output = json_encode([
          static::MSG_RUNS_ID => $run->id,
          static::MSG_RUNS_STATUS => $run->status,
          static::MSG_RUNS_URL => $run->product_export_url,
        ]);
      }
      else {
        $output = [
          sprintf(static::MSG_RUNS_ID, $run->id),
          sprintf(static::MSG_RUNS_STATUS, $run->status),
          sprintf(static::MSG_RUNS_URL, $run->product_export_url),
        ];
      }
      $this->output()->writeln($output);
    }
  }

}
