<?php

namespace Drupal\mars_search\Processors;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_search\SearchProcessFactoryInterface;
use Drupal\Core\Menu\MenuTreeParameters;

/**
 * Class SearchBuilder.
 */
class SearchBuilder implements SearchBuilderInterface, SearchProcessManagerInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Search processing factory.
   *
   * @var \Drupal\mars_search\SearchProcessFactoryInterface
   */
  protected $searchProcessor;

  /**
   * Search helper.
   *
   * @var \Drupal\mars_search\Processors\SearchHelperInterface
   */
  protected $searchHelper;

  /**
   * Search query parser.
   *
   * @var \Drupal\mars_search\Processors\SearchQueryParserInterface
   */
  protected $searchQueryParser;

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
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ThemeConfiguratorParser $themeConfiguratorParser,
    ConfigFactoryInterface $configFactory,
    SearchProcessFactoryInterface $searchProcessor
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->nodeViewBuilder = $this->entityTypeManager->getViewBuilder('node');
    $this->configFactory = $configFactory;
    $this->searchProcessor = $searchProcessor;
    $this->searchQueryParser = $this->searchProcessor->getProcessManager('search_query_parser');
    $this->searchHelper = $this->searchProcessor->getProcessManager('search_helper');
  }

  /**
   * {@inheritdoc}
   */
  public function getManagerId() {
    return 'search_builder';
  }

  /**
   * {@inheritdoc}
   */
  public function buildSearchResults(string $grid_type, array $config = [], string $grid_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID) {
    $build = [];

    // Getting default search options.
    $searchOptions = $this->searchQueryParser->parseQuery($grid_id);
    switch ($grid_type) {
      // Card Grid should include filter preset from configuration.
      case 'grid':
        $searchOptions['limit'] = 4;
        $searchOptions = $this->searchQueryParser->parseFilterPreset($searchOptions, $config);

        if (!empty($config['top_results_wrapper']['top_results'])) {
          $top_result_ids = array_map(function ($value) {
            return $value['target_id'];
          }, $config['top_results_wrapper']['top_results']);
          foreach ($this->entityTypeManager->getStorage('node')->loadMultiple($top_result_ids) as $top_result_node) {
            $build['#items'][] = $this->nodeViewBuilder->view($top_result_node, 'card');
          }
        }
        $searcher_key = "grid_{$grid_id}";
        break;

      case 'search_page':
        $searcher_key = "main_search";
        break;
    }

    // Getting and building search results.
    $query_search_results = $this->searchHelper->getSearchResults($searchOptions, $searcher_key);
    $build['#items'] = [];
    if ($query_search_results['resultsCount'] == 0) {
      $build['#no_results'] = $this->getSearchNoResult($searchOptions['keys'], $grid_type);
    }
    foreach ($query_search_results['results'] as $node) {
      $build['#items'][] = $this->nodeViewBuilder->view($node, 'card');
    }

    return [$searchOptions, $query_search_results, $build];
  }

  /**
   * Render search no result block.
   */
  public function getSearchNoResult($key, $grid_type) {
    $config = $this->configFactory->get('mars_search.search_no_results');
    $build = [
      '#no_results_heading' => str_replace('@keys', $key, $config->get('no_results_heading')),
      '#no_results_text' => $config->get('no_results_text'),
      '#theme' => 'mars_search_no_results',
    ];

    switch ($grid_type) {
      case 'search_page':
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
        $build['#no_results_links'] = $links;
        break;

      case 'grid':
        $build['#brand_border'] = $this->themeConfiguratorParser->getBrandBorder2();
        break;
    }

    return $build;
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

}
