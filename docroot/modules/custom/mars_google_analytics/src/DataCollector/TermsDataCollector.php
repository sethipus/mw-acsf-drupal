<?php

namespace Drupal\mars_google_analytics\DataCollector;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\field\FieldConfigInterface;
use Drupal\mars_google_analytics\Entity\EntityDecorator;
use Drupal\node\NodeInterface;

/**
 * Class TermsDataCollector collects data of terms.
 */
class TermsDataCollector implements DataCollectorInterface, DataLayerCollectorInterface {

  /**
   * Entity Type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityManager;

  /**
   * Collection of data.
   *
   * @var array
   */
  private $data = [];

  /**
   * Constructor method.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *   Entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityManager) {
    $this->entityManager = $entityManager;

    $this->data['terms']['loaded'] = [];
  }

  /**
   * Get data layer id.
   *
   * @return string
   *   Data layer param id.
   */
  public function getDataLayerId() {
    return 'taxonomy';
  }

  /**
   * Collect terms related to loaded nodes.
   */
  public function collect() {
    $rendered = $this->entityManager->getRendered('node');

    if ($rendered) {
      $this->data['terms']['loaded'] = array_merge(
        $this->data['terms']['loaded'],
        $this->getTermsData($rendered)
      );
    }
  }

  /**
   * Get loaded terms.
   *
   * @return array
   *   Rendered products.
   */
  public function getLoadedTerms() {
    return $this->data['terms']['loaded'];
  }

  /**
   * Add loaded term id.
   *
   * @param string $vocabulary_id
   *   Vocabulary Id.
   * @param string $term_label
   *   Term label.
   */
  public function addLoadedTerms($vocabulary_id, $term_label) {
    $this->data['terms']['loaded'][$vocabulary_id][$term_label] = $term_label;
  }

  /**
   * Get product related data.
   *
   * @param \Drupal\mars_google_analytics\Entity\EntityDecorator $decorator
   *   Decorator.
   *
   * @return array
   *   Array of product gtins.
   */
  private function getTermsData(EntityDecorator $decorator) {
    $terms = [];

    /** @var \Drupal\node\NodeInterface $node */
    foreach ($decorator->getEntities() as $node) {
      if (isset($node)) {
        $this->getLoadedTermsByNode($node, $terms);
      }
    }

    return $terms;
  }

  /**
   * Generate Google Analytics data string.
   *
   * @return string|null
   *   Google Analytics data.
   */
  public function getGaData() {
    $ga_data = NULL;

    if (!empty($this->data['terms']['loaded'])) {
      foreach ($this->data['terms']['loaded'] as $vid => $term_labels) {
        $ga_data[$vid] = array_values($term_labels);
      }
    }

    return Json::encode($ga_data);
  }

  /**
   * Get referenced term entities by node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   * @param array $terms
   *   Terms.
   */
  private function getLoadedTermsByNode(NodeInterface $node, array &$terms) {
    $field_defs = $node->getFieldDefinitions();
    foreach ($field_defs as $field_name => $field_def) {
      if ($field_def instanceof FieldConfigInterface &&
        $field_def->getType() == 'entity_reference' &&
        $field_def->getSetting('target_type') == 'taxonomy_term'
      ) {

        $term_entites = $node->get($field_name)->referencedEntities();
        foreach ($term_entites as $term_entity) {
          /** @var \Drupal\taxonomy\Entity\Term $term_entity */
          $terms[$term_entity->referencedEntities()[0]->label()][$term_entity->label()] = $term_entity->label();
        }
      }
    }
  }

}
