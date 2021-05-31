<?php

namespace Drupal\mars_search\Processors;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Class SearchPrettyFacetProcess.
 */
class SearchPrettyFacetProcess implements SearchProcessManagerInterface, SearchPrettyFacetProcessInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getManagerId() {
    return 'search_pretty_facet_process';
  }

  /**
   * {@inheritdoc}
   */
  public function checkPrettyFacets(&$query_parameters) {
    foreach ($query_parameters as $key => $query_parameter) {
      $term_object = NULL;
      if (is_array($query_parameter) && in_array($key, static::getPrettyFacetKeys())) {
        $facet_key = array_search($key, static::getPrettyFacetKeys());
        unset($query_parameters[$key]);
        if (is_array($query_parameter)) {
          $terms = explode(",", current($query_parameter));
          $ids = [];
          foreach ($terms as $term_name) {
            $term_object = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
              'vid' => $facet_key,
              'name' => urldecode($term_name),
            ]);
            $term_object = reset($term_object);
            if ($term_object instanceof TermInterface) {
              $ids[] = $term_object->id();
            }
            else {
              $ids[] = $term_name;
            }
          }

          if (isset($query_parameters['grid_id'])) {
            $query_parameters[$facet_key] = [
              $query_parameters['grid_id'] => implode(",", $ids),
            ];
          }
          else {
            $query_parameters[$facet_key] = [
              SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID => implode(",", $ids),
            ];
          }

        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getPrettyFacetKeys() {
    return [
      'faq_filter_topic' => 'faq_filter_topic',
      'mars_flavor' => 'flavor',
      'mars_format' => 'format',
      'mars_diet_allergens' => 'diet_allergens',
      'mars_occasions' => 'occasions',
      'mars_brand_initiatives' => 'brand_initiatives',
      'mars_category' => 'category',
      'mars_culture' => 'culture',
      'mars_food_type' => 'food_type',
      'mars_main_ingredient' => 'main_ingredient',
      'mars_meal_type' => 'meal_type',
      'mars_method' => 'method',
      'mars_prep_time' => 'prep_time',
      'mars_product_used' => 'product_used',
      'mars_recipe_collection' => 'recipe_collection',
    ];
  }

  /**
   * Rewrite filters keys.
   */
  public function rewriteFilterKeys(array &$build) {
    if (isset($build['#filters']) && !empty($build['#filters'])) {
      foreach ($build['#filters'] as $key => $filter) {
        if (array_key_exists($filter['filter_id'], static::getPrettyFacetKeys())) {
          $build['#filters'][$key]['filter_id'] = static::getPrettyFacetKeys()[$filter['filter_id']];
        }
      }
    }
  }

}
