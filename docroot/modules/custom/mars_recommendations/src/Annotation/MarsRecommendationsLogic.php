<?php

namespace Drupal\mars_recommendations\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Annotation class for Recommendations Population Logic plugins.
 *
 * Plugin Namespace: Plugin\MarsRecommendationsLogic.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class MarsRecommendationsLogic extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable plugin label.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * Plugin description.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description;

  /**
   * An integer to determine the weight of this plugin.
   *
   * @var int
   */
  public $weight = NULL;

  /**
   * An array of zone types of a block that the plugin support.
   *
   * @var array
   */
  public $zone_types = [];

}
