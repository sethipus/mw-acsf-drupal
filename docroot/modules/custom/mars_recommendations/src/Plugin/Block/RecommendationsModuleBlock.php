<?php

namespace Drupal\mars_recommendations\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_recommendations\RecommendationsService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Recommendations Module block.
 *
 * @Block(
 *   id = "recommendations_module",
 *   admin_label = @Translation("MARS: Recommendations Module"),
 *   category = @Translation("Mars Common"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   }
 * )
 */
class RecommendationsModuleBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Mars Recommendations Service.
   *
   * @var \Drupal\mars_recommendations\RecommendationsService
   */
  protected $recommendationsService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_recommendations.recommendations_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RecommendationsService $recommendations_service
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->recommendationsService = $recommendations_service;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'label_display' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $conf = $this->getConfiguration();
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#description' => $this->t('Defaults to <em>More <strong>&lt;Content Type&gt;</strong>s Like This</em>'),
      '#placeholder' => $this->t('More &lt;Content Type&gt;s Like This'),
      '#maxwidth' => 35,
      '#default_value' => $conf['title'] ?? NULL,
    ];

    $form['population'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Recommendations population'),
      '#tree' => TRUE,
    ];

    $form['population']['plugin_id'] = [
      '#type' => 'radios',
      '#title' => $this->t('Population Logic'),
      '#options' => $this->recommendationsService->getPopulationLogicOptions(),
      '#required' => TRUE,
      '#default_value' => $conf['population_plugin_id'] ?? NULL,
      '#ajax' => [
        'wrapper' => 'recommendations-population-configuration',
        'callback' => [$this, 'getPopulationLogicSettingsForm'],
      ],
    ];

    $form['population']['configuration'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'recommendations-population-configuration',
      ],
    ];

    $form['population']['configuration']['subform'] = [];

    $population_plugin_id = NULL;
    if ($form_state->has('population_plugin_id')) {
      $population_plugin_id = $form_state->get('population_plugin_id');
      $population_plugin_configuration = $form_state->get('population_plugin_configuration') ?? [];
    }
    elseif (!empty($conf['population_plugin_id'])) {
      $population_plugin_id = $conf['population_plugin_id'] ?? NULL;
      $population_plugin_configuration = $conf['population_plugin_configuration'] ?? [];
    }

    if ($population_plugin_id) {
      $plugin = $this->recommendationsService->getPopulationLogicPlugin($population_plugin_id, $population_plugin_configuration);

      $subform_state = SubformState::createForSubform($form['population']['configuration']['subform'], $form, $form_state);
      $form['population']['configuration']['subform'] = $plugin->buildConfigurationForm($form['population']['configuration']['subform'], $subform_state);

      $form_state->set('population_logic_plugin', $plugin);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    parent::blockValidate($form, $form_state);

    if ($form_state->getTriggeringElement()['#name'] == 'settings[population][plugin_id]') {
      $form_state->set('population_plugin_id', $form_state->getValue('population')['plugin_id']);
      $form_state->setRebuild();

      return;
    }

    if ($form_state->has('population_logic_plugin')) {
      /** @var \Drupal\mars_recommendations\RecommendationsLogicPluginInterface $plugin */
      $plugin = $form_state->get('population_logic_plugin');

      $subform_state = SubformState::createForSubform($form['population']['configuration']['subform'], $form, $form_state);
      $plugin->validateConfigurationForm($form['population']['configuration']['subform'], $subform_state);
    }

    $form_state->setError($form, 'Not working');
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->configuration['title'] = $form_state->getValue('title');
    $this->configuration['population_plugin_id'] = $form_state->getValue('population')['plugin_id'] ?? NULL;

    if ($form_state->has('population_logic_plugin')) {
      /** @var \Drupal\mars_recommendations\RecommendationsLogicPluginInterface $plugin */
      $plugin = $form_state->get('population_logic_plugin');

      $subform_state = SubformState::createForSubform($form['population']['configuration']['subform'], $form, $form_state);
      $plugin->submitConfigurationForm($form['population']['configuration']['subform'], $subform_state);

      $this->configuration['population_plugin_configuration'] = $subform_state->getValue('population_plugin_configuration');
    }
  }

  /**
   * Ajax callback for the Recommendations Logic selector.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The components wrapper render array.
   */
  public function getPopulationLogicSettingsForm(array $form, FormStateInterface $form_state) {
    return $form['settings']['population']['configuration'];
  }

}
