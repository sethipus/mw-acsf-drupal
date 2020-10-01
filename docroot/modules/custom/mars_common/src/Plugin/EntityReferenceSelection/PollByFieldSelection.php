<?php

namespace Drupal\mars_common\Plugin\EntityReferenceSelection;

use Drupal\node\Plugin\EntityReferenceSelection\NodeSelection;

/**
 * Provides specific access control for the poll entity type.
 *
 * @EntityReferenceSelection(
 *   id = "default:poll_by_field",
 *   label = @Translation("Poll by field selection"),
 *   entity_types = {"poll"},
 *   group = "default",
 *   weight = 3
 * )
 */
class PollByFieldSelection extends NodeSelection {

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = parent::buildEntityQuery($match, $match_operator);
    $handler_settings = $this->configuration['handler_settings'];
    if (!isset($handler_settings['filter'])) {
      return $query;
    }
    $filter_settings = $handler_settings['filter'];
    foreach ($filter_settings as $field_name => $value) {
      $query->condition($field_name, $value, '=');
    }
    return $query;
  }

}
