<?php

namespace Drupal\mars_product\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\Form\MarsCardColorSettingsForm;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_common\Traits\OverrideThemeTextColorTrait;
use Drupal\mars_common\Traits\SelectBackgroundColorTrait;
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
  use SelectBackgroundColorTrait;
  use OverrideThemeTextColorTrait;

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
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

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
      $container->get('mars_common.language_helper'),
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
    LanguageHelper $language_helper,
    MediaHelper $media_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configFactory = $config_factory;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->viewBuilder = $entity_type_manager->getViewBuilder('node');
    $this->themeConfiguratorParser = $theme_configurator_parser;
    $this->languageHelper = $language_helper;
    $this->mediaHelper = $media_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();
    $text_color_override = FALSE;
    if (!empty($conf['override_text_color']['override_color'])) {
      $text_color_override = static::$overrideColor;
    }
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
    $build['#title'] = $this->languageHelper->translate($conf['title']);
    $build['#graphic_divider'] = $this
      ->themeConfiguratorParser
      ->getGraphicDivider();
    if ($main_entity) {
      $build['#lead_card_entity'] = $main_entity;
      $build['#lead_card_eyebrow'] = $this->languageHelper->translate($conf['lead_card_eyebrow'] ?? $main_entity->type->entity->label());
      $build['#lead_card_title'] = $this->languageHelper->translate($conf['lead_card_title'] ?? NULL) ?: $main_entity->getTitle();
      $build['#cta_link_url'] = $main_entity->toUrl()->toString();
      $build['#cta_link_text'] = $this->languageHelper->translate($conf['cta_link_text'] ?? NULL) ?: $this->languageHelper->translate('Explore');
    }
    if ($supporting_entity) {
      $build['#supporting_card_entity'] = $supporting_entity;
      $build['#supporting_card_entity_view'] = array_merge($this->createSupportCardRenderArray(
        $supporting_entity
      ), ['#text_color_override' => $text_color_override]);
    }
    $build['#background'] = $this->getBgImage($main_entity);
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
      '#maxlength' => 55,
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
        'target_bundles' => ['product', 'product_multipack'],
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
      '#required' => TRUE,
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

    // Entity Browser element for background image.
    $form['background'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_ID,
      $this->configuration['background'], $form_state, 1, 'thumbnail', FALSE);
    // Convert the wrapping container to a details element.
    $form['background']['#type'] = 'details';
    $form['background']['#title'] = $this->t('Background');
    $form['background']['#open'] = TRUE;

    // Add select background color.
    $this->buildSelectBackground($form);
    $this->buildOverrideColorElement($form, $this->configuration);

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
    $this->configuration['background'] = $this->getEntityBrowserValue($form_state, 'background');
    $this->configuration['select_background_color'] = $form_state->getValue('select_background_color');
    $this->configuration['override_text_color'] = $form_state->getValue('override_text_color');
  }

  /**
   * Determine the bg image that should be used for the component.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface|null $main_entity
   *   The main entity of the component.
   *
   * @return string|null
   *   The bg image.
   */
  private function getBgImage(?ContentEntityInterface $main_entity): ?string {
    $bg_src = NULL;
    $background_id = NULL;
    $conf = $this->getConfiguration();
    if (!empty($conf['background'])) {
      $background_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($conf['background']);
    }
    elseif ($main_entity) {
      $background_id = $this->mediaHelper->getEntityMainMediaId($main_entity);
    }
    if ($background_id) {
      $background_params = $this->mediaHelper->getMediaParametersById($background_id);
      if (!isset($background_params['error'])) {
        $bg_src = $background_params['src'];
      }
    }
    return $bg_src;
  }

  /**
   * Returns a render array for the support entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $supporting_entity
   *   Entity that we would like to render.
   *
   * @return array
   *   Render array for the support card.
   */
  private function createSupportCardRenderArray(
    EntityInterface $supporting_entity
  ): array {
    $conf = $this->getConfiguration();
    $is_product_card = in_array($supporting_entity->bundle(), ['product', 'product_multipack']);

    $render_array = $this->viewBuilder->view(
      $supporting_entity,
      'card'
    );

    $default_eyebrow_text = $is_product_card ? $this->languageHelper->translate('Made With') : $this->languageHelper->translate('Seen In');
    $conf_eyebrow_text = $conf['supporting_card_eyebrow'] ?? NULL;
    $eyebrow_text = $conf_eyebrow_text ?: $default_eyebrow_text;
    $render_array['#eyebrow'] = $eyebrow_text;

    if ($is_product_card) {
      $brand_shape = $this->themeConfiguratorParser->getBrandShapeWithoutFill();
      $render_array['#brand_shape'] = $brand_shape;

      if (!empty($this->configuration['select_background_color']) && $this->configuration['select_background_color'] != 'default'
         && array_key_exists($this->configuration['select_background_color'], static::$colorVariables)
      ) {
        $render_array['#select__background__color'] = $this->configuration['select_background_color'];
      }
    }

    $conf = $this->configFactory->get(MarsCardColorSettingsForm::SETTINGS);
    CacheableMetadata::createFromRenderArray($render_array)
      ->merge(
        $this->themeConfiguratorParser->getCacheMetadataForThemeConfigurator()
      )
      ->addCacheableDependency($conf)
      ->applyTo($render_array);

    $render_array['#cache']['keys'][] = md5($eyebrow_text);
    return $render_array;
  }

}
