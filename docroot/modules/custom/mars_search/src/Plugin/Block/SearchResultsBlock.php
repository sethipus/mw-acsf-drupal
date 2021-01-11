<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\mars_common\LanguageHelper;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_search\SearchProcessFactoryInterface;

/**
 * Provides a search page results block.
 *
 * @Block(
 *   id = "search_results_block",
 *   admin_label = @Translation("MARS: Search page results"),
 *   category = @Translation("Mars Search")
 * )
 */
class SearchResultsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

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
      $container->get('mars_search.search_factory'),
      $container->get('mars_common.theme_configurator_parser'),
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
    SearchProcessFactoryInterface $searchProcessor,
    ThemeConfiguratorParser $themeConfiguratorParser,
    LanguageHelper $language_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->searchProcessor = $searchProcessor;
    $this->searchHelper = $this->searchProcessor->getProcessManager('search_helper');
    $this->searchBuilder = $this->searchProcessor->getProcessManager('search_builder');
    $this->languageHelper = $language_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    [$searchOptions, $query_search_results, $build] = $this->searchBuilder->buildSearchResults('search_page');

    // Results will be populated after ajax request. It's not possible to
    // know right desktop type without page inner width.
    $build['#items'] = [];
    $query_search_results['results'] = [];

    $build = array_merge($build, $this->searchBuilder->buildSearchFacets());

    // "See more" link should be visible only if it makes sense.
    $build['#ajax_card_grid_link_text'] = $this->t('See more');
    $build['#ajax_card_grid_link_attributes']['href'] = '/';
    if ($query_search_results['resultsCount'] > count($build['#items'])) {
      $build['#ajax_card_grid_link_attributes']['class'] = 'active';
    }

    // Build dataLayer attributes if search results are displayed for keys.
    $build['#data_layer'] = [
      'search_term' => $searchOptions['keys'],
      'search_results' => $query_search_results['resultsCount'],
    ];

    $file_divider_content = $this->themeConfiguratorParser->getGraphicDivider();
    $build['#theme_styles'] = 'drupal';
    $build['#results_key_header'] = !empty($searchOptions['keys'] && $query_search_results['resultsCount'] > 0)
                                    ? $this->languageHelper->translate('Results for: ') . $searchOptions['keys']
                                    : '';
    $build['#graphic_divider'] = $file_divider_content ?? '';
    $build['#ajax_card_grid_heading'] = $this->t('All results');
    $build['#theme'] = 'mars_search_search_results_block';
    $build['#attached']['library'][] = 'mars_search/datalayer_search';
    $build['#attached']['library'][] = 'mars_search/search_pager';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
