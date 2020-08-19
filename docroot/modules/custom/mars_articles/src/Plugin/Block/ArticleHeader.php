<?php

namespace Drupal\mars_articles\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;

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
   * File storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory, DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->viewBuilder = $entity_type_manager->getViewBuilder('node');
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->configFactory = $config_factory;
    $this->dateFormatter = $date_formatter;
    $this->fileStorage = $entity_type_manager->getStorage('file');
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
      $container->get('config.factory'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_url;

    $node = $this->getContextValue('node');
    $theme_settings = $this->configFactory->get('emulsifymars.settings')->get();

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

    // Get graphic divider path.
    if (!empty($theme_settings['graphic_divider']) && count($theme_settings['graphic_divider']) > 0) {
      $devider_file = $this->fileStorage->load($theme_settings['graphic_divider'][0]);
      $build['#graphic_divider'] = !empty($devider_file) ? file_get_contents($base_url . $devider_file->createFileUrl()) : '';
    }

    $build['#social_links'] = $this->socialLinks();

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

  /**
   * Prepare social links data.
   *
   * @return array
   *   Rendered menu.
   */
  protected function socialLinks() {
    global $base_url;
    $social_menu_items = [];
    $social_medias = $this->configFactory->get('social_media.settings')
      ->get('social_media');

    foreach ($social_medias as $name => $social_media) {
      if ($social_media['enable'] != 1 || empty($social_media['api_url'])) {
        continue;
      }
      $social_menu_items[$name]['title'] = $social_media['text'];
      $social_menu_items[$name]['url'] = $social_media['api_url'];
      $social_menu_items[$name]['item_modifiers'] = $social_media['attributes'];

      if (isset($social_media['default_img']) && $social_media['default_img']) {
        $icon_path = $base_url . '/' . drupal_get_path('module', 'social_media') . '/icons/';
        $social_menu_items[$name]['icon'] = $icon_path . $name . '.svg';
      }
      elseif (!empty($social_media['img'])) {
        $social_menu_items[$name]['icon'] = $base_url . '/' . $social_media['img'];
      }
    }

    return $social_menu_items;
  }

}
