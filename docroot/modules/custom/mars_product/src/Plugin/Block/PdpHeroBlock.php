<?php

namespace Drupal\mars_product\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
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
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->config = $config_factory;
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
      ]
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
    // TODO border radius - get border from Theme settings.
    // not implemented yet.
    $theme_settings = $this->config->get('emulsifymars.settings')->get();
    $build['#wtb_border_radius'] = 25;

    // Get Product dependent values.
    $node = $this->getContextValue('node');
    $build['#product_name'] = $node->label();
    $build['#product_description']
      = $node->get('field_product_description')->value;
    $build['#image_items'] = $this->getImageItems($node);
    $build['#size_items'] = $this->getSizeItems($node);
    $build['#mobile_items'] = $this->getMobileItems();

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
   * @param $node
   *
   * @return array
   */
  public function getImageItems($node) {
    $items = [];
    $field_size = 'field_product_size';
    $i = 0;
    foreach ($node->field_product_variants as $key => $reference) {
      $product_variant = $reference->entity;
      $size = $product_variant->get($field_size)->value;
      $size_id = $this->getMachineName($size);
      $i++;
      $state = $i == 1 ? 'true' : 'false';
      $items[$size_id] = [
        'active' => $state,
        'size_id' => $size_id ,
      ];
      for ($i = 1; $i <= 4; $i++) {
        $field_name = 'field_product_image_' . $i;
        $field_name_override = 'field_product_image_' . $i . '_override';

        $image = $product_variant->{$field_name}->entity;
        //$a = $product_variant->{$field_name}->first->getValue();
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
          ]
        ];

        $items[$size_id]['images'][] = $item;
      }
    }

    return $items;
  }

  /**
   * @param $node
   *
   * @return array
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
   *
   * TODO. It's a STUB. Make it configurable.
   *
   * @return array
   */
  public function getMobileItems() {
    $map = [
      'section-nutrition' => 'NUTRITION & INGREDIENTS',
      'section-allergens' => 'DIET & ALLERGENS',
      'section-products' => 'RELATED PRODUCTS',
    ];
    $item = [
      'link_url' => '#',
      'border_radius' => 20,
    ];
    $items = [];
    foreach ($map as $id => $title) {
      $item['link_attributes']['id'] = $id;
      $item['title'] = $title;
      $items[] = $item;
    }

    return $items;
  }

  /**
   * @param $string
   *
   * @return string
   */
  public function getMachineName($string = '') {
    return mb_strtolower(trim(str_replace(' ', '', $string)));
  }

}
