<?php

namespace Drupal\salsify_integration;

use Drupal\field\Entity\FieldConfig;

/**
 * Class SalsifyImportTaxonomyTerm.
 *
 * The main class used to perform taxonomy term imports from enumerated fields.
 * Imports are trigger either through queues during a cron run or via the
 * configuration page.
 *
 * @package Drupal\salsify_integration
 */
class SalsifyImportTaxonomyTerm extends SalsifyImport {

  /**
   * A function to import Salsify data as taxonomy terms in Drupal.
   *
   * @param string $vid
   *   The vocabulary ID for the taxonomy term field.
   * @param array $field
   *   The Salsify to Drupal field mapping entry.
   * @param array $salsify_ids
   *   The salsify_ids of the values to process.
   * @param array $salsify_field_data
   *   The salsify_field_data of the values to process.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function processSalsifyTaxonomyTermItems($vid, array $field, array $salsify_ids, array $salsify_field_data = []) {
    // Set the default fields to use to lookup any existing terms that were
    // previously imported.
    $field_name = 'salsify_id';
    if (empty($salsify_field_data)) {
      $salsify_data = $this->salsify->getProductData();
      $salsify_field_data = $salsify_data['fields'][$field['salsify_id']];
    }

    // Ensure that the tracking field values is created and ready to be used
    // on the given taxonomy vocabulary.
    $salsify_id_field = FieldConfig::loadByName('taxonomy_term', $vid, $field_name);
    if (is_null($salsify_id_field)) {
      SalsifyFields::createDynamicField(
          $salsify_field_data,
          $field_name,
          'taxonomy_term',
          $vid
        );
    }

    // Find any and all existing terms and update them as needed.
    $existing_terms = $this->getTaxonomyTerms($field_name, $salsify_ids, $vid);
    $updated_ids = [];
    foreach ($existing_terms as $existing_term) {
      /** @var \Drupal\taxonomy\Entity\Term $existing_term */
      $salsify_id = $existing_term->{$field_name}->value;
      if (isset($salsify_field_data['values'][$salsify_id]) && $salsify_field_data['values'][$salsify_id]['salsify:name'] != $existing_term->name->value) {
        $existing_term->set('name', $salsify_field_data['values'][$salsify_id]['salsify:name']);
        $existing_term->save();
      }
      $updated_ids[] = $salsify_id;
    }

    // Loop through the remaining values and create new terms for each.
    $new_ids = array_diff($salsify_ids, $updated_ids);
    foreach ($new_ids as $salsify_id) {
      // Only use the first vocabulary if there are multiple.
      $new_term = $this->entityTypeManager->getStorage('taxonomy_term')->create([
        'vid' => $vid,
        'name' => $salsify_field_data['values'][$salsify_id]['salsify:name'],
        'salsify_id' => $salsify_field_data['values'][$salsify_id]['salsify:id'],
      ]);
      $new_term->save();
    }

  }

  /**
   * Query media entities based on a given field and its value.
   *
   * @param string $field_name
   *   The name of the field to search on.
   * @param array $field_values
   *   The values of the field to match.
   * @param mixed $vid
   *   Vocabulary id.
   *
   * @return array|int
   *   An array of media entity ids that match the given options.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getTaxonomyTerms($field_name, array $field_values, $vid) {
    $term_ids = $this->entityTypeManager
      ->getStorage('taxonomy_term')
      ->getQuery()
      ->condition($field_name, $field_values, 'IN')
      ->condition('vid', $vid)
      ->execute();

    return $this->entityTypeManager->getStorage('taxonomy_term')->loadMultiple($term_ids);
  }

}
