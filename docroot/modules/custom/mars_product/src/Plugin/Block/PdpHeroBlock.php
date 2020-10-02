<?php

namespace Drupal\mars_product\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Header block.
 *
 * @Block(
 *   id = "pdp_hero_block",
 *   admin_label = @Translation("PDP Hero"),
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
   * Route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

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
    LanguageManagerInterface $language_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->config = $config_factory;
    $this->entityRepository = $entity_repository;
    $this->entityFormBuilder = $entity_form_builder;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->languageManager = $language_manager;
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
      $container->get('language_manager')
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

    $form['wtb']['product_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product ID'),
      '#default_value' => $this->configuration['wtb']['product_id'],
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
      '#maxlength' => 15,
      '#required' => TRUE,
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
      '#maxlength' => 50,
      '#required' => TRUE,
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
      'wtb' => [
        'commerce_vendor' => $config['wtb']['commerce_vendor'] ?? '',
        'data_widget_id' => $config['wtb']['data_widget_id'] ?? '',
        'product_id' => $config['wtb']['product_id'] ?? '',
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
      $ingredients_label = $product_variant->get('field_product_ingredients')->getFieldDefinition()->getLabel() . ':';
      $warnings_label = $product_variant->get('field_product_allergen_warnings')->getFieldDefinition()->getLabel() . ':';
    }
    $background_color = !empty($this->configuration['use_background_color']) && !empty($this->configuration['background_color']) ?
      '#' . $this->configuration['background_color'] : '';
    $pdp_common_data = [
      'hero_data' => [
        'product_label' => $this->configuration['eyebrow'] ?? '',
        'size_label' => $this->configuration['available_sizes'] ?? '',
        'brand_shape' => $this->themeConfiguratorParser->getFileContentFromTheme('brand_shape'),
        'background_color' => $background_color,
        'product_name' => $node->title->value,
        'product_description' => $node->field_product_description->value,
        'product_sku' => !empty($this->configuration['wtb']['product_id']) ? $this->configuration['wtb']['product_id'] : $product_sku,
        'commerce_vendor' => $this->configuration['wtb']['commerce_vendor'],
        'data_widget_id' => $this->configuration['wtb']['data_widget_id'] ?? '',
      ],
      'nutrition_data' => [
        'nutritional_label' => $this->configuration['nutrition']['label'] ?? '',
        'nutritional_info_serving_label' => $this->configuration['nutrition']['serving_label'] ?? '',
        'nutritional_info_daily_label' => $this->configuration['nutrition']['daily_label'] ?? '',
        'vitamins_info_label' => $this->configuration['nutrition']['vitamins_label'] . ':' ?? '',
        'ingredients_label' => $ingredients_label,
        'warnings_label' => $warnings_label,
      ],
      'allergen_data' => [
        'allergen_label' => $this->configuration['allergen_label'],
      ],
    ];
    $build['#pdp_common_data'] = $pdp_common_data;
    $build['#pdp_size_data'] = $this->getSizeData($node);
    $build['#pdp_data'] = $this->getPdpData($node);

    $build['#theme'] = 'pdp_hero_block';
    $this->pageAttachments($build);

    return $build;
  }

  /**
   * Get PDP data.
   *
   * @param object $node
   *   Product node.
   *
   * @return array
   *   PDP data array.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function getPdpData($node) {
    $items = [];
    $i = 0;
    foreach ($node->field_product_variants as $reference) {
      $product_variant = $reference->entity;
      $size_id = $product_variant->id();
      $i++;
      $state = $i == 1 ? 'true' : 'false';
      $items[] = [
        'size_id' => $size_id,
        'active' => $state,
        'hero_data' => [
          'image_items' => $this->getImageItems($product_variant),
          'mobile_sections_items' => $this->getMobileItems($product_variant),
        ],
        'nutrition_data' => [
          'serving_item' => $this->getServingItems($product_variant),
        ],
        'allergen_data' => [
          'allergens_list' => $this->getVisibleAllergenItems($product_variant),
        ],
      ];
    }

    return $items;
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
      $media = $node->{$image_field}->entity;
      $media_override = $node->{$image_field_override}->entity;
      if (!$media && !$media_override) {
        continue;
      }
      if ($media && $media_override) {
        $media = $media_override;
      }

      $file = $this->fileStorage->load($media->image->target_id);
      $image_src = $file->createFileUrl();
      $image_alt = $media->image[0]->alt;

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
      $product_variant = $reference->entity;
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
        'label' => $node->get('field_product_serving_size')->getFieldDefinition()->getLabel() . ':',
        'value' => $node->get('field_product_serving_size')->value,
      ],
      'serving_per_container' => [
        'label' => $node->get('field_product_servings_per')->getFieldDefinition()->getLabel() . ':',
        'value' => $node->get('field_product_servings_per')->value,
      ],
    ];

    $mapping = $this->getGroupingMethod($node);
    foreach ($mapping as $section => $fields) {
      foreach ($fields as $field => $field_daily) {
        $bold_modifier = array_key_exists($field, self::FIELDS_WITH_BOLD_LABELS) ? TRUE : FALSE;
        $item = [
          'label' => $node->get($field)
            ->getFieldDefinition()
            ->getLabel(),
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
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function getAllergenItems($node) {
    $items = [];
    foreach ($node->field_product_diet_allergens as $reference) {
      $allergen_term = $reference->entity;
      $icon_src = $this->getIconSrc($allergen_term);
      $items[] = [
        'allergen_icon' => $icon_src,
        'allergen_label' => $allergen_term->getName(),
      ];
    }

    return $items;
  }

  /**
   * Get Mobile section for Product Variant.
   *
   * @param object $node
   *   Product Variant node.
   *
   * @return array
   *   Mobile section array.
   */
  public function getMobileItems($node) {
    $size_id = $node->id();

    $map = [
      'section-nutrition' => $this->t('Nutrition & Ingredients'),
      'section-products' => $this->t('Related products'),
    ];
    if (
      $this->isAllergenVisible() &&
      !$node->field_product_diet_allergens->isEmpty()
    ) {
      $map['section-allergens'] = $this->t('Diet & Allergens');
    }
    $items = [];
    foreach ($map as $id => $title) {
      $items[] = [
        'title' => $title,
        'link_attributes' => [
          'href' => '#' . $id . '-' . $size_id,
        ],
      ];
    }

    return $items;
  }

  /**
   * Get Icon src from entity.
   *
   * @param object $entity
   *   Taxonomy term entity.
   * @param string $image_field
   *   Image field name.
   *
   * @return string
   *   Image src value.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function getIconSrc($entity, $image_field = 'field_allergen_image') {
    $icon_src = '';
    if (!$entity->get($image_field)->isEmpty()) {
      $icon_src = $entity->{$image_field}->entity->createFileUrl();
    }
    else {
      $field = $entity->get($image_field);
      $default_image = $field->getSetting('default_image');
      if (isset($default_image['uuid'])) {
        if ($default_image_file
          = $this->entityRepository->loadEntityByUuid('file', $default_image['uuid'])) {
          $icon_src = $default_image_file->createFileUrl();
        };
      }
    }

    return $icon_src;
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
   *
   * @return array
   *   Return build.
   */
  public function pageAttachments(array &$build) {
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
            'content' => strtolower($this->languageManager->getCurrentLanguage()->getId()),
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
    return $build;
  }

}
