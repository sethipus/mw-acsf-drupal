<?php

namespace Drupal\mars_product\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_product\Form\BazaarvoiceConfigForm;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Header block.
 *
 * @Block(
 *   id = "rating_bazarvoice_block",
 *   admin_label = @Translation("Rating Bazarvoice"),
 *   category = @Translation("Product"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Product"))
 *   }
 * )
 */
class RatingBazarvoiceBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->config = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['product'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Product'),
      '#target_type' => 'node',
      '#default_value' => ($node_id = $this->configuration['product'] ?? NULL) ? $this->nodeStorage->load($node_id) : NULL,
      '#selection_settings' => [
        'target_bundles' => ['product'],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['product'] = $form_state->getValue('product');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = $this->getConfiguration();
    return [
      'product' => $config['product'] ?? '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    // Product node.
    $node = $this->getContextValue('node');

    if (!empty($this->configuration['product'])) {
      $node = $this->entityTypeManager->getStorage('node')->load($this->configuration['product']);
    }

    if ($node instanceof NodeInterface && $node->bundle() == 'product') {
      foreach ($node->field_product_variants as $reference) {
        $product_variant = $reference->entity;
        $gtin = $product_variant->get('field_product_sku')->value;
        $size_id = $product_variant->id();
        $build['#items'][] = [
          'gtin' => trim($gtin),
          'show_rating_and_reviews' => $this->isRatingEnable($node),
          'size_id' => $size_id,
        ];
      }

      if ($this->isRatingEnable($node)) {
        $build['#attached']['library'][] = 'mars_product/mars_product.bazaarvoice';
      }
    }

    $build['#theme'] = 'pdp_rating_block';
    return $build;
  }

  /**
   * Check is rating enable.
   *
   * @param \Drupal\node\NodeInterface|null $node
   *   Product or null.
   *
   * @return bool
   *   Return state of rating.
   */
  protected function isRatingEnable(NodeInterface $node = NULL) {
    if ($node instanceof NodeInterface &&
      $node->hasField('field_rating_and_reviews') &&
      $node->hasField('field_override_global_rating') &&
      $node->get('field_override_global_rating')->value == TRUE
    ) {
      $result = $node->get('field_rating_and_reviews')->value;
    }
    else {
      $result = $this->config->get(BazaarvoiceConfigForm::SETTINGS)->get('show_rating_and_reviews');
    }

    return $result;
  }

}
