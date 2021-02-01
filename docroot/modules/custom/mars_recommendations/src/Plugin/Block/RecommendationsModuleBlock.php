<?php

namespace Drupal\mars_recommendations\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\layout_builder\Form\ConfigureBlockFormBase;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_common\Traits\OverrideThemeTextColorTrait;
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

  use OverrideThemeTextColorTrait;

  /**
   * Mars Recommendations Service.
   *
   * @var \Drupal\mars_recommendations\RecommendationsService
   */
  protected $recommendationsService;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * Theme configurator parser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_recommendations.recommendations_service'),
      $container->get('mars_common.language_helper'),
      $container->get('mars_common.theme_configurator_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RecommendationsService $recommendations_service,
    LanguageHelper $language_helper,
    ThemeConfiguratorParser $theme_configurator_parser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->recommendationsService = $recommendations_service;
    $this->languageHelper = $language_helper;
    $this->themeConfiguratorParser = $theme_configurator_parser;
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
    if (!isset($this->configuration['population_plugin_id'])) {
      return [];
    }

    $plugin = $this
      ->recommendationsService
      ->getPopulationLogicPlugin($this->configuration['population_plugin_id'], $this->configuration['population_plugin_configuration'] ?? []);

    // Inject runtime contexts.
    if ($plugin instanceof ContextAwarePluginInterface) {
      $plugin->setContext('node', $this->getContext('node'));
    }

    $node = $this->getContextValue('node');
    $title = empty($this->configuration['title'])
      ? $this->languageHelper->translate('More @types Like This', ['@type' => $node->type->entity->label()])
      : $this->languageHelper->translate($this->configuration['title']);
    $text_color_override = FALSE;
    if (!empty($this->configuration['override_text_color']['override_color'])) {
      $text_color_override = self::$overrideColor;
    }
    $raw_rendered_recommendations = $plugin->getRenderedRecommendations();
    $rendered_recommendations_with_color_override = [];
    if (!empty($text_color_override && !empty($raw_rendered_recommendations))) {
      foreach ($raw_rendered_recommendations as $item) {
        $rendered_recommendations_with_color_override[] = array_merge($item, ['#text_color_override' => $text_color_override]);
      }
    }

    return [
      '#theme' => 'recommendations_module_block',
      '#title' => $title,
      '#graphic_divider' => $this->themeConfiguratorParser->getGraphicDivider(),
      '#brand_border' => $this->themeConfiguratorParser->getBrandBorder2(),
      '#recommended_items' => $rendered_recommendations_with_color_override ?? $raw_rendered_recommendations,
      '#text_color_override' => $text_color_override,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $conf = $this->getConfiguration();
    $form = parent::buildConfigurationForm($form, $form_state);
    $form_object = $form_state->getFormObject();

    if ($form_object instanceof ConfigureBlockFormBase) {
      /** @var \Drupal\layout_builder\SectionStorageInterface $section_storage */
      [$section_storage, $delta] = $form_state->getBuildInfo()['args'];

      $form_state->set('layout_id', $section_storage->getSection($delta)->getLayoutId());

      $contexts = $section_storage->getContextValues();

      /** @var \Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay|\Drupal\Core\Entity\EntityInterface $entity */
      $form_state->set('entity', $contexts['entity'] ?? $contexts['display'] ?? NULL);
    }

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#description' => $this->t('Defaults to <em>More <strong>&lt;Content Type&gt;</strong>s Like This</em>'),
      '#placeholder' => $this->t('More &lt;Content Type&gt;s Like This'),
      '#maxlength' => 55,
      '#default_value' => $conf['title'] ?? NULL,
    ];

    $form['population'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Recommendations population'),
      '#tree' => TRUE,
    ];

    $options = $this->recommendationsService->getPopulationLogicOptions($form_state->get('layout_id'), $form_state->get('entity'));

    $default_value = $conf['population_plugin_id'] ?? NULL;
    if (!$default_value && count($options) == 1) {
      $default_value = array_key_first($options);
      $form_state->set('population_plugin_id', $default_value);
    }

    $form['population']['plugin_id'] = [
      '#type' => 'radios',
      '#title' => $this->t('Population Logic'),
      '#options' => $options,
      '#required' => TRUE,
      '#default_value' => $default_value,
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
      $plugin = $this->recommendationsService->getPopulationLogicPlugin($population_plugin_id, $population_plugin_configuration ?? []);

      $subform_state = SubformState::createForSubform($form['population']['configuration']['subform'], $form, $form_state);
      $form['population']['configuration']['subform'] = $plugin->buildConfigurationForm($form['population']['configuration']['subform'], $subform_state);

      $form_state->set('population_logic_plugin', $plugin);
    }

    $this->buildOverrideColorElement($form, $conf);

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

      $form_state->setValue('population_plugin_configuration', $subform_state->getValue('population_plugin_configuration'));
    }
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

      $subform_state = SubformState::createForSubform($form['settings']['population']['configuration']['subform'], $form, $form_state);
      $plugin->submitConfigurationForm($form['settings']['population']['configuration']['subform'], $subform_state);

      $this->configuration['population_plugin_configuration'] = $form_state->getValue('population_plugin_configuration');
    }
    $this->configuration['override_text_color'] = $form_state->getValue('override_text_color');
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
