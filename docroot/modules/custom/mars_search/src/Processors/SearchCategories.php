<?php

namespace Drupal\mars_search\Processors;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class SearchCategories.
 */
class SearchCategories implements SearchCategoriesInterface, SearchProcessManagerInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Solr index entity.
   *
   * @var \Drupal\search_api\Entity\Index
   */
  protected $index;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * Array of categories with labels.
   *
   * @var array
   */
  private $categies;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $configFactory,
    LanguageHelper $language_helper
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->index = $this->entityTypeManager->getStorage('search_api_index')->load('acquia_search_index');
    $this->configFactory = $configFactory;
    $this->languageHelper = $language_helper;
    $this->buildCategoriesList();
  }

  /**
   * {@inheritdoc}
   */
  public function getManagerId() {
    return 'search_categories';
  }

  /**
   * Return processed list of categories.
   *
   * @return array
   *   List of categories.
   */
  public function getCategories() {
    return $this->categories;
  }

  /**
   * Return processed list of content types.
   */
  public function getContentTypes() {
    return [
      'product' => $this->t('Product'),
      'article' => $this->t('Article'),
      'recipe' => $this->t('Recipe'),
      'campaign' => $this->t('Campaign'),
      'landing_page' => $this->t('Landing page'),
    ];
  }

  /**
   * Prepare Categories list from Solr index fields.
   */
  protected function buildCategoriesList() {
    $listFields = SearchCategoriesInterface::TAXONOMY_VOCABULARIES;
    $indexFields = [];
    $siteLabelConfig = $this->configFactory->get('mars_common.site_labels');

    foreach ($this->index->getFields() as $solrField => $properties) {
      $indexFields[$properties->getPropertyPath()] = $solrField;
    }
    foreach ($this->index->getDatasources() as $datasourceId => $datasource) {
      if ($datasourceId == 'entity:node') {
        $fields = $datasource->getPropertyDefinitions();
        $entityTypes = $datasource->getBundles();
        foreach ($entityTypes as $key => $bundle) {
          // Excluded entities from search.
          if ($key == 'product_multipack' || $key == 'product_variant') {
            continue;
          }
          $fieldDef = $datasource->getEntityFieldManager()->getFieldDefinitions('node', $key);
          foreach ($fieldDef as $name => $definition) {
            $fieldsDef[$name][] = $key;
          }
        }
        foreach ($fields as $field_name => $field_definition) {
          if (
            !empty($field_definition->getTargetBundle()) &&
            $field_definition->getType() == 'entity_reference' &&
            $field_definition->getSettings()['target_type'] == 'taxonomy_term' &&
            array_key_exists($field_name, $indexFields)
          ) {
            $fieldIndexName = $indexFields[$field_name];
            $listFields[$fieldIndexName] = $listFields[$fieldIndexName] ?? ['label' => $field_definition->getLabel()];
            $listFields[$fieldIndexName]['label'] = $this->languageHelper->translate($siteLabelConfig->get($fieldIndexName)) ?? $listFields[$fieldIndexName]['label'];
            $listFields[$fieldIndexName]['content_types'] = $fieldsDef[$field_name];
            $listFields[$fieldIndexName]['machine_name'] = $field_name;
          }
        }
      }
    }
    $this->categories = $listFields;
  }

}
