<?php

namespace Drupal\mars_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\views\Entity\View;
use Drupal\views\ViewEntityInterface;
use Drupal\views\ViewExecutableFactory;
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
   * Views executable factory service.
   *
   * @var \Drupal\views\ViewExecutableFactory
   */
  protected $viewsExecutableFactory;

  /**
   * Creates a new AutocompleteController instance.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\views\ViewExecutableFactory $views_executable_factory
   *   Views factory.
   */
  public function __construct(
    RendererInterface $renderer,
    ViewExecutableFactory $views_executable_factory
  ) {
    $this->renderer = $renderer;
    $this->viewsExecutableFactory = $views_executable_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('views.executable'),
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
    $suggestions = [];
    $show_all = $empty_text = $empty_text_description ='';
    $keys = $request->query->get('q');
    $view_id = $request->query->get('view_id');
    $view_display_id = $request->query->get('display_id');
    $view = View::load($view_id);

    if ($view instanceof ViewEntityInterface) {
      $view = $this->viewsExecutableFactory->get($view);
    }
    $view->setDisplay($view_display_id);
    $view->setExposedInput([
      'search_api_fulltext' => $keys,
    ]);
    $view->setItemsPerPage(4);
    $view->execute();
    if (!empty($view->result)) {
      foreach ($view->result as $resultRow) {
        $entity = $resultRow->_object->getValue();
        $suggestions[] = $entity->toLink();
      }
      $show_all = Link::fromTextAndUrl($this->t('Show All Results for "@keys"', ['@keys' => $keys]), Url::fromUri('base:search', ['query' => ['search_api_fulltext' => $keys]]));
    }
    $build = [
      '#theme' => 'mars_search_suggestions',
      '#suggestions' => $suggestions,
      '#show_all' => $show_all,
      '#empty_text' => $this->t('There are no matching results for "@keys"', ['@keys' => $keys]),
      '#empty_text_description' => $this->t('Please try entering different search'),
    ];

    return new JsonResponse($this->renderer->render($build));
  }

}
