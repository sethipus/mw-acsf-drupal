<?php

namespace Drupal\mars_seo\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines mars_json_ld_strategy annotation object.
 *
 * @Annotation
 */
class JsonLdStrategy extends Plugin {

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
   * Weight of the plugin.
   *
   * Heaviest plugin will be used first.
   *
   * @var int
   */
  public $weight = 0;

}
