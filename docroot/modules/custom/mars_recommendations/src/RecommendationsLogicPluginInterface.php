<?php

namespace Drupal\mars_recommendations;

use Drupal\Core\Form\FormStateInterface;

/**
 * An interface for Recommendations Population Logic plugins.
 */
interface RecommendationsLogicPluginInterface {

  /**
   * Do not limit recommendations result quantity.
   *
   * @var int
   */
  const UNLIMITED = -1;

  /**
   * Returns results limit for Recommendations Population Logic plugin.
   *
   * @return int
   *   Results limit.
   */
  public function getResultsLimit(): int;

  /**
   * Generates Recommendations Population Logic plugin configuration form.
   *
   * @param array $form
   *   Form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return array
   *   Form's render array.
   */
  public function buildConfigurationForm(array &$form, FormStateInterface $form_state);

  /**
   * Validation handler for plugin configuration form.
   *
   * @param array $form
   *   Form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state);

  /**
   * Submit handler for plugin configuration form.
   *
   * @param array $form
   *   Form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state);

  /**
   * Loads list of recommended nodes from the context.
   *
   * @return \Drupal\node\Entity\Node[]
   *   List of recommended nodes.
   */
  public function getRecommendations();

  /**
   * Loads list of recommended nodes render arrays from the context.
   *
   * @return array
   *   List of recommended node render arrays.
   */
  public function getRenderedRecommendations();

}
