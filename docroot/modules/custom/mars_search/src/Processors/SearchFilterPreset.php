<?php

namespace Drupal\mars_search\Processors;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SearchFilterPreset.
 */
class SearchFilterPreset implements SearchFilterPresetInterface {
  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Request stack that controls the lifecycle of requests.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  public $request;

  /**
   * Arrays with searches metadata.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $searches = [];

  /**
   * Array of solr filter parameters.
   *
   * @var array
   */
  private array $options;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    RequestStack $request,
    array $options
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->request = $request->getMasterRequest();
    $this->options = $options;
  }

  /**
   * {@inheritdoc}
   */
  public function presetFilter(array $query_parameters, array $config) {

    // Getting default search options.
    $searchOptions = $this->searchQueryParser->parseQuery($this->gridId);

    if (empty($query_parameters['see-all'])) {
      // We need only 8 items to show initially.
      // Parse query will trim limit in case of see all.
      // But initial results count needs to be 8 instead of configured default.
      $searchOptions['limit'] = 4;
    }

    // Adjusting them with grid specific configuration.
    // Content type filter.
    if (!empty($config['content_type'])) {
      $searchOptions['conditions'][] = ['type', $config['content_type'], '='];
      $grid_options['filters']['type'][$this->gridId] = $config['content_type'];
      $grid_options['filters']['options_logic'] = !empty($config['general_filters']['options_logic']) ? $config['general_filters']['options_logic'] : 'and';
    }

    // Populate top results items before other results.
    if (!empty($config['top_results_wrapper']['top_results'])) {
      $top_result_ids = [];
      foreach ($config['top_results_wrapper']['top_results'] as $top_result) {
        $top_result_ids[] = $top_result['target_id'];
      }
      $build['#attached']['drupalSettings']['cards'][$this->gridId]['topResults'] = $top_result_ids;
      foreach ($this->entityTypeManager->getStorage('node')->loadMultiple($top_result_ids) as $top_result_node) {
        $build['#items'][] = $this->nodeViewBuilder->view($top_result_node, 'card');
      }
      // Adjusting query options to consider top results.
      // Adjusting limit.
      $searchOptions['limit'] = $searchOptions['limit'] - count($top_result_ids);
      // Excluding top results ids from query.
      $searchOptions['conditions'][] = ['nid', $top_result_ids, 'NOT IN'];
    }

    // After this line $facetOptions and $searchOptions become different.
    $facetOptions = $searchOptions;
    unset($facetOptions['limit']);

    // Taxonomy preset filter(s).
    // Adding them only if facets are disabled.
    if (empty($config['exposed_filters_wrapper']['toggle_filters'])) {
      foreach ($config['general_filters'] as $filter_key => $filter_value) {
        if (!empty($filter_value['select'])) {
          $grid_options['filters'][$filter_key][$this->gridId] = implode(',', $filter_value['select']);

          $searchOptions['conditions'][] = [
            $filter_key,
            $filter_value['select'],
            'IN',
          ];
        }
      }
      $searchOptions['options_logic'] = !empty($config['general_filters']['options_logic']) ? $config['general_filters']['options_logic'] : 'and';
    }

    return [$grid_options, $build];
  }

}
