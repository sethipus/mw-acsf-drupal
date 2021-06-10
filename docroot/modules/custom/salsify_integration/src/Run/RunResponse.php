<?php

namespace Drupal\salsify_integration\Run;

/**
 * Run respons object.
 */
class RunResponse {

  /**
   * Running status.
   */
  public const STATUS_RUNNING = 'running';

  /**
   * Completed status.
   */
  public const STATUS_COMPLETED = 'completed';

  /**
   * Run ID.
   *
   * @var string
   */
  public $id;

  /**
   * Run status.
   *
   * @var string
   */
  public $status;

  /**
   * Product export URL.
   *
   * @var string
   * @codingStandardsIgnoreStart
   */
  public $product_export_url;
  /** @codingStandardsIgnoreEnd */

}
