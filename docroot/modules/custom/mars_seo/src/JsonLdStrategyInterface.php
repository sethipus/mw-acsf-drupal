<?php

namespace Drupal\mars_seo;

use Drupal\Core\Plugin\ContextAwarePluginInterface;

/**
 * Interface for Mars JSON LD strategy plugins.
 */
interface JsonLdStrategyInterface extends ContextAwarePluginInterface {

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label();

  /**
   * Checks if plugin is compatible with current contexts.
   *
   * @return bool
   *   Plugin can be used to generate JSON code if TRUE.
   */
  public function isApplicable();

  /**
   * Returns node bundles supported by the plugin.
   *
   * @return array
   *   Bundles list.
   */
  public function supportedBundles();

  /**
   * Returns Structured data for node.
   *
   * @return array
   *   Structured data.
   */
  public function getStructuredData();

}
