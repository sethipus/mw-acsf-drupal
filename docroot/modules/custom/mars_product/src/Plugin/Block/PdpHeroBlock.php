<?php

namespace Drupal\mars_product\Plugin\Block;

use Acquia\Blt\Robo\Common\EnvironmentDetector;
use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\Form\MarsCardColorSettingsForm;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_product\ProductHelper;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Header block.
 *
 * @Block(
 *   id = "pdp_hero_block",
 *   admin_label = @Translation("MARS: PDP Hero"),
 *   category = @Translation("Product"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Product"))
 *   }
 * )
 */
class PdpHeroBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
  protected $config;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Helper service to deal with media.
   *
   * @var \Drupal\mars_media\MediaHelper
   */
  private $mediaHelper;

  /**
   * Product helper service.
   *
   * @var \Drupal\mars_product\ProductHelper
   */
  private $productHelper;

  /**
   * Where to buy global configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $wtbGlobalConfig;

  /**
   * Whether reviews are enabled or not by default.
   *
   * @var bool
   */
  private $defaultReviewState;

  /**
   * WTB Price spider provider id.
   */
  const VENDOR_PRICE_SPIDER = 'price_spider';

  /**
   * WTB Commerce connector provider id.
   */
  const VENDOR_COMMERCE_CONNECTOR = 'commerce_connector';

  /**
   * WTB Smart Commerce provider id.
   */
  const VENDOR_SMART_COMMERCE = 'smart_commerce';

  /**
   * WTB none provider id.
   */
  const VENDOR_NONE = 'none';

  /**
   * Fields with bold labels.
   */
  const FIELDS_WITH_BOLD_LABELS = [
    'field_product_calories' => 'Calories',
    'field_product_total_fat' => 'Total Fat',
    'field_product_cholesterol' => 'Cholesterol',
    'field_product_sodium' => 'Sodium',
    'field_product_carb' => 'Total Carbohydrate',
    'field_product_protein' => 'Protein',
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    EntityRepositoryInterface $entity_repository,
    EntityFormBuilderInterface $entity_form_builder,
    ThemeConfiguratorParser $themeConfiguratorParser,
    LanguageHelper $language_helper,
    ProductHelper $product_helper,
    MediaHelper $media_helper,
    ImmutableConfig $wtb_global_config,
    bool $default_review_state,
    ConfigFactoryInterface $config_factory
  ) {
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->entityRepository = $entity_repository;
    $this->entityFormBuilder = $entity_form_builder;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->languageHelper = $language_helper;
    $this->productHelper = $product_helper;
    $this->mediaHelper = $media_helper;
    $this->wtbGlobalConfig = $wtb_global_config;
    $this->defaultReviewState = $default_review_state;
    $this->configFactory = $config_factory;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $config_factory = $container->get('config.factory');
    $global_wtb_config = $config_factory->get('mars_product.wtb.settings');
    $default_review_state = $config_factory
      ->get('emulsifymars.settings')
      ->get('show_rating_and_reviews') ?? FALSE;
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity.repository'),
      $container->get('entity.form_builder'),
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('mars_common.language_helper'),
      $container->get('mars_product.product_helper'),
      $container->get('mars_media.media_helper'),
      $global_wtb_config,
      (bool) $default_review_state,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $commerce_vendor = $this->getCommerceVendor();

    $form['eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#maxlength' => 15,
      '#default_value' => $this->configuration['eyebrow'] ?? '',
      '#required' => TRUE,
    ];
    $form['available_sizes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Available sizes'),
      '#maxlength' => 50,
      '#default_value' => $this->configuration['available_sizes'] ?? '',
      '#required' => TRUE,
    ];

    $form['wtb'] = [
      '#type' => 'details',
      '#title' => $this->t('Where to buy button settings'),
      '#description' => $this->t('Vendor: @vendor',
        ['@vendor' => $commerce_vendor]),
      '#open' => TRUE,
    ];

    $form['wtb']['override_global'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Override global WTB settings'),
      '#default_value' => $this->configuration['wtb']['override_global'] ?? FALSE,
      '#name' => 'override_global',
    ];

    $form['wtb']['data_widget_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Widget id'),
      '#default_value' => $this->configuration['wtb']['data_widget_id'],
      '#states' => [
        'visible' => [
          [':input[name="override_global"]' => ['checked' => TRUE]],
        ],
        'required' => [
          [':input[name="override_global"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['wtb']['product_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product SKU'),
      '#default_value' => $this->configuration['wtb']['product_id'],
      '#description' => $this->t("If left empty then the product variant's SKU is used."),
      '#states' => [
        'visible' => [
          [':input[name="override_global"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    switch ($commerce_vendor) {
      case self::VENDOR_COMMERCE_CONNECTOR:
        $form['wtb']['data_token'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Token'),
          '#default_value' => $this->configuration['wtb']['data_token'],
          '#states' => [
            'visible' => [
              [':input[name="override_global"]' => ['checked' => TRUE]],
            ],
          ],
        ];

        $form['wtb']['data_subid'] = [
          '#type' => 'textfield',
          '#title' => $this->t('SubId'),
          '#default_value' => $this->configuration['wtb']['data_subid'],
          '#states' => [
            'visible' => [
              [':input[name="override_global"]' => ['checked' => TRUE]],
            ],
          ],
        ];

        $form['wtb']['cta_title'] = [
          '#type' => 'textfield',
          '#title' => $this->t('CTA title'),
          '#default_value' => $this->configuration['wtb']['cta_title'],
          '#states' => [
            'visible' => [
              [':input[name="override_global"]' => ['checked' => TRUE]],
            ],
          ],
        ];

        $form['wtb']['button_type'] = [
          '#type' => 'select',
          '#title' => $this->t('Commerce Connector: button type'),
          '#default_value' => 'my_own',
          '#options' => [
            'my_own' => $this->t('My own button'),
            'commerce_connector' => $this->t('Commerce Connector button'),
          ],
          '#states' => [
            'visible' => [
              [':input[name="override_global"]' => ['checked' => TRUE]],
            ],
          ],
          '#disabled' => TRUE,
        ];

        $form['wtb']['data_locale'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Commerce Connector: data locale'),
          '#default_value' => $this->configuration['wtb']['data_locale'],
          '#states' => [
            'visible' => [
              [':input[name="override_global"]' => ['checked' => TRUE]],
            ],
          ],
        ];
        break;

      case self::VENDOR_SMART_COMMERCE:
        $form['wtb']['brand_js'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Smart Commerce brand specific JS file URL'),
          '#default_value' => $this->configuration['wtb']['brand_js'],
          '#states' => [
            'visible' => [
              [':input[name="override_global"]' => ['checked' => TRUE]],
            ],
          ],
        ];
        $form['wtb']['brand_css'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Smart Commerce brand specific CSS file URL'),
          '#default_value' => $this->configuration['wtb']['brand_css'],
          '#states' => [
            'visible' => [
              [':input[name="override_global"]' => ['checked' => TRUE]],
            ],
          ],
        ];
        break;

      default:
        break;
    }

    $form['nutrition'] = [
      '#type' => 'details',
      '#title' => $this->t('Nutrition part settings'),
      '#open' => TRUE,
    ];
    $form['nutrition']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nutrition section label'),
      '#default_value' => $this->configuration['nutrition']['label'],
      '#maxlength' => 18,
    ];
    $form['nutrition']['serving_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Amount per serving label'),
      '#default_value' => $this->configuration['nutrition']['serving_label'],
      '#required' => TRUE,
    ];
    $form['nutrition']['daily_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Daily value label'),
      '#default_value' => $this->configuration['nutrition']['daily_label'],
      '#required' => TRUE,
    ];
    $form['nutrition']['vitamins_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Vitamins & minerals label'),
      '#default_value' => $this->configuration['nutrition']['vitamins_label'],
      '#required' => TRUE,
    ];
    $form['nutrition']['added_sugars_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Added Sugars pre label'),
      '#default_value' => $this->configuration['nutrition']['added_sugars_label'],
    ];
    $form['nutrition']['daily_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Daily value information text'),
      '#default_value' => $this->configuration['nutrition']['daily_text'],
      '#required' => TRUE,
    ];
    $form['nutrition']['refer_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Refer part text'),
      '#default_value' => $this->configuration['nutrition']['refer_text'],
      '#required' => TRUE,
    ];
    $form['allergen_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Diet & Allergens part label'),
      '#default_value' => $this->configuration['allergen_label'],
      '#maxlength' => 18,
    ];
    $form['more_information'] = [
      '#type' => 'details',
      '#title' => $this->t('More information part settings'),
      '#open' => TRUE,
    ];
    $form['more_information']['more_information_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('More information label'),
      '#default_value' => $this->configuration['more_information']['more_information_label'] ?? '',
      '#maxlength' => 18,
      '#required' => TRUE,
    ];
    $form['more_information']['show_more_information_label'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show More information tab'),
      '#default_value' => $this->configuration['more_information']['show_more_information_label'] ?? TRUE,
    ];
    $form['use_background_color'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Background Color Override'),
      '#default_value' => $this->configuration['use_background_color'] ?? FALSE,
    ];
    $form['background_color'] = [
      '#type' => 'jquery_colorpicker',
      '#title' => $this->t('Background Color Override'),
      '#default_value' => $this->configuration['background_color'] ?? '',
      '#states' => [
        'visible' => [
          ':input[name="settings[use_background_color]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $color_a = $this->themeConfiguratorParser->getSettingValue('color_a');
    $color_e = $this->themeConfiguratorParser->getSettingValue('color_e');

    $form['color_helper'] = [
      '#type' => 'markup',
      '#markup' => $this->languageHelper->translate(
        'For light background please select color A for text and full opacity brand shape.
        For dark background please select color E or white for text and 20% opacity shape.'),
    ];

    $form['text_color'] = [
      '#type' => 'select',
      '#title' => $this->t('Text color'),
      '#options' => [
        'color_a' => 'Color A - #' . $color_a,
        'color_e' => 'Color E - #' . $color_e,
        'color_w' => $this->t('White'),
      ],
      '#default_value' => $this->configuration['text_color'] ?? 'color_a',
    ];

    $form['brand_shape_color'] = [
      '#type' => 'jquery_colorpicker',
      '#title' => $this->t('Brand shape color'),
      '#default_value' => $this->configuration['brand_shape_color'] ?? '',
    ];

    $form['brand_shape_opacity'] = [
      '#type' => 'select',
      '#title' => $this->t('Brand shape opacity'),
      '#options' => [
        'partial' => $this->t('Default 20% opacity'),
        'full' => $this->t('Full opacity'),
      ],
      '#default_value' => $this->configuration['brand_shape_opacity'] ?? 'partial',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * This method processes the blockForm() form fields when the block
   * configuration form is submitted.
   *
   * The blockValidate() method can be used to validate the form submission.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $override_global = !empty($form_state->getUserInput()['override_global']);
    $values['wtb']['override_global'] = $override_global ?? 0;
    $this->setConfiguration($values);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = $this->getConfiguration();

    $display = 'product_hero';
    $widget_id_field = $this->productHelper->getWidgetIdField($display);

    return [
      'label_display' => FALSE,
      'use_background_color' => $config['use_background_color'] ?? FALSE,
      'text_color' => $config['text_color'] ?? NULL,
      'brand_shape_color' => $config['brand_shape_color'] ?? NULL,
      'brand_shape_opacity' => $config['brand_shape_opacity'] ?? NULL,
      'eyebrow' => $config['eyebrow'] ?? $this->t('Products'),
      'available_sizes' => $config['available_sizes'] ?? $this->t('Available sizes'),
      'nutrition' => [
        'label' => $config['nutrition']['label'] ?? $this->t('Nutrition'),
        'serving_label' => $config['nutrition']['serving_label'] ?? $this->t('Amount per serving'),
        'daily_label' => $config['nutrition']['daily_label'] ?? $this->t('% Daily value'),
        'vitamins_label' => $config['nutrition']['vitamins_label'] ?? $this->t('Vitamins | Minerals'),
        'added_sugars_label' => $config['nutrition']['added_sugars_label'] ?? $this->languageHelper->translate('Includes'),
        'daily_text' => $config['nutrition']['daily_text'] ?? $this->languageHelper->translate(
            'The % Daily Value (DV) tells you how much a nutrient in a serving of food' .
            ' contributes to a daily diet. 2,000 calories a day is used for general advice.'
        ),
        'refer_text' => $config['nutrition']['refer_text'] ?? $this->languageHelper->translate(
            'Please refer to the product label for the most accurate nutrition, ingredient, and allergen information.'),
      ],
      'allergen_label' => $config['allergen_label'] ?? $this->t('Diet & Allergens'),
      'more_information_label' => $config['more_information']['more_information_label'] ?? $this->t('More information'),
      'show_more_information_label' => $config['more_information']['show_more_information_label'] ?? TRUE,
      'wtb' => [
        'data_widget_id' => $config['wtb']['data_widget_id'] ?? $this->wtbGlobalConfig->get($widget_id_field) ?? NULL,
        'data_display' => $display,
        'data_token' => $config['wtb']['data_token'] ?? $this->wtbGlobalConfig->get('data_token') ?? NULL,
        'data_subid' => $config['wtb']['data_subid'] ?? $this->wtbGlobalConfig->get('data_subid') ?? NULL,
        'cta_title' => $config['wtb']['cta_title'] ?? $this->wtbGlobalConfig->get('cta_title') ?? NULL,
        'product_id' => $config['wtb']['product_id'] ?? NULL,
        'button_type' => $config['wtb']['button_type'] ?? $this->wtbGlobalConfig->get('button_type') ?? NULL,
        'data_locale' => $config['wtb']['data_locale'] ?? $this->wtbGlobalConfig->get('data_locale') ?? NULL,
        'brand_js' => $this->wtbGlobalConfig->get('brand_js') ?? NULL,
        'brand_css' => $this->wtbGlobalConfig->get('brand_css') ?? NULL,
        'override_global' => !empty($config['wtb']['override_global']) ? $config['wtb']['override_global'] : FALSE,
      ],

    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Product node.
    $node = $this->getContextValue('node');
    // Commerce vendor info.
    $commerce_vendor = $this->getCommerceVendor();
    $commerce_vendor_settings = $this->getCommerceVendorInfo($commerce_vendor);
    // Get values from first Product Variant.
    $product_sku = '';
    foreach ($node->field_product_variants as $reference) {
      $product_variant = $reference->entity;
      $product_sku = $this->productHelper->formatSku($product_variant->get('field_product_sku')->value);
    }
    $background_color = !empty($this->configuration['use_background_color']) && !empty($this->configuration['background_color']) ?
      '#' . $this->configuration['background_color'] : '';
    $brand_shape_color = !empty($this->configuration['brand_shape_color']) ?
      '#' . $this->configuration['brand_shape_color'] : '';
    $more_information_id = Html::getUniqueId('section-more-information');
    $card_grid_bg_color_key = $this->configFactory->get(MarsCardColorSettingsForm::SETTINGS)->get('select_background_color_product');
    $pdp_common_data = [
      'hero_data' => [
        'product_label' => $this->languageHelper->translate($this->configuration['eyebrow'] ?? ''),
        'size_label' => $this->languageHelper->translate($this->configuration['available_sizes'] ?? ''),
        'brand_shape' => $this->themeConfiguratorParser->getBrandShapeWithoutFill(),
        'background_color' => $background_color,
        'product_name' => $node->title->value,
        'product_description' => $node->field_product_description->value,
        'product_sku' => !empty($this->configuration['wtb']['override_global']) && !empty($this->configuration['wtb']['product_id']) ? $this->configuration['wtb']['product_id'] : $product_sku,
        'commerce_vendor' => $commerce_vendor !== self::VENDOR_NONE ? $commerce_vendor : NULL,
        'data_widget_id' => empty($this->configuration['wtb']['override_global']) && !empty($commerce_vendor_settings['widget_id']) ? $commerce_vendor_settings['widget_id'] : $this->configuration['wtb']['data_widget_id'],
        'data_token' => empty($this->configuration['wtb']['override_global']) && !empty($commerce_vendor_settings['data_token']) ? $commerce_vendor_settings['data_token'] : $this->configuration['wtb']['data_token'],
        'data_subid' => empty($this->configuration['wtb']['override_global']) && !empty($commerce_vendor_settings['data_subid']) ? $commerce_vendor_settings['data_subid'] : $this->configuration['wtb']['data_subid'],
        'product_CTA_title' => empty($this->configuration['wtb']['override_global']) && !empty($commerce_vendor_settings['cta_title']) ? $commerce_vendor_settings['cta_title'] : $this->configuration['wtb']['cta_title'],
        'button_type' => empty($this->configuration['wtb']['override_global']) && !empty($commerce_vendor_settings['button_type']) ? $commerce_vendor_settings['button_type'] : $this->configuration['wtb']['button_type'],
        'data_locale' => empty($this->configuration['wtb']['override_global']) && !empty($commerce_vendor_settings['data_locale']) ? $commerce_vendor_settings['data_locale'] : $this->configuration['wtb']['data_locale'],
        'text_color' => $this->configuration['text_color'] ?? 'color_a',
        'brand_shape_color' => $brand_shape_color,
        'brand_shape_opacity' => $this->configuration['brand_shape_opacity'] ?? 'partial',
        'card_sticky_bg_color' => $card_grid_bg_color_key,
      ],
      'nutrition_data' => [
        'nutritional_label' => $this->languageHelper->translate($this->configuration['nutrition']['label']) ?? '',
        'nutritional_info_serving_label' => $this->languageHelper->translate($this->configuration['nutrition']['serving_label']) ?? '',
        'nutritional_info_daily_label' => $this->languageHelper->translate($this->configuration['nutrition']['daily_label']) ?? '',
        'vitamins_info_label' => $this->languageHelper->translate($this->configuration['nutrition']['vitamins_label']) . ':' ?? '',
        'daily_text' => $this->languageHelper->translate($this->configuration['nutrition']['daily_text']) ?? '',
        'refer_text' => $this->languageHelper->translate($this->configuration['nutrition']['refer_text']) ?? '',
      ],
      'allergen_data' => [
        'allergen_label' => $this->languageHelper->translate($this->configuration['allergen_label']),
      ],
      'more_information_data' => [
        'more_information_label' => $this->languageHelper->translate($this->configuration['more_information']['more_information_label'] ?? 'More information'),
        'show_more_information_label' => $this->configuration['more_information']['show_more_information_label'] ?? TRUE,
        'more_information_id' => $more_information_id,
      ],
    ];
    $build['#pdp_common_data'] = $pdp_common_data;
    $build['#pdp_size_data'] = $this->getSizeData($node);

    $node_bundle = $node->bundle();
    $build['#pdp_bundle_type'] = $node_bundle;
    switch ($node_bundle) {
      case 'product_multipack':
        $build['#pdp_data'] = $this->getPdpMultiPackProductData($node, $more_information_id);
        break;

      case 'product':
        $build['#pdp_data'] = $this->getPdpSingleProductData($node, $more_information_id);
        break;

      default:
        break;
    }

    $build['#theme'] = 'pdp_hero_block';
    $this->pageAttachments($build, $node);

    return $build;
  }

  /**
   * Get single product PDP data.
   *
   * @param object $node
   *   Product node.
   * @param string $more_information_id
   *   ID for more information section.
   *
   * @return array
   *   PDP data array.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function getPdpSingleProductData($node, string $more_information_id) {
    $items = [];
    $i = 0;

    /* @var \Drupal\node\NodeInterface $node */
    $main_variant = $this->productHelper->mainVariant($node);

    foreach ($node->field_product_variants as $reference) {
      $product_variant = $this->languageHelper->getTranslation($reference->entity);
      $size_id = $product_variant->id();
      $i++;
      $state = ($main_variant->id() == $product_variant->id()) ? 'true' : 'false';
      if (!empty($this->configuration['wtb']['product_id'])) {
        $gtin = trim($this->configuration['wtb']['product_id']);
      }
      else {
        $gtin = trim($product_variant->get('field_product_sku')->value);
        $gtin = $this->productHelper->formatSku($gtin);
      }

      $items[] = [
        'gtin' => $gtin,
        'size_id' => $size_id,
        'active' => $state,
        'hero_data' => [
          'image_items' => $this->getImageItems($product_variant),
          'mobile_sections_items' => $this->getMobileItems($product_variant, $node->bundle(), $more_information_id),
        ],
        'nutrition_data' => [
          'serving_item' => $this->getServingItems($product_variant),
        ],
        'allergen_data' => [
          'allergens_list' => $this->getVisibleAllergenItems($product_variant),
        ],
        'show_rating_and_reviews' => $this->isRatingEnable($node),
        'is_main_variant' => $i === 1,
      ];
    }

    return $items;
  }

  /**
   * Get multipack product PDP data.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   Product node.
   * @param string $more_information_id
   *   ID for more information section.
   *
   * @return array
   *   PDP data array.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function getPdpMultiPackProductData(EntityInterface $node, string $more_information_id) {
    $products_data = [];
    foreach ($node->field_product_pack_items as $product_reference) {
      $product = $this->languageHelper->getTranslation($product_reference->entity);
      $product_variant_first = $this->productHelper->mainVariant($product);
      if (empty($product_variant_first)) {
        continue;
      }
      $serving_items = $this->getServingItems($product_variant_first);

      $products_data[] = [
        'product_title' => $product->getTitle(),
        'product_image' => $this->getProductVariantImage($product_variant_first),
        'nutrition_data' => [
          'serving_item' => $serving_items,
          'serving_item_empty' => $this->isServingItemsEmpty($serving_items),
        ],
        'allergen_data' => [
          'allergens_list' => $this->getVisibleAllergenItems($product_variant_first),
        ],
      ];
    }

    $items = [];
    $i = 0;
    foreach ($node->field_product_variants as $reference) {
      $product_variant = $this->languageHelper->getTranslation($reference->entity);
      $size_id = $product_variant->id();
      $i++;
      $state = $i == 1 ? 'true' : 'false';
      $gtin = $product_variant->get('field_product_sku')->value;
      $items[] = [
        'gtin' => !empty($this->configuration['wtb']['product_id']) ? $this->configuration['wtb']['product_id'] : $gtin,
        'size_id' => $size_id,
        'active' => $state,
        'hero_data' => [
          'image_items' => $this->getImageItems($product_variant),
          'mobile_sections_items' => $this->getMobileItems($product_variant, $node->bundle(), $more_information_id),
        ],
        'products'  => $products_data,
      ];
    }

    return $items;
  }

  /**
   * Get product variant image.
   *
   * @param \Drupal\Core\Entity\EntityInterface $product_variant
   *   Product variant.
   *
   * @return array
   *   Render array.
   */
  protected function getProductVariantImage(EntityInterface $product_variant) {
    $media_id = $this->mediaHelper->getEntityMainMediaId($product_variant);
    $media_params = $this->mediaHelper->getMediaParametersById($media_id);

    if ($media_params['error'] ?? FALSE) {
      return [
        'src' => NULL,
        'alt' => '',
      ];
    }
    return [
      'src' => $media_params['src'],
      'alt' => $media_params['alt'],
    ];
  }

  /**
   * Get Image items.
   *
   * @param object $node
   *   Product Variant node.
   *
   * @return array
   *   Image items array.
   */
  public function getImageItems($node) {
    $items = [];
    $map = [
      'field_product_key_image' => 'field_product_key_image_override',
      'field_product_image_1' => 'field_product_image_1_override',
      'field_product_image_2' => 'field_product_image_2_override',
      'field_product_image_3' => 'field_product_image_3_override',
      'field_product_image_4' => 'field_product_image_4_override',
    ];

    foreach ($map as $image_field => $image_field_override) {
      $media_override_id = $node->get($image_field_override)->target_id;
      $media_params = $this->mediaHelper->getMediaParametersById($media_override_id);

      // Override media missing or has error try the normal version.
      if ($media_params['error'] ?? FALSE) {
        $media_id = $node->get($image_field)->target_id;
        $media_params = $this->mediaHelper->getMediaParametersById($media_id);
      }

      // Override media and the normal version both failed, we should skip this.
      if ($media_params['error'] ?? FALSE) {
        continue;
      }

      $image_src = $media_params['src'];
      $image_alt = $media_params['alt'];

      $format = '%s 375w, %s 768w, %s 1024w, %s 1440w';
      $items[] = [
        'image' => [
          'srcset' => sprintf($format, $image_src, $image_src, $image_src, $image_src),
          'src' => $image_src,
          'alt' => $image_alt,
        ],
      ];

    }

    return $items;
  }

  /**
   * Get Size items.
   *
   * @param object $node
   *   Product Variant node.
   *
   * @return array
   *   Size items array.
   */
  public function getSizeData($node) {
    $items = [];
    $field_size = 'field_product_size';
    foreach ($node->field_product_variants as $reference) {
      $product_variant = $this->languageHelper->getTranslation($reference->entity);
      $size = $product_variant->get($field_size)->value;
      $size_id = $product_variant->id();
      $items[] = [
        'size_id' => $size_id,
        'title' => $size,
        'link_url' => '#',
      ];
    }

    return $items;
  }

  /**
   * Get Serving items.
   *
   * @param object $node
   *   Product Variant node.
   *
   * @return array
   *   Serving items array.
   */
  public function getServingItems($node) {
    $result_item = [
      'ingredients_value' => strip_tags(html_entity_decode($node->get('field_product_ingredients')->value)),
      'warnings_value' => strip_tags(html_entity_decode($node->get('field_product_allergen_warnings')->value)),
      'serving_size' => [
        'label' => $this->languageHelper->translate($node->get('field_product_serving_size')->getFieldDefinition()->getLabel()) . ':',
        'value' => $node->get('field_product_serving_size')->value,
      ],
      'serving_per_container' => [
        'label' => $this->languageHelper->translate($node->get('field_product_servings_per')->getFieldDefinition()->getLabel()) . ':',
        'value' => $node->get('field_product_servings_per')->value,
      ],
    ];

    $mapping = $this->getGroupingMethod($node);
    foreach ($mapping as $section => $fields) {
      foreach ($fields as $field => $field_daily) {
        $bold_modifier = array_key_exists($field, self::FIELDS_WITH_BOLD_LABELS) ? TRUE : FALSE;
        $item = [
          'label' => $this->languageHelper->translate(
            $node->get($field)
              ->getFieldDefinition()
              ->getLabel()
          ),
          'value' => $node->get($field)->value,
          'bold_modifier' => $bold_modifier,
        ];
        if ($field_daily !== FALSE) {
          $field_daily = !empty($field_daily) ? $field_daily : $field . '_daily';
          $item['value_daily']
            = $node->get($field_daily)->value;
        }
        if ($field === 'field_product_added_sugars') {
          $item['pre_label'] = $this->languageHelper->translate(
            $this->configuration['nutrition']['added_sugars_label']) ?? '';
        }
        if (isset($item['value']) || isset($item['value_daily'])) {
          $result_item[$section][] = $item;
        }
      }
    }

    return $result_item;
  }

  /**
   * Check serving items is empty or not.
   *
   * @param array $serving_items
   *   serving items - results of getServingItems.
   *
   * @return bool
   *   reflects serving items empty or non-empty state.
   */
  public function isServingItemsEmpty(array $serving_items) {
    if (
      empty($serving_items['ingredients_value'] ?? NULL) &&
      empty($serving_items['warnings_value'] ?? NULL) &&
      empty($serving_items['serving_size']['value'] ?? NULL) &&
      empty($serving_items['serving_per_container']['value'] ?? NULL)
    ) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get Field Mapping for grouping.
   *
   * @param object $node
   *   Product Variant node.
   *
   * @return array
   *   Size items array.
   */
  public function getGroupingMethod($node) {
    $field_mapping = [
      'field_product_calories' => FALSE,
      'field_product_calories_fat' => FALSE,
      'field_product_total_fat' => '',
      'field_product_saturated_fat' => 'field_product_saturated_daily',
      'field_product_trans_fat' => '',
      'field_product_cholesterol' => '',
      'field_product_sodium' => '',
      'field_product_carb' => '',
      'field_product_dietary_fiber' => 'field_product_dietary_daily',
      'field_product_sugars' => '',
      'field_product_total_sugars' => FALSE,
      'field_product_sugar_alcohol' => FALSE,
      'field_product_added_sugars' => '',
      'field_product_protein' => '',
      'field_product_vitamin_a' => '',
      'field_product_vitamin_c' => '',
      'field_product_vitamin_d' => '',
      'field_product_calcium' => '',
      'field_product_thiamin' => '',
      'field_product_niacin' => '',
      'field_product_iron' => '',
      'field_product_potassium' => '',
      'field_product_riboflavin' => '',
    ];
    $groups_mapping = [
      'group_nutritional_subgroup_1',
      'group_nutritional_subgroup_2',
      'group_nutritional_subgroup_3',
      'group_vitamins',
    ];

    $form = $this->entityFormBuilder->getForm($node);
    $mapping = [];
    foreach ($groups_mapping as $group) {
      foreach ($form['#fieldgroups'] as $fieldgroup) {
        if ($fieldgroup->group_name == $group) {
          foreach ($fieldgroup->children as $field) {
            if (strpos($field, 'daily') === FALSE) {
              $mapping[$group][$field] = $field_mapping[$field];
            }
          }
        }
      }
    }

    return $mapping;
  }

  /**
   * Get theme setting for Allergen info visibility.
   *
   * @return bool
   *   Allergen visibility
   */
  public function isAllergenVisible() {
    $show_allergen_info = $this->themeConfiguratorParser->getSettingValue('show_allergen_info');
    if ($show_allergen_info) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get all visible allergen items.
   *
   * @param object $node
   *   Product Variant node.
   *
   * @return array
   *   Allergen items array.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function getVisibleAllergenItems($node) {
    if ($this->isAllergenVisible()) {
      return $this->getAllergenItems($node);
    }
    return [];
  }

  /**
   * Get Allergen items.
   *
   * @param object $node
   *   Product Variant node.
   *
   * @return array
   *   Allergen items array.
   */
  public function getAllergenItems($node) {
    $items = [];
    foreach ($node->get('field_product_diet_allergens') as $reference) {
      $allergen_term = $this->languageHelper->getTranslation($reference->entity);

      $media_id = $this->mediaHelper->getEntityMainMediaId($allergen_term);
      $media_params = $this->mediaHelper->getMediaParametersById($media_id);
      if (!($media_params['error'] ?? FALSE) && ($media_params['src'] ?? FALSE)) {
        $items[] = [
          'allergen_icon' => $media_params['src'],
          'allergen_label' => $allergen_term->getName(),
        ];
      }
    }

    return $items;
  }

  /**
   * Get Mobile section for Product Variant.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   Product Variant node.
   * @param string $bundle
   *   Product bundle.
   * @param string $more_information_id
   *   Id for more information section.
   *
   * @return array
   *   Mobile section array.
   */
  public function getMobileItems(EntityInterface $node, string $bundle, string $more_information_id) {
    $size_id = $node->id();
    $items = [];
    $items[] = [
      'title' => $this->languageHelper->translate($this->configuration['nutrition']['label']),
      'link_attributes' => [
        'class' => 'pdp-hero__nutrition-menu',
        'href' => '#section-nutrition--' . $size_id,
      ],
    ];

    if (
      $bundle !== 'product_multipack' &&
      $this->isAllergenVisible() &&
      !$node->get('field_product_diet_allergens')->isEmpty()
    ) {
      $items[] = [
        'title' => $this->languageHelper->translate($this->configuration['allergen_label']),
        'link_attributes' => [
          'class' => 'pdp-hero__allergen-menu',
          'href' => '#section-allergen-' . $size_id,
        ],
      ];
    }

    if ($this->configuration['more_information']['show_more_information_label'] ?? TRUE) {
      $items[] = [
        'title' => $this->languageHelper->translate($this->configuration['more_information']['more_information_label'] ?? 'More information'),
        'link_attributes' => [
          'class' => 'pdp-hero__more-info-menu',
          'href' => '#' . $more_information_id,
        ],
      ];
    }
    return $items;
  }

  /**
   * Create id for html element from title.
   *
   * @param string $string
   *   Product node title.
   *
   * @return string
   *   Machine-name string.
   */
  public function getMachineName($string = '') {
    return mb_strtolower(str_replace(' ', '', $string));
  }

  /**
   * Add page attachments.
   *
   * @param array $build
   *   Build array.
   * @param \Drupal\node\NodeInterface|null $node
   *   Product or null.
   *
   * @return array
   *   Return build.
   */
  public function pageAttachments(array &$build, NodeInterface $node = NULL) {
    if ($this->isRatingEnable($node)) {
      if (EnvironmentDetector::isProdEnv()) {
        $build['#attached']['library'][] = 'mars_product/mars_product.bazarrevoice_production';
      }
      else {
        $build['#attached']['library'][] = 'mars_product/mars_product.bazarrevoice_staging';
      }
    }

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
      $result = $this->defaultReviewState;
    }

    return $result;
  }

  /**
   * Returns the currently active commerce vendor.
   *
   * @return string
   *   The commerce vendor provider value.
   */
  private function getCommerceVendor(): string {
    return $this->wtbGlobalConfig->get('commerce_vendor') ?? self::VENDOR_NONE;
  }

  /**
   * Provides current Commerce vendor configuration.
   *
   * @param string $commerce_vendor
   *   The given commerce vendor ID.
   *
   * @return array
   *   Returns current Commerce vendor configuration or empty response.
   */
  private function getCommerceVendorInfo(string $commerce_vendor) : array {
    if ($commerce_vendor !== 'none') {
      $commerce_vendor_settings = $this->configFactory->get('mars_product.wtb.' . $commerce_vendor . '.settings');
      $commerce_vendor_settings = !$commerce_vendor_settings->isNew() ? $commerce_vendor_settings->getRawData() : [];
      return !empty($commerce_vendor_settings['settings']) ? $commerce_vendor_settings['settings'] : [];
    }
    return [];
  }

}
