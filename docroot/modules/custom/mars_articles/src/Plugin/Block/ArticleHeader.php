<?php

namespace Drupal\mars_articles\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\mars_common\ThemeConfiguratorParser;

/**
 * Class ArticleHeader.
 *
 * @Block(
 *   id = "article_header",
 *   admin_label = @Translation("Article header"),
 *   category = @Translation("Article"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label =
 *   @Translation("Article"))
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
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    DateFormatterInterface $date_formatter,
    ThemeConfiguratorParser $themeConfiguratorParser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->viewBuilder = $entity_type_manager->getViewBuilder('node');
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->dateFormatter = $date_formatter;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
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
      $container->get('date.formatter'),
      $container->get('mars_common.theme_configurator_parser')
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

    $build = [
      '#label' => $node->label(),
      '#eyebrow' => $this->configuration['eyebrow'],
      '#publication_date' => $node->isPublished() ? $this->dateFormatter->format($node->published_at->value, 'article_header') : NULL,
    ];

    // Check which template to use.
    if ($node->hasField('field_article_image') && $node->field_article_image->entity) {
      $build['#image'] = [
        'label' => $node->field_article_image->entity->label(),
        'url' => $node->field_article_image->entity->image->entity->createFileUrl(),
      ];
      $build['#theme'] = 'article_header_block_image';
    }
    else {
      $build['#theme'] = 'article_header_block_no_image';
    }

    // Get brand border path.
    $build['#brand_borders'] = $this->themeConfiguratorParser->getFileWithId('brand_borders', 'article-hero-border');
    $build['#social_links'] = $this->themeConfiguratorParser->socialLinks();

    return $build;
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
