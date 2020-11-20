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
   * Array of solr filter parameters.
   *
   * @var array
   */
  private $options = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    RequestStack $request
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->request = $request->getMasterRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function parseQuery(int $search_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID) {
    // Getting all GET parameters in array.
    $query_parameters = $this->request->query->all();

    // In autocomplete case we have to get search id from the GET query.
    if (isset($query_parameters['search_id'])) {
      $search_id = $query_parameters['search_id'];
    }

    // Initializing options array.
    $this->options[$search_id] = $this->getDefaultOptions($query_parameters);

    // Removing limit in "see all" case.
    if (!empty($query_parameters['see-all'])) {
      unset($this->options[$search_id]['limit']);
    }

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
            $this->options[$search_key]['keys'] = $parameter_value;
          }
          // Getting search filters values.
          elseif (in_array($parameter_key, array_keys(SearchGridBlock::TAXONOMY_VOCABULARIES))) {
            $this->options[$search_key]['conditions'][] = [
              $parameter_key,
              explode(',', $parameter_value),
              'IN',
            ];
          }
          else {
            $this->options[$search_key]['conditions'][] = [
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
      $this->options[$search_key]['options_logic'] = $query_parameters['options_logic'];
    }

    // Autocomplete specific option for header search overlay.
    // If it is set we display nodes cards, otherwise – just links.
    $this->options[$search_id]['cards_view'] = !empty($query_parameters['cards_view']);

    // Return new self($this->entityTypeManager,$this->request,$this->options);.
    return $this->options[$search_id];
  }

  /**
   * Return search options.
   *
   * @param int $search_id
   *   Grid ID.
   *
   * @return array
   *   Processed query option with preset.
   */
  public function parseResults(int $search_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID) {
    return $this->options[$search_id];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOptions(array $query_parameters = []) {
    $faq_operator = empty($query_parameters['faq']) ? '<>' : '=';
    return [
      'conditions' => [
        ['type', 'faq', $faq_operator, TRUE],
      ],
      'limit' => 8,
      // Just to not have this empty.
      'options_logic' => 'AND',
      'keys' => '',
      'sort' => [
        'created' => 'DESC',
      ],
    ];
  }

}
