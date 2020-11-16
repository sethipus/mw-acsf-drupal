<?php

namespace Drupal\mars_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\mars_search\SearchHelperInterface;
use Drupal\mars_search\SearchQueryParserInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * The node view builder.
   *
   * @var \Drupal\node\NodeViewBuilder
   */
  protected $nodeViewBuilder;

  /**
   * The request stack.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

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
   * @param \Drupal\Core\Entity\EntityViewBuilderInterface $node_view_builder
   *   Node view builder.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   */
  public function __construct(
    RendererInterface $renderer,
    SearchHelperInterface $search_helper,
    SearchQueryParserInterface $search_query_parser,
    MenuLinkTreeInterface $menu_link_tree,
    EntityViewBuilderInterface $node_view_builder,
    RequestStack $request_stack
  ) {
    $this->renderer = $renderer;
    $this->searchHelper = $search_helper;
    $this->searchQueryParser = $search_query_parser;
    $this->viewBuilder = $this->entityTypeManager()->getViewBuilder('node');
    $this->menuLinkTree = $menu_link_tree;
    $this->nodeViewBuilder = $node_view_builder;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('mars_search.search_helper'),
      $container->get('mars_search.search_query_parser'),
      $container->get('menu.link_tree'),
      $container->get('entity_type.manager')->getViewBuilder('node'),
      $container->get('request_stack')
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
    $empty_text_heading = $this->config('mars_search.search_no_results')->get('no_results_heading');
    $empty_text_description = $this->config('mars_search.search_no_results')->get('no_results_text');
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

  /**
   * Render all search cards block.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The learn more action response.
   */
  public function seeAllCallback(Request $request) {
    $parameters = $this->requestStack->getCurrentRequest()->request->all();
    $items = [];
    $search_options = $parameters['searchOptions'];
    $top_results = $parameters['topResults'];
    unset($search_options['limit']);
    if (!empty($top_results)) {
      foreach ($this->entityTypeManager->getStorage('node')->loadMultiple($top_results) as $top_result_node) {
        $items[] = $this->nodeViewBuilder->view($top_result_node, 'card');
      }
    }

    $results = $this->searchHelper->getSearchResults($search_options, !empty($parameters['id']) ?
      "grid_{$parameters['id']}" : 'main_search');

    if (!empty($results['results'])) {
      foreach ($results['results'] as $entity) {
        if (!in_array($entity->id(), $top_results)) {
          $items[] = $this->nodeViewBuilder->view($entity, 'card');
        }
      }
    }

    return new Response($this->renderer->render($items));
  }

  /**
   * Render all search cards block.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The learn more action response.
   */
  public function seeAllFaqCallback(Request $request) {
    $parameters = $this->requestStack->getCurrentRequest()->request->all();
    $search_options = $parameters['searchOptions'];
    $faq_items = [];
    unset($search_options['limit']);
    $search_results = $this->searchHelper->getSearchResults($search_options);
    if ($search_results['results']) {
      /** @var \Drupal\node\NodeInterface $search_result */
      foreach ($search_results['results'] as $row_key => $search_result) {
        // Do not fail page load if search index is not in sync with database.
        if ($search_result->bundle() != 'faq') {
          $search_results['resultsCount']--;

          continue;
        }

        $question_value = !empty($search_results['highlighted_fields'][$row_key]['field_qa_item_question'][0]) ?
          $search_results['highlighted_fields'][$row_key]['field_qa_item_question'][0] : $search_result->get('field_qa_item_question')->value;
        $answer_value = !empty($search_results['highlighted_fields'][$row_key]['field_qa_item_answer'][0]) ?
          $search_results['highlighted_fields'][$row_key]['field_qa_item_answer'][0] : $search_result->get('field_qa_item_answer')->value;
        $faq_items[$row_key] = [
          'question' => $question_value,
          'answer' => $answer_value,
          'order' => $row_key,
        ];
      }
    }

    $build = [
      '#theme' => 'mars_search_see_all_faq',
      '#qa_items' => $faq_items,
    ];

    return new Response($this->renderer->renderRoot($build));
  }

}
