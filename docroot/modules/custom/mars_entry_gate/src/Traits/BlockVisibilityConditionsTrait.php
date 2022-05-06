<?php

namespace Drupal\mars_entry_gate\Traits;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Form\FormStateInterface;

/**
 * Trait BlockVisibilityConditionsTrait is responsible for block visibility.
 *
 * @package Drupal\mars_entry_gate\Traits
 */
trait BlockVisibilityConditionsTrait {

  /**
   * Build and evaluate block visibility conditions.
   *
   * @param array $visibility_config
   *   Block visibility configuration.
   *
   * @return bool
   *   Return conditions evaluation results.
   */
  public static function ensureShouldBlockRender(array $visibility_config): bool {
    $conditions = [];
    $contexts = \Drupal::service('context.repository')->getRuntimeContexts(['@node.node_route_context:node']);
    $node_context = reset($contexts)->getContextValue('node');
    $condition_plugin_manager = \Drupal::service('plugin.manager.condition');
    foreach ($visibility_config as $condition_plugin_id => $config) {
      if ($condition_plugin_id === 'entity_bundle:node') {
        $config = array_merge($config, ['context' => ['node' => $node_context]]);
      }
      $conditions[$condition_plugin_id] = $condition_plugin_manager->createInstance($condition_plugin_id, $config);
    }
    if (empty($conditions)) {
      return TRUE;
    }
    return static::resolveConditions($conditions, 'and');
  }

  /**
   * Resolves the given conditions based on the condition logic ('and'/'or').
   *
   * Ported from Core's condition plugin system.
   *
   * @param \Drupal\Core\Condition\ConditionInterface[] $conditions
   *   A set of conditions.
   * @param string $condition_logic
   *   The logic used to compute access, either 'and' or 'or'.
   *
   * @return bool
   *   Whether these conditions grant or deny access.
   *
   * @see \Drupal\Core\Condition\ConditionAccessResolverTrait::resolveConditions()
   */
  public static function resolveConditions(array $conditions, string $condition_logic): bool {
    foreach ($conditions as $condition) {
      try {
        $pass = $condition->execute();
      }
      catch (ContextException $e) {
        // If a condition is missing context and is not negated, consider that a
        // fail.
        $pass = $condition->isNegated();
      }

      // If a condition fails and all conditions were needed, deny access.
      if (!$pass && $condition_logic == 'and') {
        return FALSE;
      }
      // If a condition passes and only one condition was needed, grant access.
      elseif ($pass && $condition_logic == 'or') {
        return TRUE;
      }
    }

    // Return TRUE if logic was 'and', meaning all rules passed.
    // Return FALSE if logic was 'or', meaning no rule passed.
    return $condition_logic == 'and';
  }

  /**
   * Helper function for building the visibility form. Ported from block form.
   *
   * Don't forget to inject \Drupal\Core\Condition\ConditionManager service.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param mixed $visibility_config
   *   The visibility configuration.
   *
   * @see \Drupal\block\BlockForm::buildVisibilityInterface()
   */
  protected function buildVisibilityInterface(array &$form, FormStateInterface $form_state, $visibility_config) {
    $form['visibility_tabs'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Visibility'),
      '#parents' => ['visibility_tabs'],
      '#attached' => [
        'library' => [
          'block/drupal.block',
        ],
      ],
    ];
    $definitions = $this->conditionPluginManager->getFilteredDefinitions('block_ui', $form_state->getTemporaryValue('gathered_contexts'), []);
    if (!empty($definitions)) {
      foreach ($definitions as $condition_id => $definition) {
        // Don't display non-requested conditions.
        if ($condition_id !== 'entity_bundle:node' && $condition_id !== 'request_path') {
          continue;
        }

        /** @var \Drupal\Core\Condition\ConditionInterface $condition */
        $condition = $this->conditionPluginManager->createInstance($condition_id, $visibility_config[$condition_id] ?? []);
        $form_state->set(['conditions', $condition_id], $condition);
        $condition_form = $condition->buildConfigurationForm([], $form_state);
        $condition_form['#type'] = 'details';
        $condition_form['#title'] = $condition->getPluginDefinition()['label'];
        $condition_form['#group'] = 'visibility_tabs';
        $condition_form['#tree'] = TRUE;
        $condition_form['negate']['#description'] = $this->t('The "negate/hide" option applies settings vice versa, e.g. - disables the block for selected content type/page, but enables the block if no content types/pages specified.');
        $form[$condition_id] = $condition_form;
      }
    }

    if (isset($form['request_path'])) {
      $form['request_path']['#title'] = $this->t('Pages');
      $form['request_path']['negate']['#type'] = 'radios';
      $form['request_path']['negate']['#default_value'] = (int) $form['request_path']['negate']['#default_value'];
      $form['request_path']['negate']['#title_display'] = 'invisible';
      $form['request_path']['negate']['#options'] = [
        $this->t('Show for the listed pages'),
        $this->t('Hide for the listed pages'),
      ];
    }
  }

  /**
   * Set visibility configuration items.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   * @param object $configuration
   *   The configuration object.
   */
  public function setVisibilityFieldsConfiguration(FormStateInterface $form_state, object &$configuration) {
    $bundle_visibility = $form_state->getValue('entity_bundle:node');
    $bundle_visibility['id'] = 'entity_bundle:node';
    $request_path_visibility = $form_state->getValue('request_path');
    $request_path_visibility['id'] = 'request_path';
    $configuration->set('visibility', [
      'entity_bundle:node' => $bundle_visibility,
      'request_path' => $request_path_visibility,
    ]);
  }

}
