<?php

namespace Drupal\mars_recommendations\Plugin\MarsRecommendationsLogic;

use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_recommendations\RecommendationsLogicPluginBase;

/**
 * Dynamic Recommendations Population Logic plugin.
 *
 * @MarsRecommendationsLogic(
 *   id = "dynamic",
 *   label = @Translation("Dynamic"),
 *   description = @Translation("Loads recommendations dynamically from context."),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   }
 * )
 */
class Dynamic extends RecommendationsLogicPluginBase {

  const DEFAULT_RESULTS_LIMIT = 4;

  /**
   * {@inheritdoc}
   */
  public function getResultsLimit(): int {
    return self::DEFAULT_RESULTS_LIMIT;
  }

  /**
   * {@inheritdoc}
   */
  public function getRecommendations() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array &$form, FormStateInterface $form_state) {
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $this->t('This plugin does not require a specific configuration.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $form_state->setValue('population_plugin_configuration', []);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Do nothing.
  }

}
