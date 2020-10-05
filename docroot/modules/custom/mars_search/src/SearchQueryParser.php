<?php

namespace Drupal\mars_search;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
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
    $options[$search_id] = $this->getDefaultOptions();
    // Looping through parameters to support several searches on a single page.
    foreach ($query_parameters as $parameter_key => $parameter) {
      foreach ($parameter as $search_key => $parameter_value) {
        if ($search_key != $search_id) {
          continue;
        }
        if ($parameter_key == SearchQueryParserInterface::MARS_SEARCH_SEARCH_KEY) {
          $options[$search_key]['keys'] = $parameter_value;
        }
      }
    }

    return $options[$search_id];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOptions($query_parameters = []) {
    $default_options = [
      'conditions' => [
        // We don't need FAQ nodes in most cases.
        ['type', 'faq', '<>'],
      ],
      'limit' => 12,
      'keys' => '',
      'sort' => [
        'created' => 'DESC',
      ],
    ];

    // @todo handle faq type query case.
    return $default_options;
  }

}
