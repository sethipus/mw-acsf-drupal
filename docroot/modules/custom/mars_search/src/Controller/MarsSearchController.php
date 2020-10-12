<?php

namespace Drupal\mars_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\mars_search\SearchHelperInterface;
use Drupal\mars_search\SearchQueryParserInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;

/**
 * Provides a controllers for search functionality.
 */
class MarsSearchController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Menu link tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  /**
   * Search helper.
   *
   * @var \Drupal\mars_search\SearchHelperInterface
   */
  protected $searchHelper;

  /**
   * Search query parser.
   *
   * @var \Drupal\mars_search\SearchQueryParserInterface
   */
  protected $searchQueryParser;

  /**
   * A view builder instance.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * Creates a new AutocompleteController instance.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\mars_search\SearchHelperInterface $search_helper
   *   Search helper.
   * @param \Drupal\mars_search\SearchQueryParserInterface $search_query_parser
   *   Search helper.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_link_tree
   *   Menu Link tree.
   */
  public function __construct(
    RendererInterface $renderer,
    SearchHelperInterface $search_helper,
    SearchQueryParserInterface $search_query_parser,
    MenuLinkTreeInterface $menu_link_tree
  ) {
    $this->renderer = $renderer;
    $this->searchHelper = $search_helper;
    $this->searchQueryParser = $search_query_parser;
    $this->viewBuilder = $this->entityTypeManager()->getViewBuilder('node');
    $this->menuLinkTree = $menu_link_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('mars_search.search_helper'),
      $container->get('mars_search.search_query_parser'),
      $container->get('menu.link_tree')
    );
  }

  /**
   * Page callback: Retrieves autocomplete suggestions.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The autocompletion response.
   */
  public function autocomplete(Request $request) {
    $options = $this->searchQueryParser->parseQuery();
    // We need only 4 results in autocomplete.
    $options['limit'] = 4;

    $suggestions = [];
    $show_all = '';
    $results = $this->searchHelper->getSearchResults($options);

    if (!empty($results['results'])) {
      foreach ($results['results'] as $entity) {
        if ($entity->bundle() == 'faq') {
          $alter = [
            'max_length' => 50,
            'word_boundary' => TRUE,
            'ellipsis' => TRUE,
          ];
          $suggestions[] = FieldPluginBase::trimText($alter, strip_tags($entity->get('field_qa_item_question')->value));
          // Indicates that it's faq query so we can skip show all link.
          $faq = TRUE;
        }
        else {
          $suggestions[] = $options['cards_view'] ? $this->viewBuilder->view($entity, 'card') : $entity->toLink();
        }
      }

      $show_all = empty($faq) ? [
        'title' => $this->t('Show All Results for "@keys"', ['@keys' => $options['keys']]),
        'attributes' => [
          'href' => Url::fromUri('internal:/' . SearchHelperInterface::MARS_SEARCH_SEARCH_PAGE_PATH, [
            'query' => [
              SearchHelperInterface::MARS_SEARCH_SEARCH_KEY => [
                SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID => $options['keys'],
              ],
            ],
          ]),
        ],
      ] : [];
    }
    $empty_text_description = $this->config('mars_search.autocomplete')->get('empty_text_description');
    $build = [
      '#theme' => 'mars_search_suggestions',
      '#suggestions' => $suggestions,
      '#cards_view' => $options['cards_view'],
      '#show_all' => $show_all,
      '#no_results' => $this->getSearchNoResult(
        $this->t('There are no matching results for "@keys"', ['@keys' => $options['keys']]),
        $empty_text_description ? $empty_text_description : $this->t('Please try entering different search')
      ),
    ];

    return new JsonResponse($this->renderer->render($build));
  }

  /**
   * Render search no result block.
   */
  private function getSearchNoResult($heading, $description) {
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
      '#no_results_heading' => $heading,
      '#no_results_text' => $description,
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
   * Search AJAX callback.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The autocompletion response.
   */
  public function searchCallback(Request $request) {
    $json_output = [];

    $options = $this->searchQueryParser->parseQuery();

    $results = $this->searchHelper->getSearchResults($options);

    if (!empty($results['results'])) {
      foreach ($results['results'] as $entity) {
        $entity_build = $this->viewBuilder->view($entity, 'card');
        $json_output['search_results'][] = $this->renderer->render($entity_build);
      }
    }
    return new JsonResponse($json_output);
  }

}
