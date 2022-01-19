<?php

namespace Drupal\mars_product\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_product\NutritionDataHelper;
use Drupal\mars_product\Plugin\Block\PdpHeroBlock;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Nutrition table config form class.
 */
class NutritionConfigForm extends ConfigFormBase {

  /**
   * The entity type manager interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The form builder interface.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilder
   */
  protected $entityFormBuilder;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * Nutrition data helper service.
   *
   * @var \Drupal\mars_product\NutritionDataHelper
   */
  private $nutritionHelper;

  /**
   * NutritionConfigForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager interface.
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $form_builder
   *   The form builder interface.
   * @param \Drupal\mars_common\LanguageHelper $language_helper
   *   The language helper service.
   * @param \Drupal\mars_product\NutritionDataHelper $nutrition_helper
   *   The nutrition data helper service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    EntityFormBuilderInterface $form_builder,
    LanguageHelper $language_helper,
    NutritionDataHelper $nutrition_helper
  ) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFormBuilder = $form_builder;
    $this->languageHelper = $language_helper;
    $this->nutritionHelper = $nutrition_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity.form_builder'),
      $container->get('mars_common.language_helper'),
      $container->get('mars_product.nutrition_data_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nutrition_table_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('mars_product.nutrition_table_settings');

    $form['general'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('General configuration'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    $form['general']['view_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Nutritional table view'),
      '#default_value' => $config->get('view_type') ?? PdpHeroBlock::NUTRITION_VIEW_US,
      '#options' => [
        PdpHeroBlock::NUTRITION_VIEW_US => $this->t('US'),
        PdpHeroBlock::NUTRITION_VIEW_UK => $this->t('EU'),
      ],
      '#required' => TRUE,
    ];

    $form['general']['set_to_default_desciption'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('Set to default state depending on nutritional table view:') .
      '</p>',
    ];
    $form['general']['set_to_default'] = [
      '#type' => 'submit',
      '#name' => 'set_to_default',
      '#value' => $this->t('Set to selected state'),
      '#limit_validation_errors' => [],
      '#button_type' => 'danger',
    ];
    $form['general']['other_general_configuration'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('Other:') . '</p>',
    ];
    $form['general']['show_other_nutrients_text'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show "other nutrients" text'),
      '#default_value' => $config->get('show_other_nutrients_text') ?? FALSE,
    ];

    $this->getGroupTableHeader($form, $form_state);
    $this->getDualGroupTableHeader($form, $form_state);
    $this->getSubgroupTable($form, $form_state, PdpHeroBlock::NUTRITION_SUBGROUP_1);
    $this->getSubgroupTable($form, $form_state, PdpHeroBlock::NUTRITION_SUBGROUP_2);
    $this->getSubgroupTable($form, $form_state, PdpHeroBlock::NUTRITION_SUBGROUP_3);
    $this->getSubgroupTable($form, $form_state, PdpHeroBlock::NUTRITION_SUBGROUP_VITAMINS);

    return $form;
  }

  /**
   * Get Group Table Header Configuration.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getGroupTableHeader(
    array &$form,
    FormStateInterface $form_state) {
    $config = $this->config('mars_product.nutrition_table_settings');
    $form['header'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Group Header configuration'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    $form['header']['product_serving_size'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product serving size label'),
      '#default_value' => !empty($config->get('product_serving_size')) ? $this->languageHelper->translate($config->get('product_serving_size')) : $this->languageHelper->translate(PdpHeroBlock::PRODUCT_SERVING_SIZE),
    ];
    $form['header']['servings_per_container'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Servings per container label'),
      '#default_value' => !empty($config->get('servings_per_container')) ? $this->languageHelper->translate($config->get('servings_per_container')) : $this->languageHelper->translate(PdpHeroBlock::SERVINGS_PER_CONTAINER),
    ];
    $form['header']['hide_table_heading'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide Table Heading'),
      '#default_value' => $config->get('hide_table_heading') ?? FALSE,
      '#attributes' => [
        'title' => $this->t("This field will hide table heading in the Nutrition Table 1."),
      ],
    ];

  }

  /**
   * Get Dual Group Table Header Configuration.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getDualGroupTableHeader(
    array &$form,
    FormStateInterface $form_state) {
    $config = $this->config('mars_product.nutrition_table_settings');
    $form['dual_header'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Dual Group Header configuration'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    $form['dual_header']['dual_servings_per_container'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Dual Servings per container label'),
      '#default_value' => !empty($config->get('dual_servings_per_container')) ? $this->languageHelper->translate($config->get('dual_servings_per_container')) : '',
    ];
    $form['dual_header']['show_dual_table'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show Dual Nutritional Table'),
      '#default_value' => is_null($config->get('show_dual_table')) ? TRUE : $config->get('show_dual_table'),
    ];
    $form['dual_header']['override_dual_table_heading'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Override Dual Table Heading'),
      '#default_value' => !empty($config->get('override_dual_table_heading')) ? $config->get('override_dual_table_heading') : FALSE,
      '#attributes' => [
        'title' => $this->t("This field will override the Per portion value in the Nutrition Table 2 with that of Salsify value."),
      ]
    ];

  }

  /**
   * Get mapping table for subgroups.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   * @param string $group_key
   *   Subgroup key.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getSubgroupTable(
    array &$form,
    FormStateInterface $form_state,
    string $group_key) {
    $config = $this->getConfig($group_key);

    $items_settings = !empty($config) ? $config : [];
    $items_storage = $form_state->get($group_key . '_storage');
    if (!isset($items_storage)) {
      if (!empty($items_settings)) {
        $items_storage = array_keys($items_settings);
      }
      else {
        $items_storage = [];
      }
      $form_state->set($group_key . '_storage', $items_storage);
    }

    $triggered = $form_state->getTriggeringElement();
    if (isset($triggered['#parents'][0]) && $triggered['#parents'][0] == $group_key &&
      isset($triggered['#parents'][2]) && $triggered['#parents'][2] == 'remove') {
      $items_storage = $form_state->get($group_key . '_storage');
      $id = $triggered['#parents'][1];
      unset($items_storage[$id]);
      $form_state->set($group_key . '_storage', $items_storage);
    }

    $group_class = 'group-order-weight';

    $form[$group_key . '_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('@label: ordering', [
        '@label' => ucfirst(str_replace('_', ' ', $group_key)),
      ]),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    // Build table.
    $form[$group_key . '_fieldset'][$group_key] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Product variant fields'),
        $this->t('Label'),
        $this->t('Bold'),
        $this->t('Daily value field'),
        $this->t('Weight'),
        $this->t('Action'),
      ],
      '#empty' => $this->t('No fields.'),
      '#tableselect' => FALSE,
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => $group_class,
        ],
      ],
      '#prefix' => '<div id="table-wrapper-' . str_replace('_', '-', $group_key) . '">',
      '#suffix' => '</div>',
    ];

    // Build rows.
    foreach ($items_storage as $key => $value) {
      $form[$group_key . '_fieldset'][$group_key][$key]['#attributes']['class'][] = 'draggable';
      $form[$group_key . '_fieldset'][$group_key][$key]['#weight'] = $config[$key]['weight'] ?? 10;

      $form[$group_key . '_fieldset'][$group_key][$key]['field'] = [
        '#type' => 'select',
        '#required' => TRUE,
        '#options' => $this->getFieldNameOptions(),
        '#default_value' => $config[$key]['field'] ?? NULL,
      ];

      $form[$group_key . '_fieldset'][$group_key][$key]['label'] = [
        '#type' => 'textfield',
        '#required' => TRUE,
        '#default_value' => $config[$key]['label'] ?? NULL,
      ];

      $form[$group_key . '_fieldset'][$group_key][$key]['bold'] = [
        '#type' => 'checkbox',
        '#default_value' => $config[$key]['bold'] ?? FALSE,
      ];

      $form[$group_key . '_fieldset'][$group_key][$key]['daily_field'] = [
        '#type' => 'select',
        '#options' => $this->getDailyOptions(),
        '#default_value' => $config[$key]['daily_field'] ?? '',
      ];

      // Weight col.
      $form[$group_key . '_fieldset'][$group_key][$key]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $config[$key]['label']]),
        '#title_display' => 'invisible',
        '#default_value' => $config[$key]['weight'] ?? 10,
        '#attributes' => ['class' => [$group_class]],
      ];

      // Action col.
      $form[$group_key . '_fieldset'][$group_key][$key]['remove'] = [
        '#type'  => 'button',
        '#name' => 'item_' . $group_key . '_' . $key,
        '#value' => $this->t('Remove item'),
        '#limit_validation_errors' => [],
        '#ajax'  => [
          'callback' => [$this, 'ajaxRemoveItemCallback'],
          'wrapper' => 'table-wrapper-' . str_replace('_', '-', $group_key),
        ],
      ];
    }
    $form[$group_key . '_fieldset']['add_item'] = [
      '#type' => 'submit',
      '#name' => 'add_item_' . $group_key,
      '#value' => $this->t('Add item'),
      '#ajax' => [
        'callback' => [$this, 'ajaxAddItemCallback'],
        'wrapper' => 'table-wrapper-' . str_replace('_', '-', $group_key),
      ],
      '#limit_validation_errors' => [],
      '#submit' => [[$this, 'addItemSubmitted']],
      '#attributes' => ['data-subgroup-id' => $group_key],
    ];
  }

  /**
   * Get config.
   *
   * @param string $group_key
   *   Subgroup key.
   * @param string $view_type
   *   View type.
   * @param bool $set_to_default
   *   Set to default settings.
   *
   * @return array|mixed|null
   *   Config.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getConfig(
    string $group_key,
    string $view_type = PdpHeroBlock::NUTRITION_VIEW_US,
    bool $set_to_default = FALSE
  ) {
    $nutrition_config = $this->config('mars_product.nutrition_table_settings');
    if ($nutrition_config->isNew() || $set_to_default) {
      $config = $this->getDefaultConfiguration(
        $view_type
      )[$group_key];
    }
    else {
      $config = $nutrition_config
        ->get($group_key);
    }
    return $config;
  }

  /**
   * Add remove item callback.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   *
   * @return array
   *   Item container of configuration settings.
   */
  public function ajaxRemoveItemCallback(array &$form, FormStateInterface $form_state) {
    $triggered = $form_state->getTriggeringElement();
    if (isset($triggered['#parents'][0])) {
      $key = $triggered['#parents'][0];
      return $form[$key . '_fieldset'][$key];
    }
  }

  /**
   * Add new item link callback.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   *
   * @return array
   *   Item container of configuration settings.
   */
  public function ajaxAddItemCallback(array &$form, FormStateInterface $form_state) {
    $triggered = $form_state->getTriggeringElement();
    if (isset($triggered['#parents'][0]) && $triggered['#parents'][0] == 'add_item') {
      $key = $triggered['#attributes']['data-subgroup-id'];
      return $form[$key . '_fieldset'][$key];
    }
  }

  /**
   * Custom submit item configuration settings form.
   *
   * @param array $form
   *   Theme settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Theme settings form state.
   */
  public function addItemSubmitted(array &$form, FormStateInterface $form_state) {
    $triggered = $form_state->getTriggeringElement();
    if (isset($triggered['#parents'][0]) && $triggered['#parents'][0] == 'add_item') {
      $storage_key = $triggered['#attributes']['data-subgroup-id'] . '_storage';
      $storage = $form_state->get($storage_key);
      array_push($storage, 1);
      $form_state->set($storage_key, $storage);
      $form_state->setRebuild(TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Load configuration entities.
    $config = $this->config('mars_product.nutrition_table_settings');
    $subgroups = [
      PdpHeroBlock::NUTRITION_SUBGROUP_1,
      PdpHeroBlock::NUTRITION_SUBGROUP_2,
      PdpHeroBlock::NUTRITION_SUBGROUP_3,
      PdpHeroBlock::NUTRITION_SUBGROUP_VITAMINS,
    ];

    $trigger = $form_state->getTriggeringElement();
    if ($trigger['#name'] == 'set_to_default') {
      $view_type = $form_state->getUserInput()['view_type'];
      foreach ($subgroups as $subgroup_key) {
        $form_state->setValue(
          $subgroup_key,
          $this->getConfig(
            $subgroup_key,
            $view_type,
            TRUE
          )
        );
      }
      $form_state->setValue('view_type', $view_type);
    }

    // Get configuration from the form fields.
    $config->set('view_type', $form_state->getValue('view_type'));
    foreach ($subgroups as $subgroup_key) {
      $subgroup_value = $form_state->getValue($subgroup_key) ?: [];
      usort($subgroup_value, function ($a, $b) {
        if ($a['weight'] == $b['weight']) {
          return 0;
        }
        return $a['weight'] < $b['weight']
          ? -1
          : 1;
      });
      $config->set($subgroup_key, $subgroup_value);
    }
    $config->set('product_serving_size', $form_state->getValue('product_serving_size'));
    $config->set('servings_per_container', $form_state->getValue('servings_per_container'));
    $config->set('show_other_nutrients_text', $form_state->getValue('show_other_nutrients_text'));
    $config->set('hide_table_heading', $form_state->getValue('hide_table_heading'));
    $config->set('dual_servings_per_container', $form_state->getValue('dual_servings_per_container'));
    $config->set('show_dual_table', $form_state->getValue('show_dual_table'));
    $config->set('override_dual_table_heading', $form_state->getValue('override_dual_table_heading'));
    // Save the configuration.
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Return the configuration names.
   */
  protected function getEditableConfigNames() {
    return [
      'mars_product.nutrition_table_settings',
    ];
  }

  /**
   * Get default configuration per brand.
   *
   * @param string $brand
   *   Brand.
   *
   * @return array
   *   Mapping.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getDefaultConfiguration(string $brand = 'US') {
    $groups_mapping = [
      PdpHeroBlock::NUTRITION_SUBGROUP_1,
      PdpHeroBlock::NUTRITION_SUBGROUP_2,
      PdpHeroBlock::NUTRITION_SUBGROUP_3,
      PdpHeroBlock::NUTRITION_SUBGROUP_VITAMINS,
    ];
    $node = $this->entityTypeManager
      ->getStorage('node')
      ->create([
        'type' => 'product_variant',
      ]);
    $form = $this->entityFormBuilder->getForm($node);
    $mapping = [];
    foreach ($groups_mapping as $group) {
      foreach ($form['#fieldgroups'] as $fieldgroup) {
        if ($fieldgroup->group_name == $group) {
          foreach ($fieldgroup->children as $field) {
            if (strpos($field, 'daily') === FALSE) {
              $mapping[$group][$field] = [
                'field' => $field,
                'label' => $this->languageHelper->translate($node->get($field)
                  ->getFieldDefinition()
                  ->getLabel()),
                'weight' => $form[$field]['#weight'] ?? 0,
                'bold' => (isset(PdpHeroBlock::FIELDS_WITH_BOLD_LABELS[$field]))
                ? TRUE
                : FALSE,
                'daily_field' => $this->getDailyField($field),
              ];
            }
          }
        }
      }
    }

    if ($brand == PdpHeroBlock::NUTRITION_VIEW_US) {
      unset($mapping[PdpHeroBlock::NUTRITION_SUBGROUP_1]['field_product_ltd_calories']);
    }
    if ($brand == PdpHeroBlock::NUTRITION_VIEW_UK) {
      unset($mapping[PdpHeroBlock::NUTRITION_SUBGROUP_1]['field_product_calories']);
      unset($mapping[PdpHeroBlock::NUTRITION_SUBGROUP_3]['field_product_added_sugars']);
      $mapping[PdpHeroBlock::NUTRITION_SUBGROUP_1]['field_product_ltd_calories']['bold'] = TRUE;
      $mapping[PdpHeroBlock::NUTRITION_SUBGROUP_2]['field_product_total_fat']['label'] = $this->t('Fat');
      $mapping[PdpHeroBlock::NUTRITION_SUBGROUP_2]['field_product_total_fat']['bold'] = TRUE;
      $mapping[PdpHeroBlock::NUTRITION_SUBGROUP_2]['field_product_saturated_fat']['label'] = $this->t(
        'Of which Saturates'
      );
      $mapping[PdpHeroBlock::NUTRITION_SUBGROUP_3]['field_product_carb']['label'] = $this->t('Carbohydrate');
      $mapping[PdpHeroBlock::NUTRITION_SUBGROUP_3]['field_product_carb']['bold'] = TRUE;
      $mapping[PdpHeroBlock::NUTRITION_SUBGROUP_3]['field_product_total_sugars']['label'] = $this->t(
        'Of which Sugars'
      );
      $mapping[PdpHeroBlock::NUTRITION_SUBGROUP_3]['field_product_protein']['bold'] = TRUE;
      $mapping[PdpHeroBlock::NUTRITION_SUBGROUP_3]['field_product_sodium']['label'] = $this->t('Salt');
      $mapping[PdpHeroBlock::NUTRITION_SUBGROUP_3]['field_product_sodium']['bold'] = TRUE;
      $mapping[PdpHeroBlock::NUTRITION_SUBGROUP_3]['field_product_sodium']['weight'] = 99;

      $this->setDailyValueToNone($mapping);
      $mapping[PdpHeroBlock::NUTRITION_SUBGROUP_VITAMINS] = [];
    }
    $result = [];
    foreach ($mapping as $key => $value) {
      $result[$key] = array_values($mapping[$key]);
    }
    return $result;
  }

  /**
   * Get available options for the 'field name' field.
   *
   * @return array|false
   *   Options.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getFieldNameOptions() {
    $groups_mapping = [
      PdpHeroBlock::NUTRITION_SUBGROUP_1,
      PdpHeroBlock::NUTRITION_SUBGROUP_2,
      PdpHeroBlock::NUTRITION_SUBGROUP_3,
      PdpHeroBlock::NUTRITION_SUBGROUP_VITAMINS,
    ];
    $node = $this->entityTypeManager
      ->getStorage('node')
      ->create([
        'type' => 'product_variant',
      ]);
    $form = $this->entityFormBuilder->getForm($node);
    $fields = [];
    foreach ($groups_mapping as $group) {
      foreach ($form['#fieldgroups'] as $fieldgroup) {
        if ($fieldgroup->group_name == $group) {
          foreach ($fieldgroup->children as $field) {
            if (strpos($field, 'daily') === FALSE) {
              $fields[$field] = $field;
            }
          }
        }
      }
    }
    return $fields;
  }

  /**
   * Get options for daily field.
   *
   * @return array|false
   *   Daily field options.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getDailyOptions() {
    $node = $this->entityTypeManager
      ->getStorage('node')
      ->create([
        'type' => 'product_variant',
      ]);
    $fields = ['none' => $this->t('None')];
    $fields += array_combine(
      array_keys($node->getFieldDefinitions()),
      array_keys($node->getFieldDefinitions())
    );
    return $fields;
  }

  /**
   * Get daily field by name.
   *
   * @param string $field_name
   *   Base field name.
   *
   * @return string
   *   Daily field name.
   */
  private function getDailyField(string $field_name) {
    $field_daily = 'none';
    if (isset(PdpHeroBlock::FIELDS_MAPPING_DAILY[$field_name]) &&
      PdpHeroBlock::FIELDS_MAPPING_DAILY[$field_name] !== FALSE) {

      $field_daily = PdpHeroBlock::FIELDS_MAPPING_DAILY[$field_name];
      $field_daily = !empty($field_daily) ? $field_daily : $field_name . '_daily';
    }
    return $field_daily;
  }

  /**
   * Set all daily values to none for the UK market.
   *
   * @param array $mapping
   *   Mapping array.
   */
  private function setDailyValueToNone(array &$mapping) {
    $groups_mapping = [
      PdpHeroBlock::NUTRITION_SUBGROUP_1,
      PdpHeroBlock::NUTRITION_SUBGROUP_2,
      PdpHeroBlock::NUTRITION_SUBGROUP_3,
      PdpHeroBlock::NUTRITION_SUBGROUP_VITAMINS,
    ];
    foreach ($groups_mapping as $group) {
      foreach ($mapping[$group] as $field => $field_value) {
        if (isset($field_value['daily_field'])) {
          $mapping[$group][$field]['daily_field'] = 'none';
        }
      }
    }
  }

}
