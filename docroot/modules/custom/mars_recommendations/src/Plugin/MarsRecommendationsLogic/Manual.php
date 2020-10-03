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
 *   description = @Translation("Allows to set a list of recommended nodes manually."),
 *   zone_types = {
 *     "flexible"
 *   }
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
    return array_map(function ($value) {
      return $this->nodeStorage->load($value);
    }, $this->configuration['nodes'] ?? []);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->has('items_count')) {
      $form_state->set('items_count', max(1, $form_state->get('items_count') ?? count($this->configuration['nodes'] ?? [])));
    }

    $form['nodes'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Recommended items'),
      '#tree' => TRUE,
      '#prefix' => '<div id="recommendations-manual-items">',
      '#suffix' => '</div>',
    ];

    for ($i = 0; $i < $form_state->get('items_count'); $i++) {
      $form['nodes'][$i] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['recommended-article-elements-container'],
        ],
      ];

      $form['nodes'][$i]['node'] = [
        '#type' => 'entity_autocomplete',
        '#title' => $this->t('Card'),
        '#title_display' => 'invisible',
        '#target_type' => 'node',
        '#selection_settings' => [
          'target_bundles' => [
            'article',
            'recipe',
            'product',
            'content_hub_page',
          ],
        ],
        '#default_value' => !empty($this->configuration['nodes'][$i]) ? $this->nodeStorage->load($this->configuration['nodes'][$i]) : NULL,
      ];

      $form['nodes'][$i]['remove'] = [
        '#type' => 'submit',
        '#name' => 'remove[' . $i . ']',
        '#value' => $this->t('Remove'),
        '#validate' => [[$this, 'validateRemoveItem']],
        '#ajax' => [
          'wrapper' => 'recommendations-manual-items',
          'callback' => [$this, 'ajaxCallback'],
        ],
      ];
    }

    if ($this->getResultsLimit() !== self::UNLIMITED && $form_state->get('items_count') < $this->getResultsLimit()) {
      $form['nodes']['add_more'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add more'),
        '#validate' => [[$this, 'validateAddItem']],
        '#ajax' => [
          'wrapper' => 'recommendations-manual-items',
          'callback' => [$this, 'ajaxCallback'],
        ],
      ];
    }

    $form['#attached']['library'][] = 'mars_recommendations/block.admin';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    unset($values['nodes']['add_more']);

    $form_state->setValue('population_plugin_configuration', [
      'nodes' => array_unique(
        array_map(function ($value) {
          return $value['node'];
        }, $values['nodes'])
      ),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Do nothing.
  }

  /**
   * Validation handler for Add More button.
   *
   * @param array $form
   *   Form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function validateAddItem(array &$form, FormStateInterface $form_state) {
    $items_count = $form_state->get('items_count');

    if ($this->getResultsLimit() !== self::UNLIMITED && $items_count < $this->getResultsLimit()) {
      $form_state->set('items_count', $items_count + 1);
    }

    $form_state->setRebuild();
  }

  /**
   * Validation handler for Remove button.
   *
   * @param array $form
   *   Form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function validateRemoveItem(array &$form, FormStateInterface $form_state) {
    $items_count = $form_state->get('items_count');

    $delta = array_slice($form_state->getTriggeringElement()['#parents'], -2, 1)[0];

    $input = $form_state->getUserInput();
    array_splice($input['settings']['population']['configuration']['subform']['nodes'], $delta, 1);
    $form_state->setUserInput($input);

    $values = $form_state->getValues();
    array_splice($values['settings']['population']['configuration']['subform']['nodes'], $delta, 1);
    $form_state->setValues($values);

    $form_state->set('items_count', $items_count - 1);

    $form_state->setRebuild();
  }

  /**
   * Callback for AJAX-powered buttons.
   *
   * @param array $form
   *   Form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return array
   *   AJAX Callback.
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['settings']['population']['configuration']['subform']['nodes'];
  }

}
