<?php

namespace Drupal\mars_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a no results block for the search page.
 *
 * @Block(
 *   id = "search_no_results_block",
 *   admin_label = @Translation("MARS: Search no results"),
 *   category = @Translation("Mars Search")
 * )
 */
class NoResultsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The currently active request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $search_text = $this->request->query->get('search');
    return [
      '#no_results_heading' => $this->t('There are no matching results for "%search"', ['%search' => $search_text]),
      '#no_results_text' => $this->t('Please try entering a different search'),
      '#no_results_links' => [],
      '#theme' => 'mars_search_no_results',
    ];
  }

}
