<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\mars_search\SearchHelperInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_search\SearchQueryParserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;

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
   * List of vocabularies which are included in indexing.
   *
   * @var array
   */
  const TAXONOMY_VOCABULARIES = [
    'mars_brand_initiatives' => [
      'label' => 'Brand initiatives',
      'content_types' => ['article', 'recipe', 'landing_page', 'campaign'],
    ],
    'mars_occasions' => [
      'label' => 'Occasions',
      'content_types' => [
        'article', 'recipe', 'product', 'landing_page', 'campaign',
      ],
    ],
    'mars_flavor' => [
      'label' => 'Flavor',
      'content_types' => ['product'],
    ],
    'mars_format' => [
      'label' => 'Format',
      'content_types' => ['product'],
    ],
    'mars_diet_allergens' => [
      'label' => 'Diet & Allergens',
      'content_types' => ['product'],
    ],
    'mars_trade_item_description' => [
      'label' => 'Trade item description',
      'content_types' => ['product'],
    ],
  ];

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
   * Menu link tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  /**
   * Search query parser.
   *
   * @var \Drupal\mars_search\SearchQueryParserInterface
   */
  protected $searchQueryParser;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

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
      $container->get('entity_type.manager')->getViewBuilder('node'),
      $container->get('menu.link_tree'),
      $container->get('mars_search.search_query_parser'),
      $container->get('config.factory')
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
    EntityViewBuilderInterface $node_view_builder,
    MenuLinkTreeInterface $menu_link_tree,
    SearchQueryParserInterface $search_query_parser,
    ConfigFactoryInterface $configFactory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->searchHelper = $search_helper;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->nodeViewBuilder = $node_view_builder;
    $this->menuLinkTree = $menu_link_tree;
    $this->searchQueryParser = $search_query_parser;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $searchOptions = $this->searchQueryParser->parseQuery();

    // Results should be obtained from static cache.
    $query_search_results = $this->searchHelper->getSearchResults($searchOptions, 'main_search');
    // After this line $facetOptions and $searchOptions become different.
    $facetOptions = $searchOptions;
    unset($facetOptions['limit']);

    $facets_query = $this->searchHelper->getSearchResults($facetOptions, 'main_search_facet');

    // Preparing search results.
    $build['#items'] = [];
    foreach ($query_search_results['results'] as $node) {
      $build['#items'][] = $this->nodeViewBuilder->view($node, 'card');
    }
    if (count($build['#items']) == 0) {
      $build['#no_results'] = $this->getSearchNoResult();
    }

    // Build dataLayer attributes if search results are displayed for keys.
    $build['#attached']['drupalSettings']['dataLayer'] = [
      'searchPage' => 'search_page',
      'siteSearchResults' => [
        'siteSearchTerm' => $searchOptions['keys'],
        'siteSearchResults' => $query_search_results['resultsCount'],
      ],
    ];

    $file_divider_content = $this->themeConfiguratorParser->getGraphicDivider();

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
    [$build['#applied_filters_list'], $build['#filters']] = $this->searchHelper->processTermFacets($facets_query['facets'], self::TAXONOMY_VOCABULARIES, 1);
    $build['#theme'] = 'mars_search_search_results_block';
    $build['#attached']['library'][] = 'mars_search/datalayer.search';
    $build['#attached']['library'][] = 'mars_search/see_all_cards';
    return $build;
  }

  /**
   * Render search no result block.
   */
  private function getSearchNoResult() {
    $config = $this->configFactory->get('mars_search.search_no_results');
    $url = $this->searchHelper->getCurrentUrl();
    $url_options = $url->getOptions();
    $search_text = array_key_exists('search', $url_options['query']) ? reset($url_options['query']['search']) : '';

    $linksMenu = $this->buildMenu('error-page-menu');
    $links = [];
    foreach ($linksMenu as $linkMenu) {
      $links[] = [
        'content' => $linkMenu['title'],
        'attributes' => [
          'target' => '_self',
          'href' => $linkMenu['url'],
        ],
      ];
    }

    return [
      '#no_results_heading' => str_replace('@keys', $search_text, $config->get('no_results_heading')),
      '#no_results_text' => $config->get('no_results_text'),
      '#no_results_links' => $links,
      '#theme' => 'mars_search_no_results',
    ];
  }

  /**
   * Render menu by its name.
   *
   * @param string $menu_name
   *   Menu name.
   *
   * @return array
   *   Rendered menu.
   */
  protected function buildMenu($menu_name) {
    $menu_parameters = new MenuTreeParameters();
    $menu_parameters->setMaxDepth(1);

    // Get the tree.
    $tree = $this->menuLinkTree->load($menu_name, $menu_parameters);

    // Apply some manipulators (checking the access, sorting).
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkNodeAccess'],
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuLinkTree->transform($tree, $manipulators);

    // And the last step is to actually build the tree.
    $menu = $this->menuLinkTree->build($tree);
    $menu_links = [];
    if (!empty($menu['#items'])) {
      foreach ($menu['#items'] as $item) {
        array_push($menu_links, ['title' => $item['title'], 'url' => $item['url']->setAbsolute()->toString()]);
      }
    }
    return $menu_links;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
