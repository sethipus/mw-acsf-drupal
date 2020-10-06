<?php

namespace Drupal\mars_search;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mars_search\Plugin\Block\SearchGridBlock;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SearchQueryParser.
 */
class SearchQueryParser implements SearchQueryParserInterface {
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
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RequestStack $request) {
    $this->entityTypeManager = $entity_type_manager;
    $this->request = $request->getMasterRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function parseQuery($search_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID) {
    // Getting all GET parameters in array.
    $query_parameters = $this->request->query->all();

    // In autocomplete case we have to get search id from the GET query.
    if (isset($query_parameters['search_id'])) {
      $search_id = $query_parameters['search_id'];
    }

    // Initializing options array.
    $options[$search_id] = $this->getDefaultOptions($query_parameters);

    // Looping through parameters to support several searches on a single page.
    foreach ($query_parameters as $parameter_key => $parameter) {
      if (is_array($parameter)) {
        foreach ($parameter as $search_key => $parameter_value) {
          // We need query options only for specified search instance.
          if ($search_key != $search_id) {
            continue;
          }
          // Getting search keyword.
          if ($parameter_key == SearchQueryParserInterface::MARS_SEARCH_SEARCH_KEY) {
            $options[$search_key]['keys'] = $parameter_value;
          }
          // Getting search filters values.
          elseif (in_array($parameter_key, array_keys(SearchGridBlock::TAXONOMY_VOCABULARIES))) {
            $options[$search_key]['conditions'][] = [
              $parameter_key,
              explode(',', $parameter_value),
              'IN',
            ];
          }
          else {
            $options[$search_key]['conditions'][] = [
              $parameter_key,
              $parameter_value,
              '=',
            ];
          }
        }
      }
    }

    // Getting search filters query logic.
    if (!empty($query_parameters['options_logic'])) {
      $options[$search_key]['options_logic'] = $query_parameters['options_logic'];
    }

    return $options[$search_id];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOptions($query_parameters = []) {
    $faq_operator = empty($query_parameters['faq']) ? '<>' : '=';
    return [
      'conditions' => [
        ['type', 'faq', $faq_operator, TRUE],
      ],
      'limit' => 12,
      // Just to not have this empty.
      'options_logic' => 'OR',
      'keys' => '',
      'sort' => [
        'created' => 'DESC',
      ],
    ];
  }

}
