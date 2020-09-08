<?php

namespace Drupal\mars_product\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Multipack Products block.
 *
 * @Block(
 *   id = "pdp_product_multipack_block",
 *   admin_label = @Translation("MARS: Multipack Products"),
 *   category = @Translation("Product"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label =
 *   @Translation("Product"))
 *   }
 * )
 */
class PdpMultipackProductsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * File storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileStorage = $entity_type_manager->getStorage('file');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = $this->getConfiguration();

    return [
      'multipack_label' => $config['multipack_label'] ?? $this->t("What's in the pack"),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['multipack_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t("What's in the pack label"),
      '#maxlength' => 50,
      '#default_value' => $this->configuration['multipack_label'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $access = $this->getContextValue('node')->bundle() == 'product_multipack';
    return AccessResult::allowedIf($access);
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->setConfiguration($form_state->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $node = $this->getContextValue('node');

    $build['#multipack_label'] = $this->configuration['multipack_label'];
    $build['#multipack_items'] = $this->getMultipackItems($node);

    $build['#theme'] = 'pdp_product_multipack_block';
    return $build;
  }

  /**
   * Get Multipack items.
   *
   * @param object $node
   *   Product node.
   *
   * @return array
   *   Multipack items array.
   */
  public function getMultipackItems($node) {
    $items = [];
    foreach ($node->field_product_variants as $reference) {
      $product_variant = $reference->entity;
      $items[] = [
        'card_url' => $product_variant->toUrl('canonical', ['absolute' => FALSE])->toString(),
        'card__image__src' => $this->getMultipackImageSrc($product_variant),
        'paragraph_content' => $product_variant->title->value,
        'default_link_content' => $this->t('SEE DETAILS'),
        'link_content' => $this->t('BUY NOW'),
      ];
    }

    return $items;
  }

  /**
   * Get Image Src from Product Variant.
   *
   * @param object $node
   *   Product node.
   *
   * @return string
   *   Image src.
   */
  public function getMultipackImageSrc($node) {
    $field_name = 'field_product_key_image';
    $field_name_override = 'field_product_key_image_override';
    $image = $node->{$field_name}->entity;
    $image_override = $node->{$field_name_override}->entity;

    if ($image && $image_override) {
      $image = $image_override;
    }

    $file = $this->fileStorage->load($image->id());
    $image_src = $file->createFileUrl();

    return $image_src;
  }

}
