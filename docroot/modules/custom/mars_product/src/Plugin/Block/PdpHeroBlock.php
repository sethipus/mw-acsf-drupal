<?php

namespace Drupal\mars_product\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
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
   * Price spider id.
   */
  const VENDOR_PRICE_SPIDER = 'price_spider';

  /**
   * Commerce connector id.
   */
  const VENDOR_COMMERCE_CONNECTOR = 'commerce_connector';

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
    ThemeConfiguratorParser $themeConfiguratorParser,
    LanguageManagerInterface $language_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->config = $config_factory;
    $this->entityRepository = $entity_repository;
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

    $form['commerce_vendor'] = [
      '#type' => 'select',
      '#title' => $this->t('Commerce Vendor'),
      '#default_value' => $this->configuration['commerce_vendor'],
      '#options' => [
        self::VENDOR_PRICE_SPIDER => $this->t('Price Spider'),
        self::VENDOR_COMMERCE_CONNECTOR => $this->t('Commerce Connector'),
      ],
      '#required' => TRUE,
    ];

    $form['data_widget_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Commerce Connector - Data widget id'),
      '#default_value' => $this->configuration['data_widget_id'],
      '#required' => TRUE,
    ];

    $form['product_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product ID'),
      '#default_value' => $this->configuration['product_id'],
    ];

    $form['wtb'] = [
      '#type' => 'details',
      '#title' => $this->t('WTB button settings'),
      '#description' => $this->t('WTB button settings'),
      '#open' => TRUE,
    ];
    $form['wtb']['cta_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA label'),
      '#default_value' => $this->configuration['wtb']['cta_label'],
    ];
    $form['wtb']['cta_link'] = [
      '#type' => 'url',
      '#title' => $this->t('CTA link'),
      '#default_value' => $this->configuration['wtb']['cta_link'] ?? '#',
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
    $form['background_color'] = [
      '#type' => 'jquery_colorpicker',
      '#title' => $this->t('Background Color Override'),
      '#default_value' => $this->configuration['background_color'] ?? '',
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
      'eyebrow' => $config['eyebrow'] ?? $this->t('Products'),
      'available_sizes' => $config['available_sizes'] ?? $this->t('Available sizes'),
      'wtb' => [
        'cta_label' => $config['wtb']['cta_label'] ?? $this->t('Where to buy'),
      ],
      'nutrition' => [
        'label' => $config['nutrition']['label'] ?? $this->t('Nutrition'),
        'serving_label' => $config['nutrition']['serving_label'] ?? $this->t('Amount per serving'),
        'daily_label' => $config['nutrition']['daily_label'] ?? $this->t('% Daily value'),
        'vitamins_label' => $config['nutrition']['vitamins_label'] ?? $this->t('Vitamins | Minerals'),
      ],
      'allergen_label' => $config['allergen_label'] ?? $this->t('Diet & Allergens'),
      'commerce_vendor' => $config['commerce_vendor'] ?? '',
      'product_id' => $config['product_id'] ?? '',
      'data_widget_id' => $config['data_widget_id'] ?? '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#eyebrow'] = $this->configuration['eyebrow'] ?? '';
    $build['#available_sizes'] = $this->configuration['available_sizes'] ?? '';
    $build['#cta_link'] = $this->configuration['wtb']['cta_link'] ?? '#';
    $build['#cta_label'] = $this->configuration['wtb']['cta_label'] ?? '';

    // Nutrition part labels.
    $build['#nutritional_label'] = $this->configuration['nutrition']['label'] ?? '';
    $build['#nutritional_info_serving_label'] = $this->configuration['nutrition']['serving_label'] ?? '';
    $build['#nutritional_info_daily_label'] = $this->configuration['nutrition']['daily_label'] ?? '';
    $build['#vitamins_info_label'] = $this->configuration['nutrition']['vitamins_label'] . ':' ?? '';

    // Get Product dependent values.
    $node = $this->getContextValue('node');
    $build['#product'] = $node;
    $build['#image_items'] = $this->getImageItems($node);
    $build['#size_items'] = $this->getSizeItems($node);
    $build['#mobile_items'] = $this->getMobileItems();
    // Nutrition part.
    $build['#serving_items'] = $this->getServingItems($node);
    // Allergen part.
    $build['#allergen_label'] = $this->configuration['allergen_label'];
    $build['#allergens_list'] = $this->getAllergenItems($node);

    // Theme settings.
    $build['#brand_shape'] = $this->themeConfiguratorParser->getFileContentFromTheme('brand_shape');
    $build['#background_color'] = $this->configuration['background_color'] ?? '';

    $build['#theme'] = 'pdp_hero_block';

    $product_sku = '';
    foreach ($node->field_product_variants as $reference) {
      $product_variant = $reference->entity;
      $product_sku = $product_variant->get('field_product_sku')->value;
    }
    $build['#product_sku'] = !empty($this->configuration['product_id']) ? $this->configuration['product_id'] : $product_sku;
    $build['#commerce_vendor'] = $this->configuration['commerce_vendor'];
    $build['#data_widget_id'] = $this->configuration['data_widget_id'] ?? '';
    $this->pageAttachments($build);

    return $build;
  }

  /**
   * Get Image items.
   *
   * @param object $node
   *   Product node.
   *
   * @return array
   *   Size items array.
   */
  public function getImageItems($node) {
    $items = [];
    $field_size = 'field_product_size';

    $map = [
      'field_product_key_image' => 'field_product_key_image_override',
      'field_product_image_1' => 'field_product_image_1_override',
      'field_product_image_2' => 'field_product_image_2_override',
      'field_product_image_3' => 'field_product_image_3_override',
      'field_product_image_4' => 'field_product_image_4_override',
    ];
    $i = 0;
    foreach ($node->field_product_variants as $reference) {
      $product_variant = $reference->entity;
      $size = $product_variant->get($field_size)->value;
      $size_id = $this->getMachineName($size);
      $i++;
      $state = $i == 1 ? 'true' : 'false';
      $items[$size_id] = [
        'active' => $state,
        'size_id' => $size_id,
      ];
      foreach ($map as $image_field => $image_field_override) {
        $media = $product_variant->{$image_field}->entity;
        $media_override = $product_variant->{$image_field_override}->entity;
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
        $item = [
          'image' => [
            'srcset' => sprintf($format, $image_src, $image_src, $image_src, $image_src),
            'src' => $image_src,
            'alt' => $image_alt,
          ],
        ];

        $items[$size_id]['images'][] = $item;
      }
    }

    return $items;
  }

  /**
   * Get Size items.
   *
   * @param object $node
   *   Product node.
   *
   * @return array
   *   Size items array.
   */
  public function getSizeItems($node) {
    $items = [];
    $field_size = 'field_product_size';
    $i = 0;
    foreach ($node->field_product_variants as $reference) {
      $product_variant = $reference->entity;
      $title = $product_variant->get($field_size)->value;
      $id = $this->getMachineName($title);
      $i++;
      $state = $i == 1 ? 'true' : 'false';
      $items[] = [
        'title' => $title,
        'link_url' => '#',
        'size_attributes' => [
          'data-size-selected' => $state,
          'data-size-id' => $id,
        ],
      ];
    }

    return $items;
  }

  /**
   * Get Serving items.
   *
   * @param object $node
   *   Product node.
   *
   * @return array
   *   Size items array.
   */
  public function getServingItems($node) {
    $mapping = [
      'nutritional_info_calories' => [
        'field_product_calories' => FALSE,
        'field_product_calories_fat' => FALSE,
      ],
      'nutritional_info_fat' => [
        'field_product_total_fat' => '',
        'field_product_saturated_fat' => 'field_product_saturated_daily',
        'field_product_trans_fat' => '',
      ],
      'nutritional_info_others' => [
        'field_product_cholesterol' => '',
        'field_product_sodium' => '',
        'field_product_carb' => '',
        'field_product_dietary_fiber' => 'field_product_dietary_daily',
        'field_product_sugars' => '',
        'field_product_protein' => '',
      ],
      'vitamins_info' => [
        'field_product_vitamin_a' => '',
        'field_product_vitamin_c' => '',
        'field_product_vitamin_d' => '',
        'field_product_calcium' => '',
        'field_product_iron' => '',
        'field_product_potassium' => '',
      ],
    ];

    $items = [];
    $field_size = 'field_product_size';
    $i = 0;
    foreach ($node->field_product_variants as $reference) {
      $product_variant = $reference->entity;
      $size = $product_variant->get($field_size)->value;
      $size_id = $this->getMachineName($size);
      $i++;
      $state = $i == 1 ? 'true' : 'false';
      $items[$size_id] = [
        'active' => $state,
        'size_id' => $size_id,
        'ingredients_label' => $product_variant->get('field_product_ingredients')->getFieldDefinition()->getLabel() . ':',
        'ingredients_value' => strip_tags(html_entity_decode($product_variant->get('field_product_ingredients')->value)),
        'warnings_label' => $product_variant->get('field_product_allergen_warnings')->getFieldDefinition()->getLabel() . ':',
        'warnings_value' => strip_tags(html_entity_decode($product_variant->get('field_product_allergen_warnings')->value)),
        'serving_size' => [
          'label' => $product_variant->get('field_product_serving_size')->getFieldDefinition()->getLabel() . ':',
          'value' => $product_variant->get('field_product_serving_size')->value,
        ],
        'serving_per_container' => [
          'label' => $product_variant->get('field_product_servings_per')->getFieldDefinition()->getLabel() . ':',
          'value' => $product_variant->get('field_product_servings_per')->value,
        ],
      ];
      foreach ($mapping as $section => $fields) {
        foreach ($fields as $field => $field_daily) {
          $item = [
            'label' => $product_variant->get($field)
              ->getFieldDefinition()
              ->getLabel(),
            'value' => $product_variant->get($field)->value,
          ];
          if ($field_daily !== FALSE) {
            $field_daily = !empty($field_daily) ? $field_daily : $field . '_daily';
            $item['value_daily']
              = $product_variant->get($field_daily)->value;
          }
          if (isset($item['value']) || isset($item['value_daily'])) {
            $items[$size_id][$section][] = $item;
          }
        }
      }
    }

    return $items;
  }

  /**
   * Get Allergen items.
   *
   * @param object $node
   *   Product node.
   *
   * @return array
   *   Size items array.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function getAllergenItems($node) {
    $items = [];
    $i = 0;
    foreach ($node->field_product_variants as $reference) {
      $product_variant = $reference->entity;
      $size = $product_variant->get('field_product_size')->value;
      $size_id = $this->getMachineName($size);
      $i++;
      $state = $i == 1 ? 'true' : 'false';

      $allergen_items = [];
      foreach ($product_variant->field_product_diet_allergens as $ref) {
        $allergen_term = $ref->entity;
        $icon_src = $this->getIconSrc($allergen_term);
        $allergen_items[] = [
          'allergen_icon' => $icon_src,
          'allergen_label' => $allergen_term->getName(),
        ];
      }
      $items[] = [
        'active' => $state,
        'size_id' => $size_id,
        'allergen_items' => $allergen_items,
      ];
    }

    return $items;
  }

  /**
   * TODO. It's a STUB. Make it configurable.
   *
   * @return array
   *   Mobile items array.
   */
  public function getMobileItems() {
    $map = [
      'section-nutrition' => 'NUTRITION & INGREDIENTS',
      'section-allergens' => 'DIET & ALLERGENS',
      'section-products' => 'RELATED PRODUCTS',
    ];
    $items = [];
    foreach ($map as $id => $title) {
      $items[] = [
        'title' => $title,
        'link_attributes' => [
          'href' => '#' . $id,
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
    if ($this->configuration['commerce_vendor'] == self::VENDOR_PRICE_SPIDER) {
      $metatags = [
        'ps-key' => [
          '#tag' => 'meta',
          '#attributes' => [
            'name' => 'ps-key',
            'content' => $this->configuration['data_widget_id'],
          ],
        ],
        'ps-country' => [
          '#tag' => 'meta',
          '#attributes' => [
            'name' => 'ps-country',
            'content' => '<' . $this->config->get('system.date')->get('country.default') . '>',
          ],
        ],
        'ps-language' => [
          '#tag' => 'meta',
          '#attributes' => [
            'name' => 'ps-language',
            'content' => '<' . strtolower($this->languageManager->getCurrentLanguage()->getId()) . '>',
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
