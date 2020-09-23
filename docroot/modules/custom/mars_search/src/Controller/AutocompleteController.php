<?php

namespace Drupal\mars_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\mars_search\SearchHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a controller for autocompletion.
 */
class AutocompleteController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Search helper.
   *
   * @var \Drupal\mars_search\SearchHelperInterface
   */
  protected $searchHelper;

  /**
   * Creates a new AutocompleteController instance.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\mars_search\SearchHelperInterface $search_helper
   *   Search helper.
   */
  public function __construct(
    RendererInterface $renderer,
    SearchHelperInterface $search_helper
  ) {
    $this->renderer = $renderer;
    $this->searchHelper = $search_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('mars_search.search_helper')
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
    $keys = $request->query->get(SearchHelperInterface::MARS_SEARCH_SEARCH_KEY);
    $suggestions = [];
    $show_all = '';
    $results = $this->searchHelper->getSearchResults(['limit' => 4]);

    if (!empty($results['results'])) {
      foreach ($results['results'] as $entity) {
        $suggestions[] = $entity->toLink();
      }

      $show_all = Link::fromTextAndUrl($this->t('Show All Results for "@keys"', ['@keys' => $keys]), Url::fromUri('internal:/' . SearchHelperInterface::MARS_SEARCH_SEARCH_PAGE_PATH, ['query' => [SearchHelperInterface::MARS_SEARCH_SEARCH_KEY => $keys]]));
    }
    $empty_text_description = $this->config('mars_search.autocomplete')->get('empty_text_description');
    $build = [
      '#theme' => 'mars_search_suggestions',
      '#suggestions' => $suggestions,
      '#show_all' => $show_all,
      '#empty_text' => $this->t('There are no matching results for "@keys"', ['@keys' => $keys]),
      '#empty_text_description' => $empty_text_description ? $empty_text_description : $this->t('Please try entering different search'),
    ];

    return new JsonResponse($this->renderer->render($build));
  }

}
