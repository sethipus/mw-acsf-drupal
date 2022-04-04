<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mars_search\SearchProcessFactoryInterface;

/**
 * Provides a no results block for the search page.
 *
 * @Block(
 *   id = "search_faq_block",
 *   admin_label = @Translation("MARS: Search FAQs"),
 *   category = @Translation("Mars Search"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Current Node"))
 *   }
 * )
 */
class SearchFaqBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Search helper.
   *
   * @var \Drupal\mars_search\Processors\SearchHelperInterface
   */
  protected $searchHelper;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Templates builder service .
   *
   * @var \Drupal\mars_search\Processors\SearchBuilder
   */
  protected $searchBuilder;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * Theme configurator parser service.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  private $themeConfiguratorParser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_search.search_factory'),
      $container->get('config.factory'),
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
    SearchProcessFactoryInterface $searchProcessor,
    ConfigFactoryInterface $configFactory,
    LanguageHelper $language_helper,
    ThemeConfiguratorParser $theme_configurator_parser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->searchProcessor = $searchProcessor;
    $this->searchHelper = $this->searchProcessor->getProcessManager('search_helper');
    $this->searchBuilder = $this->searchProcessor->getProcessManager('search_builder');
    $this->languageHelper = $language_helper;
    $this->themeConfiguratorParser = $theme_configurator_parser;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['faq_title'] = [
      '#type' => 'textfield',
      '#title' => $this->languageHelper->translate('FAQ block title'),
      '#maxlength' => 55,
      '#required' => TRUE,
      '#default_value' => $config['faq_title'] ?? 'FAQs',
    ];

    $form['cta_button_label'] = [
      '#title' => $this->languageHelper->translate('CTA button label'),
      '#type' => 'textfield',
      '#size' => 200,
      '#required' => TRUE,
      '#default_value' => $config['cta_button_label'] ?? strtoupper($this->languageHelper->translate('See all')),
    ];

    $form['faq_filter_toggle'] = [
      '#type' => 'checkbox',
      '#title'         => $this->t('Turn off FAQ filter'),
      '#default_value' => $config['faq_filter_toggle'] ?? FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $config_no_results = $this->configFactory->get('mars_search.search_no_results');

    [$searchOptions, $query_search_results, $build] = $this->searchBuilder->buildSearchResults('faq');
    $build = array_merge($build, $this->searchBuilder->buildFaqFilters());

    $cta_button_label = $config['cta_button_label'] ?? strtoupper($this->languageHelper->translate('See all'));
    $cta_button_link = '/';
    // Extracting the node context.
    $context_node = $this->getContextValue('node');

    $render_default = [
      '#theme' => 'mars_search_faq_block',
      '#faq_filter_toggle' => !empty($config['faq_filter_toggle']) ? $config['faq_filter_toggle'] : '',
      '#faq_title' => $config['faq_title'],
      '#cta_button_label' => $cta_button_label,
      '#cta_button_link' => $cta_button_link,
      '#faq_ques_label' => $this->themeConfiguratorParser->getSettingValue('faq_ques_label'),
      '#faq_ans_label' => $this->themeConfiguratorParser->getSettingValue('faq_ans_label'),
      '#search_result_counter' => $query_search_results['resultsCount'],
      '#no_results_heading' => str_replace('@keys', $searchOptions['keys'], $this->languageHelper->translate($config_no_results->get('no_results_heading'))),
      '#no_results_text' => $this->languageHelper->translate($config_no_results->get('no_results_text')),
      '#data_layer' => [
        'search_term' => $searchOptions['keys'],
        'search_results' => $query_search_results['resultsCount'],
        'page_id' => $context_node->id(),
        'page_revision_id' => $context_node->getRevisionId(),
      ],
      '#attached' => [
        'library' => [
          'mars_search/datalayer_search',
          'mars_search/search_filter_faq',
          'mars_search/search_pager',
        ],
      ],
      '#graphic_divider' => $this->themeConfiguratorParser->getGraphicDivider(),
    ];

    return array_merge($render_default, $build);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getContextMapping() {
    $mapping = parent::getContextMapping();
    $mapping['node'] = $mapping['node'] ?? 'layout_builder.entity';
    return $mapping;
  }

}
