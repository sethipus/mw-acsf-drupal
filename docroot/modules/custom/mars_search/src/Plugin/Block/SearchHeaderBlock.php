<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mars_common\LanguageHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_search\SearchProcessFactoryInterface;

/**
 * Provides a search page header block.
 *
 * @Block(
 *   id = "search_header_block",
 *   admin_label = @Translation("MARS: Search page header"),
 *   category = @Translation("Mars Search")
 * )
 */
class SearchHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * Search processing factory.
   *
   * @var \Drupal\mars_search\SearchProcessFactoryInterface
   */
  protected $searchProcessor;

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('mars_search.search_factory'),
      $container->get('mars_common.language_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ThemeConfiguratorParser $themeConfiguratorParser,
    SearchProcessFactoryInterface $searchProcessor,
    LanguageHelper $language_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->searchProcessor = $searchProcessor;
    $this->searchBuilder = $this->searchProcessor->getProcessManager('search_builder');
    $this->languageHelper = $language_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $build = $this->searchBuilder->buildSearchHeader($config);
    if(!empty($config['exclude_products'])){
      $exclude_products_arr = explode(',',$config['exclude_products']);
      $exclude_count = count($exclude_products_arr);
      $build['#search_filters'][0]['count'] -= $exclude_count;
    }
    $build['#search_header_heading'] = $config['search_header_heading'] ?? $this->languageHelper->translate('What are you looking for?');
    $build['#brand_border'] = $this->themeConfiguratorParser->getBrandBorder();
    $build['#brand_shape'] = $this->themeConfiguratorParser->getBrandShapeWithoutFill();
    $build['#filter_title_transform'] = $this->themeConfiguratorParser->getSettingValue('facets_text_transform', 'uppercase');
    $build['#theme'] = 'mars_search_header';
    $build['#attached']['library'][] = 'mars_search/search_filter_search_page';

    return $build;
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
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $character_limit_config = \Drupal::config('mars_common.character_limit_page');
    $config = $this->getConfiguration();

    $form['search_header_heading'] = [
      '#type' => 'textfield',
      '#title' => $this->languageHelper->translate('Search heading title'),
      '#maxlength' => !empty($character_limit_config->get('search_page_header_title')) ? $character_limit_config->get('search_page_header_title') : 55,
      '#required' => TRUE,
      '#default_value' => $config['search_header_heading'] ?? $this->languageHelper->translate('What are you looking for?'),
    ];

    $form['search_header_placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->languageHelper->translate('Search input hint'),
      '#required' => TRUE,
      '#default_value' => $config['search_header_placeholder'] ?? $this->languageHelper->translate('Search products, recipes, articles...'),
    ];

    $form['exclude_products'] = [
      '#title' => $this->languageHelper->translate('Exclude products'),
      '#type' => 'textfield',
      '#size' => 200,
      '#default_value' => $config['exclude_products'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * This method processes the blockForm() form fields when the block
   * configuration form is submitted.
   *
   * The blockValidate() method can be used to validate the form submission.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues());
  }

}
