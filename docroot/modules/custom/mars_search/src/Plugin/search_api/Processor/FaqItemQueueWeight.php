<?php
namespace Drupal\mars_search\Plugin\search_api\processor;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\entityqueue\EntitySubqueueInterface;
use Drupal\node\NodeInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Adds entityqueue weigh for FAQ items.
 *
 * @SearchApiProcessor(
 *   id = "faq_item_queue_weight",
 *   label = @Translation("FAQ item queue weight"),
 *   description = @Translation("Entityqueue weigh for FAQ items."),
 *   stages = {
 *     "add_properties" = 0,
 *   }
 * )
 */
class FaqItemQueueWeight extends ProcessorPluginBase {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|null
   */
  protected $entityTypeManager;
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $processor */
    $processor = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $processor->setEntityTypeManager($container->get('entity_type.manager'));
    return $processor;
  }
  /**
   * Retrieves the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  public function getEntityTypeManager() {
    return $this->entityTypeManager ?: \Drupal::entityTypeManager();
  }
  /**
   * Sets the entity type manager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The new entity type manager.
   *
   * @return $this
   */
  public function setEntityTypeManager(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    return $this;
  }
  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];
    if (!$datasource) {
      $definition = [
        'label' => $this->t('FAQ item queue weight'),
        'description' => $this->t('Entityqueue weigh for FAQ items'),
        'type' => 'integer',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['faq_item_queue_weight'] = new ProcessorProperty($definition);
    }
    return $properties;
  }
  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $queue = $this->getEntityTypeManager()->getStorage('entity_subqueue')->load('faq_queue');
    // Means in the bottom of the list.
    $weight = 9999;
    $node = $item->getOriginalObject()->getValue();
    if (
      $node instanceof NodeInterface &&
      $queue instanceof EntitySubqueueInterface &&
      $queue->hasField('items') &&
      !$queue->get('items')->isEmpty()) {
      $items = $queue->get('items')->getValue();
      foreach ($items as $item_weight => $item_id) {
        if ($item_id['target_id'] == $node->id()) {
          $weight = $item_weight;
        }
      }
    }
    $fields = $item->getFields(FALSE);
    $fields = $this->getFieldsHelper()
      ->filterForPropertyPath($fields, NULL, 'faq_item_queue_weight');
    foreach ($fields as $field) {
      $field->addValue($weight);
    }
  }
}
