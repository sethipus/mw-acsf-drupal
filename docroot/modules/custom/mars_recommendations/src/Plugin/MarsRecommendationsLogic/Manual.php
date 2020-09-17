<?php

namespace Drupal\mars_recommendations\Plugin\MarsRecommendationsLogic;

use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_recommendations\RecommendationsLogicPluginBase;

/**
 * Dynamic Recommendations Population Logic plugin.
 *
 * @MarsRecommendationsLogic(
 *   id = "manual",
 *   label = @Translation("Manual"),
 *   description = @Translation("Allows to set a list of recommended nodes manually.")
 * )
 */
class Manual extends RecommendationsLogicPluginBase {

  const DEFAULT_RESULTS_LIMIT = 16;

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
  public function buildConfigurationForm(&$form, FormStateInterface $form_state) {
    $form['nodes'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Recommended items'),
      '#tree' => TRUE,
      '#prefix' => '',
    ];

    $form['nodes'][] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Card'),
      '#target_type' => 'node',
      '#selection_settings' => [
        'target_bundles' => ['article', 'recipe', 'product'],
      ],
    ];

    $form['nodes']['add_more'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add more'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(&$form, FormStateInterface $form_state) {
    // Do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(&$form, FormStateInterface $form_state) {
    // Do nothing.
  }

}
