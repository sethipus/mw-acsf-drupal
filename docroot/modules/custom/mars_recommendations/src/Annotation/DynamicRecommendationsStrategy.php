<?php

namespace Drupal\mars_recommendations\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines dynamic_recommendations_strategy annotation object.
 *
 * @Annotation
 */
class DynamicRecommendationsStrategy extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title;

  /**
   * The description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * Fallback plugin that will be used if some plugin conditions missed.
   *
   * @var string
   */
  public $fallback_plugin;

}
