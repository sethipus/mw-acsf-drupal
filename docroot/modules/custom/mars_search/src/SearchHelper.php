<?php

namespace Drupal\mars_search;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SearchHelper.
 */
class SearchHelper implements SearchHelperInterface {
  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Request stack that controls the lifecycle of requests
   * @var RequestStack
   */
  private $request;

  protected static $_searches = array();

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RequestStack $request) {
    $this->entityTypeManager = $entity_type_manager;
    $this->request = $request->getMasterRequest();
  }


  /**
   * @inheritDoc
   */
  public function getSearchResults($options = [], $searcher_key = 'searcher_1') {
    if (!isset(self::$_searches[$searcher_key])) {
      $keys = $this->request->query->get(SearchHelperInterface::MARS_SEARCH_SEARCH_KEY);;
      $type = $this->request->query->get('type');;

      $index = $this->entityTypeManager->getStorage('search_api_index')->load('acquia_search_index');
      $query = $index->query(
        [
          'limit'  => isset($options['limit']) ? $options['limit'] : 8,
          'offset' => 0,
        ]
      );
      if ($facets = $this->entityTypeManager->getStorage('facets_facet')->loadMultiple()) {
        $facet_options = [];
        foreach ($facets as $facet) {
          $facet_field = $facet->getFieldIdentifier();
          $facet_options[$facet_field] = [
            'field' => $facet_field,
            'limit' => 20,
            'operator' => 'AND',
            'min_count' => 1,
            'missing' => TRUE,
          ];
        }
        if ($facet_options) {
          $query->setOption('search_api_facets', $facet_options);
        }
      }


      // Applying ndoe type filter.
      if ($type) {
        $query = $query->addCondition('type', $type);
      }
      // Applying search keys.
      if ($keys) {
        $query->keys($keys);
      }

      $results = $query->execute();

      self::$_searches[$searcher_key] = [
        'results' => $results,
        'facets' => $results->getExtraData('search_api_facets', []),
      ];

    }
    return self::$_searches[$searcher_key];
  }

}
