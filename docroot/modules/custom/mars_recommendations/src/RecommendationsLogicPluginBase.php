<?php

namespace Drupal\mars_recommendations;

use Drupal\Core\Plugin\ContextAwarePluginBase;

/**
 * Base class for mars_recommendations_logic plugins.
 */
abstract class RecommendationsLogicPluginBase extends ContextAwarePluginBase implements RecommendationsLogicPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function label() {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
