<?php

namespace Drupal\mars_recommendations\Plugin\MarsRecommendationsLogic;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_recommendations\DynamicRecommendationsStrategyPluginManager;
use Drupal\mars_recommendations\RecommendationsLogicPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * Custom service dynamic recommendations strategy.
   *
   * @var Drupal\mars_recommendations\DynamicRecommendationsStrategyPluginManager
   */
  protected $strategyPluginManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.dynamic_recommendations_strategy'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
  $plugin_id,
  $plugin_definition,
  DynamicRecommendationsStrategyPluginManager $strategy_plugin_manager,
    EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);

    $this->strategyPluginManager = $strategy_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getRecommendations() {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getContextValue('node');

    // @todo Replace "default" with config value.
    $plugin_id = $node->getType() && $this->strategyPluginManager->hasDefinition($node->getType()) ? $node->getType() : 'default';

    /** @var \Drupal\mars_recommendations\DynamicRecommendationsStrategyInterface $plugin */
    $plugin = $this->strategyPluginManager->createInstance($plugin_id, ['limit' => $this->getResultsLimit()]);
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
