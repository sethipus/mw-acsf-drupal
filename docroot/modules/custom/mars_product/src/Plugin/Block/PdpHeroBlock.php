<?php

namespace Drupal\mars_product\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
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
 *     "node" = @ContextDefinition("entity:node", label =
 *   @Translation("Product"))
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
    ConfigFactoryInterface $config_factory,
    ThemeConfiguratorParser $themeConfiguratorParser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->config = $config_factory;
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
      $container->get('config.factory'),
      $container->get('mars_common.theme_configurator_parser')
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
    $build['#wtb_border_radius'] = $this->themeConfiguratorParser->getSettingValue('button_style');

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

    $build['#theme'] = 'pdp_hero_block';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $access = $this->getContextValue('node')->bundle() == 'product';
    return AccessResult::allowedIf($access);
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
      for ($i = 1; $i <= 4; $i++) {
        $field_name = 'field_product_image_' . $i;
        $field_name_override = 'field_product_image_' . $i . '_override';

        $image = $product_variant->{$field_name}->entity;
        $image_override = $product_variant->{$field_name_override}->entity;

        if (!$image && !$image_override) {
          continue;
        }
        if ($image && $image_override) {
          $image = $image_override;
        }

        $file = $this->fileStorage->load($image->id());
        $image_src = $file->createFileUrl();
        $image_alt = $image->image[0]->alt;

        // TODO how srcet should be defined on Back side.
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
          'data-size-id' => $id ,
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
        $allergen_items[] = [
          'allergen_icon' => $allergen_term->field_allergen_image->entity->createFileUrl(),
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
        'border_radius' => $this->themeConfiguratorParser->getSettingValue('button_style'),
        'title' => $title,
        'link_attributes' => [
          'href' => '#' . $id,
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

}
