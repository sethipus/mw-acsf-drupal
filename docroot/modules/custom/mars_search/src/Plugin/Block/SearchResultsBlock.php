<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\mars_search\SearchHelperInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mars_common\ThemeConfiguratorParser;

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
   * Search helper.
   *
   * @var \Drupal\mars_search\SearchHelperInterface
   */
  protected $searchHelper;

  /**
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * The node view builder.
   *
   * @var \Drupal\node\NodeViewBuilder
   */
  protected $nodeViewBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_search.search_helper'),
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('entity_type.manager')->getViewBuilder('node')

    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    SearchHelperInterface $search_helper,
    ThemeConfiguratorParser $themeConfiguratorParser,
    EntityViewBuilderInterface $node_view_builder
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->searchHelper = $search_helper;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->nodeViewBuilder = $node_view_builder;

  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    // Results should be obtained from static cache.
    $query_search_results = $this->searchHelper->getSearchResults([], 'main_search');

    // Preparing search results.
    $build['#items'] = [];
    foreach ($query_search_results['results'] as $node) {
      $build['#items'][] = $this->nodeViewBuilder->view($node, 'card');
    }

    $file_divider_content = $this->themeConfiguratorParser->getFileContentFromTheme('graphic_divider');
    $border_radius = $this->themeConfiguratorParser->getSettingValue('button_style');

    $build['#theme_styles'] = 'drupal';
    $build['#graphic_divider'] = $file_divider_content ?? '';

    // "See all" link should be visible only if it makes sense.
    if ($query_search_results['resultsCount'] > count($build['#items'])) {
      $url = $this->searchHelper->getCurrentUrl();
      $url_options = $url->getOptions();
      $url_options['query']['see-all'] = 1;
      $url->setOptions($url_options);
      $build['#ajax_card_grid_link_text'] = $this->t('See all');
      $build['#ajax_card_grid_link_attributes']['href'] = $url->toString();
    }

    $build['#ajax_card_grid_heading'] = $this->t('All results');
    $build['#ajax_card_grid_border_radius'] = $border_radius ?? 0;
    $build['#theme'] = 'mars_search_search_results_block';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
