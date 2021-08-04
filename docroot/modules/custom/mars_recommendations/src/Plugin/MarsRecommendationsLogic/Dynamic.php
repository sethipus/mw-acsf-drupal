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
 *   },
 *   zone_types = {
 *     "fixed",
 *     "flexible"
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
    /** @var \Drupal\mars_recommendations\DynamicRecommendationsStrategyPluginManager $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.dynamic_recommendations_strategy');

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getContextValue('node');

    // @todo Replace "default" with config value.
    $plugin_id = $node->getType() && $plugin_manager->hasDefinition($node->getType()) ? $node->getType() : 'default';

    /** @var \Drupal\mars_recommendations\DynamicRecommendationsStrategyInterface $plugin */
    $plugin = $plugin_manager->createInstance($plugin_id, ['limit' => $this->getResultsLimit()]);
    $plugin->setContext('node', $this->getContext('node'));

    return $plugin->generate();
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
