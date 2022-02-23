<?php

namespace Drupal\mars_product\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\ContentEntityInterface;
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
use Drupal\mars_product\Form\BazaarvoiceConfigForm;
use Drupal\mars_product\NutritionDataHelper;
use Drupal\mars_product\ProductHelper;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Path\CurrentPathStack;

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
   * Node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

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
   * Route match service for getting node
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The current path stack.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPathStack;

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
   * Nutrition table helper.
   *
   * @var \Drupal\mars_product\NutritionDataHelper
   */
  private $nutritionHelper;

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
   * WTB Manual Link selection provider id.
   */
  const VENDOR_MANUAL_LINK_SELECTION = 'manual_link_selection';

  /**
   * WTB none provider id.
   */
  const VENDOR_NONE = 'none';

  /**
   * Nutritional table US view.
   */
  const NUTRITION_VIEW_US = 'US';

  /**
   * Nutritional table UK view.
   */
  const NUTRITION_VIEW_UK = 'EU';

  /**
   * Nutritional table subgorup 1.
   */
  const NUTRITION_SUBGROUP_1 = 'group_nutritional_subgroup_1';

  /**
   * Nutritional table subgorup 2.
   */
  const NUTRITION_SUBGROUP_2 = 'group_nutritional_subgroup_2';

  /**
   * Nutritional table subgorup 3.
   */
  const NUTRITION_SUBGROUP_3 = 'group_nutritional_subgroup_3';

  /**
   * Nutritional table subgorup vitamins.
   */
  const NUTRITION_SUBGROUP_VITAMINS = 'group_vitamins';

  /**
   * Product serving size.
   */
  const PRODUCT_SERVING_SIZE = 'Serving Size';

  /**
   * Servings per container.
   */
  const SERVINGS_PER_CONTAINER = 'Servings Per Container';

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
   * Fields with bold labels.
   */
  const FIELDS_MAPPING_DAILY = [
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
    ConfigFactoryInterface $config_factory,
    NutritionDataHelper $nutrition_helper,
    RouteMatchInterface $route_match,
    CurrentPathStack $current_path
  ) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->entityRepository = $entity_repository;
    $this->entityFormBuilder = $entity_form_builder;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->languageHelper = $language_helper;
    $this->productHelper = $product_helper;
    $this->mediaHelper = $media_helper;
    $this->wtbGlobalConfig = $wtb_global_config;
    $this->defaultReviewState = $default_review_state;
    $this->configFactory = $config_factory;
    $this->nutritionHelper = $nutrition_helper;
    $this->routeMatch = $route_match;
    $this->currentPathStack = $current_path;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $config_factory = $container->get('config.factory');
    $global_wtb_config = $config_factory->get('mars_product.wtb.settings');
    $default_review_state = $config_factory
      ->get(BazaarvoiceConfigForm::SETTINGS)
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
      $container->get('config.factory'),
      $container->get('mars_product.nutrition_data_helper'),
      $container->get('current_route_match'),
      $container->get('path.current')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $commerce_vendor = $this->getCommerceVendor();
    $current_path = $this->currentPathStack->getPath();
    $path_arr = explode('/',$current_path);
    $node_str = preg_grep('/node./',$path_arr);
    $nid = str_replace('node.','',implode('',$node_str));
    if($nid){
      $node = $this->nodeStorage->load($nid);
    }

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

    $form['product'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Default product'),
      '#selection_settings' => [
        'target_bundles' => ['product'],
      ],
      '#default_value' => isset($this->configuration['product']) ? $this->nodeStorage->load($this->configuration['product']) : NULL,
    ];

    $form['wtb'] = [
      '#type' => 'fieldset',
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
      '#type' => 'fieldset',
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
    if(!empty($node) && $node->bundle() == 'product'){
      $form['nutrition']['dual_serving_label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Dual Amount per serving label'),
        '#default_value' => $this->configuration['nutrition']['dual_serving_label'],
      ];
      $form['nutrition']['table_label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Table Label'),
        '#default_value' => $this->configuration['nutrition']['table_label'],
      ];
      $form['nutrition']['dual_table_label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Dual Table Label'),
        '#default_value' => $this->configuration['nutrition']['dual_table_label'],
      ];
    }
    $form['nutrition']['daily_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Daily value label'),
      '#default_value' => $this->configuration['nutrition']['daily_label'],
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
    ];
    $form['nutrition']['other_nutrients_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Source of other nutrients text'),
      '#default_value' => $this->configuration['nutrition']['other_nutrients_text'],
    ];
    $form['nutrition']['refer_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Refer part text'),
      '#default_value' => $this->configuration['nutrition']['refer_text'],
      '#required' => TRUE,
    ];
    $benefits_enabled = !empty($this->themeConfiguratorParser->getSettingValue('show_nutrition_claims_benefits'));
    $form['nutrition']['benefits_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nutritional claims and benefits label'),
      '#default_value' => $this->configuration['nutrition']['benefits_title'],
      '#maxlength' => 55,
      '#access' => $benefits_enabled,
    ];
    $form['nutrition']['benefits_disclaimer'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Nutritional claims and benefits disclaimer'),
      '#default_value' => $this->configuration['nutrition']['benefits_disclaimer']['value'] ?? '',
      '#format' => 'rich_text',
      '#access' => $benefits_enabled,
    ];
    $form['labels'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Labels'),
      '#open' => TRUE,
    ];
    $form['labels']['allergen_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Diet & Allergens part label'),
      '#default_value' => $this->configuration['labels']['allergen_label'],
      '#maxlength' => 18,
    ];
    $form['labels']['cooking_instructions_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cooking instructions label'),
      '#default_value' => $this->configuration['labels']['cooking_instructions_label'],
      '#maxlength' => 55,
    ];
    $form['more_information'] = [
      '#type' => 'fieldset',
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
        'color_a' => 'Color A - ' . $color_a,
        'color_e' => 'Color E - ' . $color_e,
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
    $node = $this->routeMatch->getParameter('node');

    $view_type = $this->nutritionHelper
      ->getNutritionConfig()
      ->get('view_type');
    $serving_label = (isset($view_type) && $view_type == self::NUTRITION_VIEW_UK)
      ? $this->languageHelper->translate('Amount per 100g')
      : $this->languageHelper->translate('Amount per serving');
    $dual_servings_per_label = $this->nutritionHelper
      ->getNutritionConfig()
      ->get('dual_servings_per_container');
    $dual_serv_label = !empty($dual_servings_per_label) ? $this->languageHelper->translate('Amount Per Portion (51g)') : $this->languageHelper->translate('Amount per 100g');
    $dual_serving_label = (isset($view_type) && $view_type == self::NUTRITION_VIEW_UK)
      ? $dual_serv_label
      : $this->languageHelper->translate('Amount per serving');
    $daily_label = (isset($view_type) && $view_type == self::NUTRITION_VIEW_UK)
      ? '(%*)'
      : $this->languageHelper->translate('% Daily value');
    $daily_text = (isset($view_type) && $view_type == self::NUTRITION_VIEW_UK)
      ? ''
      : $this->languageHelper->translate(
        'The % Daily Value (DV) tells you how much a nutrient in a serving of food' .
        ' contributes to a daily diet. 2,000 calories a day is used for general advice.'
      );
    $other_nutrients_text = $this->languageHelper->translate(
      'Not a significant source of other nutrients.'
    );

    return [
      'label_display' => FALSE,
      'use_background_color' => $config['use_background_color'] ?? FALSE,
      'text_color' => $config['text_color'] ?? NULL,
      'brand_shape_color' => $config['brand_shape_color'] ?? NULL,
      'brand_shape_opacity' => $config['brand_shape_opacity'] ?? NULL,
      'eyebrow' => $config['eyebrow'] ?? $this->t('Products'),
      'available_sizes' => $config['available_sizes'] ?? $this->t('Available sizes'),
      'product' => !empty($config['product']) ? $this->nodeStorage->load($config['product']) : NULL,
      'nutrition' => [
        'label' => $config['nutrition']['label'] ?? $this->t('Nutrition'),
        'serving_label' => $config['nutrition']['serving_label'] ?? $serving_label,
        'dual_serving_label' => $config['nutrition']['dual_serving_label'] ?? $dual_serving_label,
        'table_label' => $config['nutrition']['table_label'] ?? '',
        'dual_table_label' => $config['nutrition']['dual_table_label'] ?? '',
        'daily_label' => $config['nutrition']['daily_label'] ?? $daily_label,
        'vitamins_label' => $config['nutrition']['vitamins_label'] ?? $this->t('Vitamins | Minerals'),
        'added_sugars_label' => $config['nutrition']['added_sugars_label'] ?? $this->languageHelper->translate('Includes'),
        'daily_text' => $config['nutrition']['daily_text'] ?? $daily_text,
        'other_nutrients_text' => $config['nutrition']['other_nutrients_text'] ?? $other_nutrients_text,
        'refer_text' => $config['nutrition']['refer_text'] ?? $this->languageHelper->translate(
            'Please refer to the product label for the most accurate nutrition, ingredient, and allergen information.'),
        'benefits_title' => $config['nutrition']['benefits_title'] ?? '',
        'benefits_disclaimer' => $config['nutrition']['benefits_disclaimer']['value'] ?? '',
      ],
      'labels' => [
        'allergen_label' => $config['labels']['allergen_label'] ?? (string) $this->t('Diet & Allergens'),
        'cooking_instructions_label' => $config['labels']['cooking_instructions_label'] ?? (string) $this->t('Cooking instructions'),
      ],
      'more_information_label' => $config['more_information']['more_information_label'] ?? (string) $this->t('More information'),
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

    // Load custom product.
    if (!empty($this->configuration['product'])) {
      $node = $this->nodeStorage->load($this->configuration['product']) ?? $node;
    }

    // Skip build process for non product entities.
    if (empty($node) || !in_array($node->bundle(), [
      'product',
      'product_multipack',
    ])) {
      return [];
    }

    // Commerce vendor info.
    $commerce_vendor = $this->getCommerceVendor();
    $commerce_vendor_settings = $this->getCommerceVendorInfo($commerce_vendor);
    // Get correct widget id field name.
    $widget_id_field = $this->productHelper->getWidgetIdField('pdp_page');
    // Get values from first Product Variant.
    $product_sku = '';
    $consumption_2 = '';
    foreach ($node->field_product_variants as $reference) {
      $product_variant = $reference->entity;
      if (!empty($product_variant)) {
        $product_sku = $this->productHelper->formatSku($product_variant->get('field_product_sku')->value);
        $consumption_2 = $this->languageHelper->translate($product_variant->get('field_product_consumption_2')->value);
      }
    }
    $view_type = $this->nutritionHelper
      ->getNutritionConfig()
      ->get('view_type');
    $dual_servings_per_label = $this->nutritionHelper
      ->getNutritionConfig()
      ->get('dual_servings_per_container');
    $dual_consumption_label = $this->nutritionHelper
      ->getNutritionConfig()
      ->get('dual_consumption_label');
    $dual_serv_label = !empty($dual_servings_per_label) ? $this->languageHelper->translate('Amount Per Portion (51g)') : $this->languageHelper->translate('Amount per 100g');
    $dual_serving_label = (isset($view_type) && $view_type == self::NUTRITION_VIEW_UK)
      ? $dual_serv_label
      : $this->languageHelper->translate('Amount per serving');
    if ($this->overrideDualTableHeading()) {
      $dual_serving_label = $dual_consumption_label ? $this->languageHelper->translate($dual_consumption_label . ' ' . $consumption_2) : $this->languageHelper->translate($consumption_2);
    }
    $background_color = !empty($this->configuration['use_background_color']) && !empty($this->configuration['background_color']) ?
      $this->configuration['background_color'] : '';
    $brand_shape_color = !empty($this->configuration['brand_shape_color']) ?
      $this->configuration['brand_shape_color'] : '';
    $more_information_id = Html::getUniqueId('section-more-information');
    $card_grid_bg_color_key = $this->configFactory->get(MarsCardColorSettingsForm::SETTINGS)->get('select_background_color_product');
    $pdp_common_data = [
      'hero_data' => [
        'product_label' => $this->languageHelper->translate($this->configuration['eyebrow'] ?? ''),
        'size_label' => $this->languageHelper->translate($this->configuration['available_sizes'] ?? ''),
        'brand_shape' => $this->themeConfiguratorParser->getBrandShapeWithoutFill(),
        'background_color' => $background_color,
        'product_name' => $node->getTitle(),
        'product_description' => $node->field_product_description->value,
        'product_sku' => !empty($this->configuration['wtb']['override_global']) && !empty($this->configuration['wtb']['product_id']) ? $this->configuration['wtb']['product_id'] : $product_sku,
        'commerce_vendor' => $commerce_vendor !== self::VENDOR_NONE ? $commerce_vendor : NULL,
        'data_widget_id' => empty($this->configuration['wtb']['override_global']) && !empty($commerce_vendor_settings[$widget_id_field]) ? $commerce_vendor_settings[$widget_id_field] : $this->configuration['wtb']['data_widget_id'],
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
        'show_nutrition_data' => $this->isNutritionDataVisible(),
        'nutritional_view_type' => $view_type,
        'nutritional_label' => $this->languageHelper->translate($this->configuration['nutrition']['label']) ?? '',
        'nutritional_info_serving_label' => $this->languageHelper->translate($this->configuration['nutrition']['serving_label']) ?? '',
        'nutritional_info_dual_serving_label' => $this->overrideDualTableHeading() ? $dual_serving_label : $this->languageHelper->translate($this->configuration['nutrition']['dual_serving_label']),
        'nutritional_info_daily_label' => $this->languageHelper->translate($this->configuration['nutrition']['daily_label']) ?? '',
        'vitamins_info_label' => $this->languageHelper->translate($this->configuration['nutrition']['vitamins_label']) . ':' ?? '',
        'daily_text' => $this->languageHelper->translate($this->configuration['nutrition']['daily_text']) ?? '',
        'other_nutrients_text' => $this->languageHelper->translate($this->configuration['nutrition']['other_nutrients_text']) ?? '',
        'refer_text' => $this->languageHelper->translate($this->configuration['nutrition']['refer_text']) ?? '',
        'benefits_title' => $this->languageHelper->translate($this->configuration['nutrition']['benefits_title']) ?? '',
        'benefits_disclaimer' => !empty($this->configuration['nutrition']['benefits_disclaimer']['value']) ? $this->languageHelper->translate($this->configuration['nutrition']['benefits_disclaimer']['value']) : '',
        'show_claims_benefits' => !empty($this->themeConfiguratorParser->getSettingValue('show_nutrition_claims_benefits')),
      ],
      'allergen_data' => [
        'allergen_label' => $this->languageHelper->translate($this->configuration['labels']['allergen_label']),
      ],
      'cooking_data' => [
        'cooking_label' => $this->languageHelper->translate($this->configuration['labels']['cooking_instructions_label']),
      ],
      'more_information_data' => [
        'more_information_label' => $this->languageHelper->translate($this->configuration['more_information']['more_information_label'] ?? 'More information'),
        'show_more_information_label' => $this->configuration['more_information']['show_more_information_label'] ?? TRUE,
        'more_information_id' => $more_information_id,
      ],
    ];
    $build['#pdp_common_data'] = $pdp_common_data;
    $build['#pdp_size_data'] = $this->getSizeData($node);
    // Sort PDP variants if there more than one item.
    if (!empty($build['#pdp_size_data']) && count($build['#pdp_size_data']) >= 2) {
      usort($build['#pdp_size_data'], function ($a, $b) {
        return intval($a['title']) > intval($b['title']);
      });
    }

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

    /** @var \Drupal\node\NodeInterface $node */
    $main_variant = $this->productHelper->mainVariant($node);

    foreach ($node->field_product_variants as $reference) {
      if (empty($reference->entity)) {
        continue;
      }
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

      $item = [
        'gtin' => $gtin,
        'size_id' => $size_id,
        'active' => $state,
        'product_description' => $product_variant->hasField('field_product_description') && !$product_variant->get('field_product_description')->isEmpty() ? $product_variant->field_product_description->value : NULL,
        'product_name' => !empty($product_variant->title->value) ? $product_variant->title->value : $node->title->value,
        'hero_data' => [
          'image_items' => $this->getImageItems($product_variant),
          'mobile_sections_items' => $this->getMobileItems($product_variant, $node->bundle(), $more_information_id),
        ],
        'nutrition_data' => [
          'claims_benefits' => $this->getNutritionClaimsBenefits($product_variant),
          'serving_item' => ($this->isNutritionDataVisible()) ? $this->getServingItems($product_variant) : NULL,
        ],
        'allergen_data' => [
          'allergens_list' => $this->getVisibleAllergenItems($product_variant),
        ],
        'cooking_data' => $this->getCookingInfo($product_variant),
        'show_rating_and_reviews' => $this->isRatingEnable($node),
        'is_main_variant' => $i === 1,
      ];
      if (!empty($product_variant->get('field_product_consumption_1')->value) || $this->showDualTable()) {
        $item['dual_nutrition_data'] = [
          'serving_item' => $this->getServingItems($product_variant, 'dual'),
        ];
        if($this->getDualServingsPerContainerLabel()){
          $item['nutrition_data']['serving_item']['table_label'] = !empty($this->configuration['nutrition']['table_label']) ? $this->languageHelper->translate($this->configuration['nutrition']['table_label']) : '';
          $item['dual_nutrition_data']['serving_item']['table_label'] = !empty($this->configuration['nutrition']['dual_table_label']) ? $this->languageHelper->translate($this->configuration['nutrition']['dual_table_label']) : '';
        }
        else{
          $item['nutrition_data']['serving_item']['table_label'] = $product_variant->get('field_product_consumption_1')->value;
          $item['dual_nutrition_data']['serving_item']['table_label'] = $product_variant->get('field_product_consumption_2')->value;
        }
      }
      $config_reference_intake = $this->configFactory->get('mars_product.nutrition_table_settings')->get('reference_intake_visibility');
      if ($config_reference_intake == '1' && !empty($item['dual_nutrition_data']['serving_item'])) {
        $item['dual_nutrition_data']['serving_item']['reference_intake_value'] = '';
      }
      elseif ($config_reference_intake == '2') {
        $item['nutrition_data']['serving_item']['reference_intake_value'] = '';
      }
      elseif ($config_reference_intake == '0') {
        $item['nutrition_data']['serving_item']['reference_intake_value'] = '';
        if (!empty($item['dual_nutrition_data']['serving_item'])) {
          $item['dual_nutrition_data']['serving_item']['reference_intake_value'] = '';
        }
      }
      if ($this->getCommerceVendor() == self::VENDOR_MANUAL_LINK_SELECTION && !$product_variant->get('field_product_hide_wtb_link')->value) {
        $item['wtb_manual_link_info'] = $this->getManualLinkInfo($product_variant);
      }
      elseif ($this->getCommerceVendor() == self::VENDOR_PRICE_SPIDER && !$product_variant->get('field_product_hide_wtb_link')->value) {
        $item['price_spider_link_info'] = $this->getManualPriceSpiderLinkInfo($product_variant);
      }
      $items[] = $item;
    }

    return $items;
  }

  /**
   * Get WTB manual link attributes.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $product_variant
   *   Product variant node.
   *
   * @return array
   *   Manual link attributes.
   */
  private function getManualLinkInfo(ContentEntityInterface $product_variant): array {
    $global_config = $this->getCommerceVendorInfo(self::VENDOR_MANUAL_LINK_SELECTION);
    return [
      'button_name' => $product_variant->get('field_product_wtb_override')->value ? $product_variant->get('field_product_wtb_button_name')->value : $this->languageHelper->translate($global_config['button_name']),
      'button_url' => $product_variant->get('field_product_wtb_override')->value ? $product_variant->get('field_product_wtb_button_url')->value : $this->languageHelper->translate($global_config['button_url']),
      'button_new_tab' => $product_variant->get('field_product_wtb_override')->value ? $product_variant->get('field_product_wtb_new_tab')->value : $global_config['button_new_tab'],
      'button_style' => $product_variant->get('field_product_wtb_override')->value ? $product_variant->get('field_product_wtb_button_style')->value : $global_config['button_style'],
    ];
  }

  /**
   * Get PriceSpider WTB manual link attributes.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $product_variant
   *   Product variant node.
   *
   * @return array
   *   Manual link attributes.
   */
  private function getManualPriceSpiderLinkInfo(ContentEntityInterface $product_variant): array {
    $global_config = $this->getCommerceVendorInfo(self::VENDOR_PRICE_SPIDER);
    return [
      'option' => isset($global_config['option']) ? $global_config['option'] : FALSE,
      'price_spider_button_name' => isset($global_config['price_spider_button_name']) ? $this->languageHelper->translate($global_config['price_spider_button_name']) : '',
      'price_spider_button_url' => isset($global_config['price_spider_button_url']) ? $this->languageHelper->translate($global_config['price_spider_button_url']) . '?ps-sku=' . $this->productHelper->formatSku($product_variant->get('field_product_sku')->value) : '',
    ];
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
      $serving_items = ($this->isNutritionDataVisible())
        ? $this->getServingItems($product_variant_first)
        : [];

      $item = [
        'product_title' => $product->getTitle(),
        'product_image' => $this->getProductVariantImage($product_variant_first),
        'nutrition_data' => [
          'claims_benefits' => $this->getNutritionClaimsBenefits($product_variant_first),
          'serving_item' => $serving_items,
          'serving_item_empty' => $this->isServingItemsEmpty($serving_items),
        ],
        'allergen_data' => [
          'allergens_list' => $this->getVisibleAllergenItems($product_variant_first),
        ],
      ];
      if (!empty($product_variant_first->get('field_product_consumption_1')->value)) {
        $item['dual_nutrition_data'] = [
          'serving_item' => $this->getServingItems($product_variant_first, 'dual'),
        ];
        $item['nutrition_data']['serving_item']['table_label'] = $product_variant_first->get('field_product_consumption_1')->value;
        $item['dual_nutrition_data']['serving_item']['table_label'] = $product_variant_first->get('field_product_consumption_2')->value;
      }
      $products_data[] = $item;
    }

    $items = [];
    $i = 0;
    foreach ($node->field_product_variants as $reference) {
      $product_variant = $this->languageHelper->getTranslation($reference->entity);
      $size_id = $product_variant->id();
      $i++;
      $state = $i == 1 ? 'true' : 'false';
      $gtin = $product_variant->get('field_product_sku')->value;
      $item = [
        'gtin' => !empty($this->configuration['wtb']['product_id']) ? $this->configuration['wtb']['product_id'] : $gtin,
        'size_id' => $size_id,
        'active' => $state,
        'hero_data' => [
          'image_items' => $this->getImageItems($product_variant),
          'mobile_sections_items' => $this->getMobileItems($product_variant, $node->bundle(), $more_information_id),
        ],
        'products'  => $products_data,
        'cooking_data' => $this->getCookingInfo($product_variant),
      ];
      if ($this->getCommerceVendor() == self::VENDOR_MANUAL_LINK_SELECTION && !$product_variant->get('field_product_hide_wtb_link')->value) {
        $item['wtb_manual_link_info'] = $this->getManualLinkInfo($product_variant);
      }
      elseif ($this->getCommerceVendor() == self::VENDOR_PRICE_SPIDER && !$product_variant->get('field_product_hide_wtb_link')->value) {
        $item['price_spider_link_info'] = $this->getManualPriceSpiderLinkInfo($product_variant);
      }
      $items[] = $item;
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
      if (empty($product_variant)) {
        continue;
      }
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
   * @param string $field_prefix
   *   Prefix for the field name. Dual or product.
   *
   * @return array
   *   Serving items array.
   */
  public function getServingItems($node, string $field_prefix = 'product') {
    $result_item = [
      'ingredients_value' => strip_tags(html_entity_decode($node->get('field_' . $field_prefix . '_ingredients')->value), '<strong><b><br>'),
      'warnings_value' => strip_tags(html_entity_decode($node->get('field_' . $field_prefix . '_allergen_warnings')->value)),
      'legal_warnings_value' => strip_tags(html_entity_decode($node->get('field_' . $field_prefix . '_legal_warnings')->value)),
      'hide_dialy_value_column' => TRUE,
      'show_other_nutrients_text' => $this->showOtherNutrientsText($node, $field_prefix),
    ];
    if ($field_prefix == 'product') {
      $serving_size = [
        'label' => $this->getProductServingSizeLabel($node),
        'value' => $node->get('field_product_serving_size')->value,
      ];
      $serving_per_container = [
        'label' => $this->getServingsPerContainerLabel($node),
        'value' => $node->get('field_product_servings_per')->value,
      ];
      $result_item['serving_size'] = $this->hideServingSizeHeading() ? [] : $serving_size;
      $result_item['serving_per_container'] = $this->hideServingsPerHeading() ? [] : $serving_per_container;
      $result_item['disclaimers_value'] = strip_tags(html_entity_decode($node->get('field_product_disclaimers')->value), '<strong><b>');
    }
    elseif ($field_prefix == 'dual') {
      $result_item['dual_servings_per_container'] = [
        'label' => $this->getDualServingsPerContainerLabel($node),
        'value' => $node->get('field_dual_servings_per')->value,
      ];
    }

    if ($node->hasField('field_product_reference_intake')) {
      $result_item['reference_intake_value'] = strip_tags(html_entity_decode($node->get('field_product_reference_intake')->value), '<strong><b>');
    }

    $mapping = $this->nutritionHelper
      ->getMapping($field_prefix);

    $unsorted_result = [];
    foreach ($mapping as $section => $fields) {
      foreach ($fields as $field => $field_data) {
        $bold_modifier = (bool) $field_data['bold'];
        $item = [
          'label' => $this->languageHelper->translate($field_data['label']),
          'value' => $node->get($field)->value,
          'bold_modifier' => $bold_modifier,
          'weight' => $field_data['weight'],
        ];
        if ($field_data['daily_field'] !== 'none') {
          $result_item['hide_dialy_value_column'] = FALSE;
          $item['value_daily']
            = $node->get($field_data['daily_field'])->value;
        }
        if ($field === 'field_' . $field_prefix . '_added_sugars') {
          $item['pre_label'] = $this->languageHelper->translate(
              $this->configuration['nutrition']['added_sugars_label']) ?? '';
        }
        if (isset($item['value']) || isset($item['value_daily'])) {
          $unsorted_result[$section][] = $item;
        }
      }
    }

    foreach ($unsorted_result as $section => $section_data) {
      $result_item[$section] = $this->nutritionHelper
        ->sortFields($section_data);
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
      empty($serving_items['reference_intake_value'] ?? NULL) &&
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
   * Get theme setting for Allergen info visibility.
   *
   * @return bool
   *   Allergen visibility
   */
  public function isAllergenVisible(): bool {
    $show_allergen_info = $this->themeConfiguratorParser->getSettingValue('show_allergen_info');
    if ($show_allergen_info) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get theme setting for Cooking info visibility.
   *
   * @return bool
   *   Cooking info visibility
   */
  public function isCookingInfoVisible(): bool {
    $show_cooking_info = $this->themeConfiguratorParser->getSettingValue('show_cooking_info');
    if ($show_cooking_info) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get theme setting for Nutrition info visibility.
   *
   * @return bool
   *   Nutrition info visibility
   */
  public function isNutritionDataVisible(): bool {
    $show_nutrition_data = $this->themeConfiguratorParser->getSettingValue('show_nutrition_info');
    if ($show_nutrition_data || $show_nutrition_data === '') {
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
   */
  public function getVisibleAllergenItems($node) {
    if ($this->isAllergenVisible()) {
      return $this->getAllergenItems($node);
    }
    return [];
  }

  /**
   * Get all visible allergen items.
   *
   * @param object $node
   *   Product Variant node.
   *
   * @return null|string
   *   Cooking info.
   */
  public function getCookingInfo($node) {
    return ($this->isCookingInfoVisible())
      ? $node->get('field_product_cooking_instruct')->value
      : NULL;
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

    if ($this->isNutritionDataVisible()) {
      $items[] = [
        'title' => $this->languageHelper->translate($this->configuration['nutrition']['label']),
        'link_attributes' => [
          'class' => 'pdp-hero__nutrition-menu',
          'href' => '#section-nutrition--' . $size_id,
        ],
      ];
    }

    if (
      $bundle !== 'product_multipack' &&
      $this->isAllergenVisible() &&
      !$node->get('field_product_diet_allergens')->isEmpty()
    ) {
      $items[] = [
        'title' => $this->languageHelper->translate($this->configuration['labels']['allergen_label']),
        'link_attributes' => [
          'class' => 'pdp-hero__allergen-menu',
          'href' => '#section-allergen-' . $size_id,
        ],
      ];
    }

    if (
      $this->isCookingInfoVisible() &&
      !$node->get('field_product_cooking_instruct')->isEmpty()
    ) {
      $items[] = [
        'title' => $this->languageHelper->translate($this->configuration['labels']['cooking_instructions_label']),
        'link_attributes' => [
          'class' => 'pdp-hero__cooking-menu',
          'href' => '#section-cooking-' . $size_id,
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
      $build['#attached']['library'][] = 'mars_product/mars_product.bazaarvoice';
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

  /**
   * Get Nutrition Claims and Benefits items.
   *
   * @param object $variant_node
   *   Product Variant node.
   *
   * @return array
   *   Nutrition Claims and Benefits items array.
   */
  public function getNutritionClaimsBenefits(object $variant_node): array {
    if (!empty($variant_node)) {
      if ($variant_node->hasField('field_nutritional_claims_benefit') && !$variant_node->get('field_nutritional_claims_benefit')->isEmpty()) {
        $benefits_to_render = [];
        $benefit_items = $variant_node->get('field_nutritional_claims_benefit')->getValue();
        foreach ($benefit_items as $item) {
          $benefits_to_render[] = $this->languageHelper->translate($item['value']);
        }
        return $benefits_to_render;
      }
      return [];
    }
    return [];
  }

  /**
   * Get Product serving size.
   *
   * @return string
   *   Product serving size string.
   */
  private function getProductServingSizeLabel($node): string {
    $serv_sz = $this->configFactory->get('mars_product.nutrition_table_settings')->get('product_serving_size');
    $product_serving_size = !empty($serv_sz) ? $this->languageHelper->translate($serv_sz) : $this->languageHelper->translate($node->get('field_product_serving_size')->getFieldDefinition()->getLabel()) . ':';
    return $product_serving_size;
  }

  /**
   * Get Servings per container.
   *
   * @return string
   *   Serving per container string.
   */
  private function getServingsPerContainerLabel($node): string {
    $serv_per_cont = $this->configFactory->get('mars_product.nutrition_table_settings')->get('servings_per_container');
    $servings_per_container = !empty($serv_per_cont) ? $this->languageHelper->translate($serv_per_cont) : $this->languageHelper->translate($node->get('field_product_servings_per')->getFieldDefinition()->getLabel()) . ':';
    return $servings_per_container;
  }

  /**
   * Get Dual Servings per container.
   *
   * @return string
   *   Dual Serving per container string.
   */
  private function getDualServingsPerContainerLabel(): string {
    $dual_serv_per_cont = $this->configFactory->get('mars_product.nutrition_table_settings')->get('dual_servings_per_container');
    $dual_servings_per_container = !empty($dual_serv_per_cont) ? $this->languageHelper->translate($dual_serv_per_cont) : '';
    return $dual_servings_per_container;
  }

  /**
   * Check for should be text rendered or not.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Product entity.
   * @param string $field_prefix
   *   Field prefix.
   *
   * @return bool
   *   Whether it should be rendered or not.
   */
  private function showOtherNutrientsText(NodeInterface $node, string $field_prefix): bool {
    $show_other_nutrients_text = $this->configFactory
      ->get('mars_product.nutrition_table_settings')
      ->get('show_other_nutrients_text');

    $sugar_alc = $node->get('field_' . $field_prefix . '_sugar_alcohol')->value;

    return ($show_other_nutrients_text && isset($sugar_alc) && !empty($sugar_alc));
  }

  /**
   * Check for dual nutrition table to be shown or not.
   *
   * @return bool
   *   Whether it should be rendered or not.
   */
  private function showDualTable(): bool {
    $show_dual_table = $this->configFactory
      ->get('mars_product.nutrition_table_settings')
      ->get('show_dual_table');

    return is_null($show_dual_table) ? TRUE : (isset($show_dual_table) && !empty($show_dual_table));
  }

  /**
   * Check for dual nutrition table heading to be overridden or not.
   *
   * @return bool
   *   Whether it should be rendered or not.
   */
  private function overrideDualTableHeading(): bool {
    $override_dual_table_heading = $this->configFactory
      ->get('mars_product.nutrition_table_settings')
      ->get('override_dual_table_heading');

    return (isset($override_dual_table_heading) && !empty($override_dual_table_heading));
  }

  /**
   * Check for serving size heading to be shown or not in the nutrition table 1.
   *
   * @return bool
   *   Whether it should be rendered or not.
   */
  private function hideServingSizeHeading(): bool {
    $hide_serving_size_heading = $this->configFactory
      ->get('mars_product.nutrition_table_settings')
      ->get('hide_serving_size_heading');

    return (isset($hide_serving_size_heading) && !empty($hide_serving_size_heading));
  }

    /**
   * Check for servings per heading to be shown or not in the nutrition table 1.
   *
   * @return bool
   *   Whether it should be rendered or not.
   */
  private function hideServingsPerHeading(): bool {
    $hide_servings_per_heading = $this->configFactory
      ->get('mars_product.nutrition_table_settings')
      ->get('hide_servings_per_heading');

    return (isset($hide_servings_per_heading) && !empty($hide_servings_per_heading));
  }

}
