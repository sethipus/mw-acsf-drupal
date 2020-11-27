<?php

namespace Drupal\mars_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\mars_search\SearchProcessFactoryInterface;
use Drupal\mars_search\Processors\SearchHelperInterface;
use Drupal\mars_search\Processors\SearchQueryParserInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\mars_common\Utils\NodeLBComponentIterator;

/**
 * Provides a controllers for search functionality.
 */
class MarsSearchController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Search key which is using in URL.
   */
  const MARS_SEARCH_AJAX_PAGER = 'pager';

  /**
   * Search key which is using in URL.
   */
  const MARS_SEARCH_AJAX_FACET = 'facet';

  /**
   * Search key which is using in URL.
   */
  const MARS_SEARCH_AJAX_QUERY = 'query';

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
   * Taxonomy facet process service.
   *
   * @var \Drupal\mars_search\Processors\SearchTermFacetProcess
   */
  protected $searchTermFacetProcess;

  /**
   * Templates builder service .
   *
   * @var \Drupal\mars_search\Processors\SearchBuilder
   */
  protected $searchBuilder;

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
   * @param \Drupal\mars_search\SearchProcessFactoryInterface $searchProcessor
   *   Search processor factory.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_link_tree
   *   Menu Link tree.
   * @param \Drupal\Core\Entity\EntityViewBuilderInterface $node_view_builder
   *   Node view builder.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   */
  public function __construct(
    RendererInterface $renderer,
    SearchProcessFactoryInterface $searchProcessor,
    MenuLinkTreeInterface $menu_link_tree,
    EntityViewBuilderInterface $node_view_builder,
    RequestStack $request_stack
  ) {
    $this->renderer = $renderer;
    $this->viewBuilder = $this->entityTypeManager()->getViewBuilder('node');
    $this->menuLinkTree = $menu_link_tree;
    $this->nodeViewBuilder = $node_view_builder;
    $this->requestStack = $request_stack;
    $this->searchProcessor = $searchProcessor;
    $this->searchQueryParser = $this->searchProcessor->getProcessManager('search_query_parser');
    $this->searchHelper = $this->searchProcessor->getProcessManager('search_helper');
    $this->searchTermFacetProcess = $this->searchProcessor->getProcessManager('search_facet_process');
    $this->searchBuilder = $this->searchProcessor->getProcessManager('search_builder');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('mars_search.search_factory'),
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
      '#no_results' => $this->searchBuilder->getSearchNoResult($options['keys'], 'search_page'),
    ];
    if ($options['cards_view']) {
      $build['#no_results'] = $this->searchBuilder->getSearchNoResult($options['keys'], 'search_page');
    }

    return new JsonResponse($this->renderer->render($build));
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
    $query_parameters = $request->query->all();
    $json_output = [];
    $config = [];
    if (empty($query_parameters['page_id']) || empty($query_parameters['grid_id'])) {
      return new JsonResponse($json_output);
    }
    if (!empty($query_parameters['grid_type']) && $query_parameters['grid_type'] == 'grid') {
      $config = $this->getComponentConfig($query_parameters['page_id'], $query_parameters['grid_id']);
    }

    switch ($query_parameters['action_type']) {
      case self::MARS_SEARCH_AJAX_PAGER:
        $results = $this->searchBuilder->buildSearchResults($query_parameters['grid_type'], $config, $query_parameters['grid_id']);
        foreach ($results[2]['#items'] as $key => $item) {
          $results[2]['#items'][$key] = $this->renderer->render($item);
        }
        $json_output['results'] = $results[2];

        break;

      case self::MARS_SEARCH_AJAX_FACET:
        $build = $this->searchBuilder->buildSearchFacets($config, $query_parameters['grid_id']);
        $build['#theme'] = 'mars_search_filter';
        $json_output['filter'] = $this->renderer->render($build);

        break;

      case self::MARS_SEARCH_AJAX_QUERY:
        break;
    }

    return new JsonResponse($json_output);
  }

  /**
   * Get block configuration from node ID and grid ID.
   *
   * @param string $nid
   *   Node ID.
   * @param string $grid_id
   *   Grid ID of the component.
   *
   * @return mixed
   *   Returns block configuration or FALSE.
   */
  protected function getComponentConfig(string $nid, string $grid_id) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->entityTypeManager()->getStorage('node')->load($nid);
    $nodeIterator = new NodeLBComponentIterator($node);
    foreach ($nodeIterator as $component) {
      $config = $component->get('configuration');
      if (!empty($config['grid_id']) && $config['grid_id'] == $grid_id) {
        return $config;
      }
    }
    return FALSE;
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
    $query_parameters = $this->searchHelper->request->query->all();
    $search_options = $this->searchQueryParser->parseQuery($query_parameters['id'] ?
      $query_parameters['id'] : 1);
    if (!empty($query_parameters['contentType'])) {
      $search_options['conditions'][] = [
        'type',
        $query_parameters['contentType'],
        '=',
      ];
    }
    $items = [];
    $top_results = $query_parameters['topResults'];
    if (!empty($query_parameters['isFilterAjax'])) {
      $search_options['limit'] = 4;
    }
    else {
      unset($search_options['limit']);
    }

    if (!empty($top_results)) {
      foreach ($this->entityTypeManager->getStorage('node')->loadMultiple($top_results) as $top_result_node) {
        $items[] = [
          '#type' => 'container',
          'children' => $this->nodeViewBuilder->view($top_result_node, 'card'),
          '#attributes' => ['class' => ['ajax-card-grid__item_wrapper']],
        ];
      }
    }

    $results = $this->searchHelper->getSearchResults($search_options, $query_parameters['id'] ?
      "grid_{$query_parameters['id']}" : 'main_search');

    if (!empty($results['results'])) {
      foreach ($results['results'] as $entity) {
        if (!in_array($entity->id(), $top_results)) {
          $items[] = [
            '#type' => 'container',
            'children' => $this->nodeViewBuilder->view($entity, 'card'),
            '#attributes' => ['class' => ['ajax-card-grid__item_wrapper']],
          ];
        }
      }
    }

    $build = $this->renderer->renderRoot($items);

    return new JsonResponse([
      'build' => $build,
      'showButton' => $results['resultsCount'] > count($items) ? TRUE : FALSE,
    ]);
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
    $query_parameters = $this->searchHelper->request->query->all();
    $search_options = $this->searchQueryParser->parseQuery();
    $search_options['conditions'][0] = ['type', 'faq', '=', TRUE];
    $faq_items = [];
    if ($query_parameters['isFilterAjax']) {
      $search_options['limit'] = 4;
    }
    else {
      unset($search_options['limit']);
    }
    $search_options['sort'] = [
      'faq_item_queue_weight' => 'ASC',
      'created' => 'DESC',
    ];
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
    $build = $this->renderer->renderRoot($build);

    return new JsonResponse([
      'build' => $build,
      'showButton' => $search_results['resultsCount'] > count($faq_items) ? TRUE : FALSE,
    ]);
  }

}
