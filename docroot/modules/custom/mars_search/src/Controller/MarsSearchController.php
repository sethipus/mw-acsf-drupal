<?php

namespace Drupal\mars_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_search\SearchProcessFactoryInterface;
use Drupal\mars_search\Processors\SearchHelperInterface;
use Drupal\mars_search\Processors\SearchQueryParserInterface;
use Drupal\pathauto\AliasCleanerInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\mars_common\Utils\NodeLBComponentIterator;

/**
 * Provides a controllers for search functionality.
 */
class MarsSearchController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Search key which is using in URL.
   */
  const MARS_SEARCH_AJAX_RESULTS = 'results';

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
   * Theme configurator parser service.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  private $themeConfiguratorParser;

  /**
   * The path validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The alias cleaner.
   *
   * @var \Drupal\pathauto\AliasCleanerInterface
   */
  protected $aliasCleaner;

  /**
   * Creates a new AutocompleteController instance.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\mars_search\SearchProcessFactoryInterface $searchProcessor
   *   Search processor factory.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\mars_common\ThemeConfiguratorParser $theme_configurator_parser
   *   Theme configurator parser service.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator service.
   * @param \Drupal\pathauto\AliasCleanerInterface $pathauto_alias_cleaner
   *   The path alias cleaner.
   */
  public function __construct(
    RendererInterface $renderer,
    SearchProcessFactoryInterface $searchProcessor,
    RequestStack $request_stack,
    EntityTypeManagerInterface $entityTypeManager,
    ThemeConfiguratorParser $theme_configurator_parser,
    PathValidatorInterface $path_validator,
    AliasCleanerInterface $pathauto_alias_cleaner
  ) {
    $this->renderer = $renderer;
    $this->viewBuilder = $entityTypeManager->getViewBuilder('node');
    $this->entityTypeManager = $entityTypeManager;
    $this->requestStack = $request_stack;
    $this->searchProcessor = $searchProcessor;
    $this->searchQueryParser = $this->searchProcessor->getProcessManager('search_query_parser');
    $this->searchHelper = $this->searchProcessor->getProcessManager('search_helper');
    $this->searchBuilder = $this->searchProcessor->getProcessManager('search_builder');
    $this->themeConfiguratorParser = $theme_configurator_parser;
    $this->pathValidator = $path_validator;
    $this->aliasCleaner = $pathauto_alias_cleaner;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('mars_search.search_factory'),
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('path.validator'),
      $container->get('pathauto.alias_cleaner')
    );
  }

  /**
   * Page callback: Retrieves autocomplete suggestions.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The autocompletion response.
   *
   * @throws \Exception
   */
  public function autocomplete() {
    $options = $this->searchQueryParser->parseQuery();

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
          $suggestions[] = [
            '#type' => 'html_tag',
            '#tag' => 'button',
            '#attributes' => [],
            '#value' => FieldPluginBase::trimText($alter, strip_tags($entity->get('field_qa_item_question')->value)),
          ];
        }
        else {
          $suggestions[] = $options['cards_view'] ? $this->viewBuilder->view($entity, 'card') : $entity->toLink();
        }
      }
      // Get alias for search url.
      $search_url = $this->searchHelper->getAliasForSearchUrl();

      $show_all = isset($options['cards_view']) ? [
        'title' => $this->t('@show_all "@keys"', ['@show_all' => 'Show All Results for', '@keys' => $options['keys']]),
        'attributes' => [
          'href' => urldecode(Url::fromUri('internal:' . $search_url, [
            'query' => [
              SearchHelperInterface::MARS_SEARCH_SEARCH_KEY => [
                SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID => $options['keys'],
              ],
              // Adding 's' query param to pass search string to GA dashboard.
              's' => $options['keys'],
            ],
          ])->toString()),
        ],
      ] : [];
    }
    // Set Card view FLASE by default.
    $options['cards_view'] = $options['cards_view'] ?? FALSE;
    $config_no_results = $this->config('mars_search.search_no_results');
    $empty_text_heading = $config_no_results->get('no_results_heading');
    $empty_text_description = $config_no_results->get('no_results_text');
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
    if (isset($query_parameters['grid_type']) &&
      $query_parameters['grid_type'] != 'faq') {
      $this->searchProcessor->getProcessManager('search_pretty_facet_process')->checkPrettyFacets($query_parameters);
    }
    $json_output = [];
    $config = [];
    $query_parameters['grid_id'] = empty($query_parameters['grid_id']) ? 1 : $query_parameters['grid_id'];
    $query_parameters['page_revision_id'] = empty($query_parameters['page_revision_id']) ? '' : $query_parameters['page_revision_id'];
    if (!empty($query_parameters['grid_type'])) {
      $config = $this->getComponentConfig($query_parameters['page_id'], $query_parameters['grid_id'], $query_parameters['page_revision_id']) ?: [];
    }

    switch ($query_parameters['action_type']) {
      case self::MARS_SEARCH_AJAX_RESULTS:
        $results = $this->searchBuilder->buildSearchResults($query_parameters['grid_type'], $config, $query_parameters['grid_id']);
        foreach ($results[2]['#items'] as $key => $item) {
          $results[2]['#items'][$key] = $this->renderer->render($item);
        }
        $json_output['no_results'] = !empty($results[2]['#no_results']) ? $this->renderer->render($results[2]['#no_results']) : '';

        if ((($results[1]['resultsCount'] - $results[0]['offset']) <= $query_parameters["limit"]) ||
          (($query_parameters['grid_type']) == 'faq' && $results[0]['offset'] > 0)) {
          $pager = 0;
        }
        else {
          $pager = 1;
        }
        $json_output['pager'] = $pager;
        $json_output['results'] = $results[2]['#items'];
        $json_output['results_count'] = $results[1]['resultsCount'];
        $json_output['search_key'] = $results[0]['keys'];
        if ($query_parameters['grid_type'] == 'faq') {
          $json_output['search_result_text'] = $results[2]['#search_result_text'];
        }

        break;

      case self::MARS_SEARCH_AJAX_FACET:
        $build = $this->searchBuilder->buildSearchFacets($query_parameters['grid_type'], $config, $query_parameters['grid_id']);
        $build['#filter_title_transform'] = $this->themeConfiguratorParser->getSettingValue('facets_text_transform', 'uppercase');
        $build['#theme'] = 'mars_search_filter';
        $json_output['filters'] = $this->renderer->render($build);
        if ($query_parameters['grid_type'] === 'search_page') {
          unset($build['#input_form']);
          $build = $this->searchBuilder->buildSearchHeader($config, $query_parameters['grid_id']);
          $build['#search_results'] = $build['#search_filters'];
          $build['#theme'] = 'mars_search_type_filter';
          $json_output['types'] = $this->renderer->render($build);
        }

        break;

      default:
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
   * @param string $vid
   *   The current node revision ID.
   *
   * @return mixed
   *   Returns block configuration or FALSE.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getComponentConfig(string $nid, string $grid_id, string $vid) {
    $nodeStorage = $this->entityTypeManager()->getStorage('node');
    /** @var \Drupal\node\Entity\Node $node */
    $node = !empty($vid) ? $nodeStorage->loadRevision($vid) : $nodeStorage->load($nid);
    $nodeIterator = new NodeLBComponentIterator($node);
    foreach ($nodeIterator as $component) {
      $config = $component->get('configuration');
      if (!empty($config['grid_id']) && $config['grid_id'] == $grid_id) {
        return $config;
      }
      // Adding an additional probe to get config if grid is not specified
      // because the text color may be overridden.
      if ($grid_id == 1 && !empty($config['override_text_color'])) {
        return $config;
      }
      $block_grid_id = $this->aliasCleaner->cleanString($config['title']);
      if (isset($config['title']) && $block_grid_id === $grid_id) {
        return $config;
      }
    }
    return [];
  }

}
