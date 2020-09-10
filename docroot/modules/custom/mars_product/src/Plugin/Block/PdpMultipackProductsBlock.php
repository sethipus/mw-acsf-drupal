<?php

namespace Drupal\mars_product\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\views\Views;

/**
 * Provides a Multipack Products block.
 *
 * @Block(
 *   id = "pdp_product_multipack_block",
 *   admin_label = @Translation("Multipack Products"),
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
      'label_display' => FALSE,
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
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->setConfiguration($form_state->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $node = $this->getContextValue('node');

    $view_id = 'multipack_product_card_grid';
    $view_display = 'block_multipack_product';
    $view = Views::getView($view_id);
    $view->setDisplay($view_display);
    $view->preExecute();
    $output = $view->render($view_display);

    $build = [];
    if (count($view->result)) {
      $build['#multipack_label'] = $this->configuration['multipack_label'];
      $build['#multipack_view'] = $output;
    }

    $build['#theme'] = 'pdp_product_multipack_block';
    return $build;
  }

}
