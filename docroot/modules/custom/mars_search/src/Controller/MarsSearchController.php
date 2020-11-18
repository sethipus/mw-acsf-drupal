<?php

namespace Drupal\mars_search\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\mars_search\SearchHelperInterface;
use Drupal\mars_search\SearchQueryParserInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a controllers for search functionality.
 */
class MarsSearchController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Creates a new AutocompleteController instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The renderer.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\mars_search\SearchHelperInterface $search_helper
   *   Search helper.
   * @param \Drupal\mars_search\SearchQueryParserInterface $search_query_parser
   *   Search helper.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_link_tree
   *   Menu Link tree.
   *   Node view builder.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Request stack.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    RendererInterface $renderer,
    SearchHelperInterface $search_helper,
    SearchQueryParserInterface $search_query_parser,
    MenuLinkTreeInterface $menu_link_tree,
    RequestStack $request_stack,
    ConfigFactoryInterface $configFactory
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->renderer = $renderer;
    $this->searchHelper = $search_helper;
    $this->searchQueryParser = $search_query_parser;
    $this->viewBuilder = $entityTypeManager->getViewBuilder('node');
    $this->menuLinkTree = $menu_link_tree;
    $this->requestStack = $request_stack;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('mars_search.search_helper'),
      $container->get('mars_search.search_query_parser'),
      $container->get('menu.link_tree'),
      $container->get('request_stack'),
      $container->get('config.factory'),
    );
  }

  /**
   * Page callback: Retrieves autocomplete suggestions.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The autocompletion response.
   */
  public function autocomplete() {
    $options = $this->searchQueryParser->parseQuery(SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID);
    // We need only 4 results in autocomplete.
    $options['limit'] = 4;

    $suggestions = [];
    $show_all = '';
    $results = $this->searchHelper->getSearchResults($options, 'search_autocomplete');

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
    $config = $this->configFactory->get('mars_search.search_no_results');
    $empty_text_heading = $config->get('no_results_heading');
    $empty_text_description = $config->get('no_results_text');
    $build = [
      '#theme' => 'mars_search_suggestions',
      '#suggestions' => $suggestions,
      '#cards_view' => $options['cards_view'],
      '#show_all' => $show_all,
      '#empty_text' => str_replace('@keys', $options['keys'], $empty_text_heading),
      '#empty_text_description' => $empty_text_description ?? $this->t('Please try entering different search'),
      '#no_results' => $this->getSearchNoResult(
        str_replace('@keys', $options['keys'], $empty_text_heading),
        $empty_text_description ?? $this->t('Please try entering different search')
      ),
    ];
    if ($options['cards_view']) {
      $build['#no_results'] = $this->getSearchNoResult($build['#empty_text'], $build['#empty_text_description']);
    }

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
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The autocompletion response.
   */
  public function searchCallback() {
    $json_output = [];

    $options = $this->searchQueryParser->parseQuery(SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID);

    $results = $this->searchHelper->getSearchResults($options, 'search_callback');

    if (!empty($results['results'])) {
      foreach ($results['results'] as $entity) {
        $entity_build = $this->viewBuilder->view($entity, 'card');
        $json_output['search_results'][] = $this->renderer->render($entity_build);
      }
    }
    return new JsonResponse($json_output);
  }

  /**
   * Render all search cards block.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The learn more action response.
   */
  public function seeAllCallback() {
    $parameters = $this->requestStack->getCurrentRequest()->request->all();
    $items = [];
    $search_options = $parameters['searchOptions'];
    $top_results = $parameters['topResults'];
    unset($search_options['limit']);
    if (!empty($top_results)) {
      foreach ($this->entityTypeManager->getStorage('node')->loadMultiple($top_results) as $top_result_node) {
        $items[] = $this->viewBuilder->view($top_result_node, 'card');
      }
    }

    $results = $this->searchHelper->getSearchResults($search_options, "grid_{$parameters['id']}");

    if (!empty($results['results'])) {
      foreach ($results['results'] as $entity) {
        if (!in_array($entity->id(), $top_results)) {
          $items[] = $this->viewBuilder->view($entity, 'card');
        }
      }
    }

    return new Response($this->renderer->render($items));
  }

}
