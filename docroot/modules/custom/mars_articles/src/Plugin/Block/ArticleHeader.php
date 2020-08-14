<?php

namespace Drupal\mars_articles\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class ArticleHeader.
 *
 * @Block(
 *   id = "article_header",
 *   admin_label = @Translation("Article header"),
 *   category = @Translation("Article"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Article"))
 *   }
 * )
 *
 * @package Drupal\mars_recipes\Plugin\Block
 */
class ArticleHeader extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  /**
   * A view builder instance.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * Node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->viewBuilder = $entity_type_manager->getViewBuilder('node');
    $this->nodeStorage = $entity_type_manager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->getContextValue('node');
    if (!$node || !$node->bundle() == 'article') {
      $node = $this->nodeStorage->load($this->configuration['article']);
    }
    // TODO add eyebrow to variables.
    return $this->viewBuilder->view($node, 'teaser');
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#maxlength' => 15,
      '#required' => TRUE,
      '#default_value' => $config['eyebrow'] ?? '',
    ];
    $form['article'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Default article'),
      '#default_value' => isset($config['article']) ? $this->nodeStorage->load($this->configuration['article']) : NULL,
      '#selection_settings' => [
        'target_bundles' => ['article'],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->cleanValues()->getValues());
  }

}
