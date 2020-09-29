<?php

namespace Drupal\mars_product\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Product Content Pair Up block.
 *
 * @Block(
 *   id = "product_content_pair_up_block",
 *   admin_label = @Translation("MARS: Product Content Pair Up"),
 *   category = @Translation("Mars Product"),
 * )
 */
class ProductContentPairUpBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use EntityBrowserFormTrait;

  /**
   * Article or recipe first.
   */
  const ARTICLE_OR_RECIPE_FIRST = 'article_first';

  /**
   * Product first.
   */
  const PRODUCT_FIRST = 'product_first';

  /**
   * Lighthouse entity browser id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_ID = 'lighthouse_browser';

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * Media storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * Node View Builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * Theme configurator parser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * Mars Media Helper service.
   *
   * @var \Drupal\mars_common\MediaHelper
   */
  protected $mediaHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('mars_common.media_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    EntityTypeManager $entity_type_manager,
    ThemeConfiguratorParser $theme_configurator_parser,
    MediaHelper $media_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configFactory = $config_factory;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->viewBuilder = $entity_type_manager->getViewBuilder('node');
    $this->themeConfiguratorParser = $theme_configurator_parser;
    $this->mediaHelper = $media_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();
    $theme_settings = $this->configFactory->get('emulsifymars.settings')->get();

    /** @var \Drupal\node\Entity\Node $main_entity */
    /** @var \Drupal\node\Entity\Node $supporting_entity */
    switch ($conf['entity_priority']) {
      case self::PRODUCT_FIRST:
        $main_entity = !empty($conf['product']) ? $this->nodeStorage->load($conf['product']) : NULL;
        $supporting_entity = !empty($conf['article_recipe']) ? $this->nodeStorage->load($conf['article_recipe']) : NULL;
        break;

      case self::ARTICLE_OR_RECIPE_FIRST:
      default:
        $main_entity = !empty($conf['article_recipe']) ? $this->nodeStorage->load($conf['article_recipe']) : NULL;
        $supporting_entity = !empty($conf['product']) ? $this->nodeStorage->load($conf['product']) : NULL;

    }

    $build['#theme'] = 'product_content_pair_up_block';
    $build['#title'] = $conf['title'];
    $build['#graphic_divider'] = $this
      ->themeConfiguratorParser
      ->getFileContentFromTheme('graphic_divider');

    if (!empty($conf['background'])) {
      $mediaId = $this->mediaHelper->getIdFromEntityBrowserSelectValue($conf['background']);
      $mediaParams = $this->mediaHelper->getMediaParametersById($mediaId);
      if (!isset($mediaParams['error']) && !$mediaParams['error']) {
        $build['#background'] = file_create_url($mediaParams['src']);
      }
    }

    if ($main_entity) {
      $build['#lead_card_entity'] = $main_entity;
      $build['#lead_card_eyebrow'] = ($conf['lead_card_eyebrow'] ?? NULL) ?: $main_entity->type->entity->label();
      $build['#lead_card_title'] = ($conf['lead_card_title'] ?? NULL) ?: $main_entity->getTitle();
      $build['#cta_link_url'] = $main_entity->toUrl()->toString();
      $build['#cta_link_text'] = ($conf['cta_link_text'] ?? NULL) ?: $this->t('Explore');

      // TODO: Consider Strategy pattern if logic gets more complex.
      if (empty($build['#background'])) {
        switch ($main_entity->getType()) {
          case 'article':
            $build['#background'] = $main_entity
              ->field_article_image
              ->entity
              ->image
              ->entity
              ->createFileUrl();
            break;

          case 'recipe':
            $build['#background'] = $main_entity
              ->field_recipe_image
              ->entity
              ->image
              ->entity
              ->createFileUrl();
            break;

          case 'product':
            $build['#background'] = $main_entity
              ->field_product_variants
              ->first()
              ->entity
              ->field_product_key_image
              ->entity
              ->image
              ->entity
              ->createFileUrl();
            break;

          default:
            $build['#background'] = NULL;
        }
      }
    }

    if ($supporting_entity) {
      $build['#supporting_card_entity'] = $supporting_entity;

      $default_eyebrow_text = $supporting_entity->bundle() == 'product' ? $this->t('Made With') : $this->t('Seen In');
      $conf_eyebrow_text = $conf['supporting_card_eyebrow'] ?? NULL;
      $view_mode = 'card';

      $build['#supporting_card_entity_view'] = $this->viewBuilder->view($supporting_entity, $view_mode);
      $eyebrow_text = $conf_eyebrow_text ?: $default_eyebrow_text;
      $build['#supporting_card_entity_view']['#eyebrow'] = $eyebrow_text;
      $build['#supporting_card_entity_view']['#cache']['keys'][] = md5($eyebrow_text);
      $build['#supporting_card_eyebrow'] = $build['#supporting_card_entity_view']['#eyebrow'];
    }

    // Get PNG asset path.
    if (!empty($theme_settings['png_asset']) && count($theme_settings['png_asset']) > 0) {
      /** @var \Drupal\file\Entity\File $png_asset_file */
      $png_asset_file = $this->fileStorage->load($theme_settings['png_asset'][0]);
      $build['#png_asset'] = !empty($png_asset_file) ? $png_asset_file->createFileUrl() : NULL;
    }

    $build['#max_width'] = $conf['max_width'];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
      '#maxlength' => 33,
      '#default_value' => $this->configuration['title'] ?? NULL,
    ];

    $form['entity_priority'] = [
      '#type' => 'select',
      '#title' => $this->t('Variants'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['entity_priority'] ?? NULL,
      '#options' => [
        self::ARTICLE_OR_RECIPE_FIRST => $this->t('Supporting product variant'),
        self::PRODUCT_FIRST => $this->t('Lead product variant'),
      ],
    ];

    $form['article_recipe'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Article/Recipe'),
      '#target_type' => 'node',
      '#default_value' => ($node_id = $this->configuration['article_recipe'] ?? NULL) ? $this->nodeStorage->load($node_id) : NULL,
      '#selection_settings' => [
        'target_bundles' => ['article', 'recipe'],
      ],
    ];

    $form['product'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Product'),
      '#target_type' => 'node',
      '#default_value' => ($node_id = $this->configuration['product'] ?? NULL) ? $this->nodeStorage->load($node_id) : NULL,
      '#selection_settings' => [
        'target_bundles' => ['product'],
      ],
    ];

    $form['lead_card_eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Master Card Eyebrow'),
      '#description' => $this->t('Defaults to master entity type label, e.g. <em>Recipe</em>, <em>Article</em>, <em>Product</em>.'),
      '#maxlength' => 15,
      '#default_value' => $this->configuration['lead_card_eyebrow'] ?? NULL,
    ];

    $form['lead_card_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Master Card Title'),
      '#description' => $this->t('Set this field to override default Master Card title which defaults to node title'),
      '#maxlength' => 33,
      '#default_value' => $this->configuration['lead_card_title'] ?? NULL,
    ];

    $form['cta_link_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Link text'),
      '#maxlength' => 15,
      '#placeholder' => $this->t('Explore'),
      '#default_value' => $this->configuration['cta_link_text'] ?? NULL,
    ];

    $form['supporting_card_eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Supporting Card Eyebrow'),
      '#description' => $this->t('Defaults to Made With or Seen In'),
      '#placeholder' => $this->t('Made With / Seen In'),
      '#maxlength' => 15,
      '#default_value' => $this->configuration['supporting_card_eyebrow'] ?? NULL,
    ];

    $form['max_width'] = [
      '#type' => 'select',
      '#title' => $this->t('Max Width'),
      '#required' => TRUE,
      '#options' => [
        '1440' => '1440',
        '768' => '768',
        '375' => '375',
      ],
      '#default_value' => (string) ($this->configuration['max_width'] ?? 1440),
    ];

    // Entity Browser element for background image.
    $form['background'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_ID, $this->configuration['background'], 1, 'thumbnail');
    // Convert the wrapping container to a details element.
    $form['background']['#type'] = 'details';
    $form['background']['#title'] = $this->t('Background');
    $form['background']['#open'] = TRUE;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->configuration['entity_priority'] = $form_state->getValue('entity_priority');
    $this->configuration['article_recipe'] = $form_state->getValue('article_recipe');
    $this->configuration['product'] = $form_state->getValue('product');
    $this->configuration['title'] = $form_state->getValue('title');
    $this->configuration['lead_card_eyebrow'] = $form_state->getValue('lead_card_eyebrow');
    $this->configuration['lead_card_title'] = $form_state->getValue('lead_card_title');
    $this->configuration['cta_link_text'] = $form_state->getValue('cta_link_text');
    $this->configuration['supporting_card_eyebrow'] = $form_state->getValue('supporting_card_eyebrow');
    $this->configuration['max_width'] = $form_state->getValue('max_width');
    $this->configuration['background'] = $this->getEntityBrowserValue($form_state, 'background');
  }

  /**
   * Helper method that loads Media file URL using Media id.
   *
   * @param int $media_id
   *   Media ID.
   *
   * @return string|null
   *   File URI or NULL if URI cannot be defined.
   */
  protected function getMediaUriById($media_id) {
    if (empty($media_id) || !($entity = $this->mediaStorage->load($media_id))) {
      return NULL;
    }
    if (!$entity->image || !$entity->image->target_id) {
      return NULL;
    }
    return $entity->image->entity->uri->value ?? NULL;
  }

}
