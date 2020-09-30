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
