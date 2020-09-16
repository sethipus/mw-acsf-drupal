<?php

namespace Drupal\mars_recommendations\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
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
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#description' => $this->t('Defaults to <em>More <strong>&lt;Content Type&gt;</strong>s Like This</em>'),
      '#placeholder' => $this->t('More &lt;Content Type&gt;s Like This'),
      '#maxwidth' => 35,
    ];

    $form['population_logic'] = [
      '#type' => 'radios',
      '#title' => $this->t('Population Logic'),
      '#options' => $this->recommendationsService->getPopulationLogicOptions(),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->configuration['title'] = $form_state->getValue('title');
  }

}
