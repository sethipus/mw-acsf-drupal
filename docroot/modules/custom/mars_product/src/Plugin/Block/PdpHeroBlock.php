<?php

namespace Drupal\mars_product\Plugin\Block;

use Acquia\Blt\Robo\Common\EnvironmentDetector;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\MediaHelper;
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
   * Helper service to deal with media.
   *
   * @var \Drupal\mars_common\MediaHelper
   */
  private $mediaHelper;

  /**
   * Product helper service.
   *
   * @var \Drupal\mars_product\ProductHelper
   */
  private $productHelper;

  /**
   * Price spider id.
   */
  const VENDOR_PRICE_SPIDER = 'price_spider';

  /**
   * Commerce connector id.
   */
  const VENDOR_COMMERCE_CONNECTOR = 'commerce_connector';

  /**
   * Fields with bold labels.
   */
  const FIELDS_WITH_BOLD_LABELS = [
    'field_product_saturated_fat' => 'Saturated Fat',
    'field_product_trans_fat' => 'Trans Fat',
    'field_product_sugars' => 'Sugars',
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
    ConfigFactoryInterface $config_factory,
    EntityRepositoryInterface $entity_repository,
    EntityFormBuilderInterface $entity_form_builder,
    ThemeConfiguratorParser $themeConfiguratorParser,
    LanguageHelper $language_helper,
    ProductHelper $product_helper,
    MediaHelper $media_helper
  ) {
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->config = $config_factory;
    $this->entityRepository = $entity_repository;
    $this->entityFormBuilder = $entity_form_builder;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->languageHelper = $language_helper;
    $this->productHelper = $product_helper;
    $this->mediaHelper = $media_helper;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('entity.repository'),
      $container->get('entity.form_builder'),
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('mars_common.language_helper'),
      $container->get('mars_product.product_helper'),
      $container->get('mars_common.media_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

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
      '#open' => TRUE,
    ];

    $form['wtb']['commerce_vendor'] = [
      '#type' => 'select',
      '#title' => $this->t('Commerce Vendor'),
      '#default_value' => $this->configuration['wtb']['commerce_vendor'],
      '#options' => [
        self::VENDOR_PRICE_SPIDER => $this->t('Price Spider'),
        self::VENDOR_COMMERCE_CONNECTOR => $this->t('Commerce Connector'),
      ],
      '#required' => TRUE,
    ];

    $form['wtb']['data_widget_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Widget id'),
      '#default_value' => $this->configuration['wtb']['data_widget_id'],
      '#required' => TRUE,
    ];

    $form['wtb']['data_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token'),
      '#default_value' => $this->configuration['wtb']['data_token'],
      '#states' => [
        'visible' => [
          [':input[name="settings[wtb][commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
        'required' => [
          [':input[name="settings[wtb][commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

    $form['wtb']['data_subid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SubId'),
      '#default_value' => $this->configuration['wtb']['data_subid'],
      '#states' => [
        'visible' => [
          [':input[name="settings[wtb][commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

    $form['wtb']['product_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product ID'),
      '#default_value' => $this->configuration['wtb']['product_id'],
    ];

    $form['wtb']['cta_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA title'),
      '#default_value' => $this->configuration['wtb']['cta_title'],
    ];

    $form['wtb']['button_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Commerce Connector: button type'),
      '#default_value' => $this->configuration['wtb']['button_type'],
      '#options' => [
        'my_own' => $this->t('My own button'),
        'commerce_connector' => $this->t('Commerce Connector button'),
      ],
      '#states' => [
        'visible' => [
          [':input[name="settings[wtb][commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

    $form['wtb']['data_locale'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Commerce Connector: data locale'),
      '#default_value' => $this->configuration['wtb']['data_locale'],
      '#states' => [
        'visible' => [
          [':input[name="settings[wtb][commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
        'required' => [
          [':input[name="settings[wtb][commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

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
    $this->setConfiguration($form_state->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = $this->getConfiguration();
    $wtb_global = $this->config->get('mars_product.wtb.settings');

    return [
      'label_display' => FALSE,
      'use_background_color' => $config['use_background_color'] ?? FALSE,
      'eyebrow' => $config['eyebrow'] ?? $this->t('Products'),
      'available_sizes' => $config['available_sizes'] ?? $this->t('Available sizes'),
      'nutrition' => [
        'label' => $config['nutrition']['label'] ?? $this->t('Nutrition'),
        'serving_label' => $config['nutrition']['serving_label'] ?? $this->t('Amount per serving'),
        'daily_label' => $config['nutrition']['daily_label'] ?? $this->t('% Daily value'),
        'vitamins_label' => $config['nutrition']['vitamins_label'] ?? $this->t('Vitamins | Minerals'),
      ],
      'allergen_label' => $config['allergen_label'] ?? $this->t('Diet & Allergens'),
      'more_information_label' => $config['more_information']['more_information_label'] ?? $this->t('More information'),
      'show_more_information_label' => $config['more_information']['show_more_information_label'] ?? TRUE,
      'wtb' => [
        'commerce_vendor' => $config['wtb']['commerce_vendor'] ?? $wtb_global->get('commerce_vendor') ?? self::VENDOR_COMMERCE_CONNECTOR,
        'data_widget_id' => $config['wtb']['data_widget_id'] ?? $wtb_global->get('widget_id') ?? NULL,
        'data_token' => $config['wtb']['data_token'] ?? $wtb_global->get('data_token') ?? NULL,
        'data_subid' => $config['wtb']['data_subid'] ?? $wtb_global->get('data_subid') ?? NULL,
        'cta_title' => $config['wtb']['cta_title'] ?? $wtb_global->get('cta_title') ?? NULL,
        'product_id' => $config['wtb']['product_id'] ?? NULL,
        'button_type' => $config['wtb']['button_type'] ?? $wtb_global->get('button_type') ?? NULL,
        'data_locale' => $config['wtb']['data_locale'] ?? $wtb_global->get('data_locale') ?? NULL,
      ],

    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Product node.
    $node = $this->getContextValue('node');
    // Get values from first Product Variant.
    $product_sku = '';
    $ingredients_label = '';
    $warnings_label = '';
    foreach ($node->field_product_variants as $reference) {
      $product_variant = $reference->entity;
      $product_sku = $product_variant->get('field_product_sku')->value;
      $ingredients_label = $this->languageHelper->translate($product_variant->get('field_product_ingredients')->getFieldDefinition()->getLabel()) . ':';
      $warnings_label = $this->languageHelper->translate($product_variant->get('field_product_allergen_warnings')->getFieldDefinition()->getLabel()) . ':';
    }
    $background_color = !empty($this->configuration['use_background_color']) && !empty($this->configuration['background_color']) ?
      '#' . $this->configuration['background_color'] : '';
    $pdp_common_data = [
      'hero_data' => [
        'product_label' => $this->languageHelper->translate($this->configuration['eyebrow'] ?? ''),
        'size_label' => $this->languageHelper->translate($this->configuration['available_sizes'] ?? ''),
        'brand_shape' => $this->themeConfiguratorParser->getBrandShapeWithoutFill(),
        'background_color' => $background_color,
        'product_name' => $node->title->value,
        'product_description' => $node->field_product_description->value,
        'product_sku' => !empty($this->configuration['wtb']['product_id']) ? $this->configuration['wtb']['product_id'] : $product_sku,
        'commerce_vendor' => $this->configuration['wtb']['commerce_vendor'],
        'data_widget_id' => $this->configuration['wtb']['data_widget_id'] ?? '',
        'data_token' => $this->configuration['wtb']['data_token'] ?? '',
        'data_subid' => $this->configuration['wtb']['data_subid'] ?? '',
        'product_CTA_title' => $this->configuration['wtb']['cta_title'] ?? '',
        'button_type' => $this->configuration['wtb']['button_type'] ?? '',
        'data_locale' => $this->configuration['wtb']['data_locale'] ?? '',
      ],
      'nutrition_data' => [
        'nutritional_label' => $this->languageHelper->translate($this->configuration['nutrition']['label']) ?? '',
        'nutritional_info_serving_label' => $this->languageHelper->translate($this->configuration['nutrition']['serving_label']) ?? '',
        'nutritional_info_daily_label' => $this->languageHelper->translate($this->configuration['nutrition']['daily_label']) ?? '',
        'vitamins_info_label' => $this->languageHelper->translate($this->configuration['nutrition']['vitamins_label']) . ':' ?? '',
        'ingredients_label' => $ingredients_label,
        'warnings_label' => $warnings_label,
      ],
      'allergen_data' => [
        'allergen_label' => $this->languageHelper->translate($this->configuration['allergen_label']),
      ],
      'more_information_data' => [
        'more_information_label' => $this->languageHelper->translate($this->configuration['more_information']['more_information_label'] ?? 'More information'),
        'show_more_information_label' => $this->configuration['more_information']['show_more_information_label'] ?? TRUE,
      ],
    ];
    $build['#pdp_common_data'] = $pdp_common_data;
    $build['#pdp_size_data'] = $this->getSizeData($node);

    $node_bundle = $node->bundle();
    $build['#pdp_bundle_type'] = $node_bundle;
    switch ($node_bundle) {
      case 'product_multipack':
        $build['#pdp_data'] = $this->getPdpMultiPackProductData($node);
        break;

      case 'product':
        $build['#pdp_data'] = $this->getPdpSingleProductData($node);
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
   *
   * @return array
   *   PDP data array.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function getPdpSingleProductData($node) {
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
          'mobile_sections_items' => $this->getMobileItems($product_variant, $node->bundle()),
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
   *
   * @return array
   *   PDP data array.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function getPdpMultiPackProductData(EntityInterface $node) {
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
          'mobile_sections_items' => $this->getMobileItems($product_variant, $node->bundle()),
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
      'field_product_protein' => '',
      'field_product_vitamin_a' => '',
      'field_product_vitamin_c' => '',
      'field_product_vitamin_d' => '',
      'field_product_calcium' => '',
      'field_product_iron' => '',
      'field_product_potassium' => '',
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
   *
   * @return array
   *   Mobile section array.
   */
  public function getMobileItems(EntityInterface $node, string $bundle) {
    $size_id = $node->id();
    $items = [];
    $items[] = [
      'title' => $this->languageHelper->translate($this->configuration['nutrition']['label']),
      'link_attributes' => [
        'class' => 'pdp-hero__nutrition-menu',
        'href' => '#section-nutrition-' . $size_id,
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
          'href' => '#section-more-information',
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
    if ($this->configuration['wtb']['commerce_vendor'] == self::VENDOR_PRICE_SPIDER) {
      $metatags = [
        'ps-key' => [
          '#tag' => 'meta',
          '#attributes' => [
            'name' => 'ps-key',
            'content' => $this->configuration['wtb']['data_widget_id'],
          ],
        ],
        'ps-country' => [
          '#tag' => 'meta',
          '#attributes' => [
            'name' => 'ps-country',
            'content' => $this->config->get('system.date')->get('country.default'),
          ],
        ],
        'ps-language' => [
          '#tag' => 'meta',
          '#attributes' => [
            'name' => 'ps-language',
            'content' => strtolower($this->languageHelper->getCurrentLanguageId()),
          ],
        ],
        'price-spider' => [
          '#tag' => 'script',
          '#attributes' => [
            'src' => '//cdn.pricespider.com/1/lib/ps-widget.js',
            'async' => TRUE,
          ],
        ],
      ];
      foreach ($metatags as $key => $metatag) {
        $build['#attached']['html_head'][] = [$metatag, $key];
      }
    }
    elseif ($this->configuration['wtb']['commerce_vendor'] == self::VENDOR_COMMERCE_CONNECTOR) {
      $locale = $this->languageHelper->getCurrentLanguageId();
      $build['#attached']['drupalSettings']['cc'] = [
        'data-token' => $this->configuration['wtb']['data_token'],
        'data-locale' => $this->configuration['wtb']['data_locale'],
        'data-displaylanguage' => $locale,
        'data-widgetid' => $this->configuration['wtb']['data_widget_id'],
        'data-subid' => $this->configuration['wtb']['data_subid'] ?? NULL,
      ];
      $build['#attached']['library'][] = 'mars_product/mars_product.commerce_connector';
    }

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
      $result = $this->config->get('emulsifymars.settings')->get('show_rating_and_reviews');
    }

    return $result;
  }

}
