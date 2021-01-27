<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_product\Plugin\Block\PdpHeroBlock;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Where To Buy block.
 *
 * @Block(
 *   id = "where_to_buy_block",
 *   admin_label = @Translation("MARS: Where To Buy"),
 *   category = @Translation("Mars Common")
 * )
 */
class WhereToBuyBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Media helper.
   *
   * @var \Drupal\mars_common\MediaHelper
   */
  private $mediaHelper;

  /**
   * Where to buy global configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $wtbGlobalConfig;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LanguageManagerInterface $language_manager,
    EntityTypeManagerInterface $entity_manager,
    MediaHelper $media_helper,
    ImmutableConfig $wtb_global_config
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_manager;
    $this->mediaHelper = $media_helper;
    $this->wtbGlobalConfig = $wtb_global_config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    $config_factory = $container->get('config.factory');
    $config = $config_factory->get('mars_product.wtb.settings');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('entity_type.manager'),
      $container->get('mars_common.media_helper'),
      $config
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state
  ) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $selected_vendor = $this->getCommerceVendor();

    $form['widget_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Widget id'),
      '#default_value' => $this->configuration['widget_id'],
      '#required' => TRUE,
    ];
    if ($selected_vendor === PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR) {
      $form['data_token'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Token'),
        '#default_value' => $this->configuration['data_token'],
        '#required' => TRUE,
      ];

      $form['data_subid'] = [
        '#type' => 'textfield',
        '#title' => $this->t('SubId'),
        '#default_value' => $this->configuration['data_subid'],
      ];

      $form['data_locale'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Commerce connector data locale'),
        '#default_value' => $this->configuration['data_locale'],
        '#required' => TRUE,
      ];
    }
    elseif ($selected_vendor === PdpHeroBlock::VENDOR_PRICE_SPIDER) {
      $form['product_sku'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Product sku'),
        '#description' => $this->t('Valid product sku that will be the initially selected product.'),
        '#default_value' => $this->configuration['product_sku'],
        '#required' => TRUE,
      ];
    }

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
      'widget_id' => $config['widget_id'] ?? '',
      'data_token' => $config['data_token'] ?? '',
      'data_subid' => $config['data_subid'] ?? '',
      'data_locale' => $config['data_locale'] ?? '',
      'product_sku' => $config['product_sku'] ?? '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#theme'] = 'where_to_buy_block';

    $commerceVendor = $this->getCommerceVendor();
    $build['#widget_id'] = $this->configuration['widget_id'];
    $build['#commerce_vendor'] = $commerceVendor;

    if ($commerceVendor === PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR) {
      $build['#data_subid'] = $this->configuration['data_subid'];
      $build['#data_token'] = $this->configuration['data_token'];
      $build['#data_locale'] = $this->configuration['data_locale'];

      $locale = $this->languageManager->getCurrentLanguage()->getId();
      $build['#data_displaylanguage'] = $locale;

      $build['#attached']['drupalSettings']['wtb_block'] = [
        'widget_id' => $this->configuration['widget_id'],
        'data_subid' => $this->configuration['data_subid'],
        'data_token' => $this->configuration['data_token'],
        'data_locale' => $this->configuration['data_locale'],
        'data_displaylanguage' => $locale,
      ];

      /** @var \Drupal\node\Entity\Node[] $products */
      $products = $this->entityTypeManager->getStorage('node')
        ->loadByProperties([
          'type' => ['product', 'product_multipack'],
        ]);
      $products_for_render = [];
      $default_product = [];
      foreach ($products as $product) {
        $products_for_render[] = [
          'id' => $product->id(),
          'title' => $product->label(),
        ];
        if (empty($default_product)) {
          $variants_info = $this->addProductVariantsInfo($product);
          if (empty($variants_info) || empty($variants_info[0]['size'])) {
            continue;
          }

          $default_product['id'] = $product->id();
          $default_product['title'] = $product->label();
          $default_product['variants'] = $variants_info;
        }
      }
      $build['#products'] = $products_for_render;
      $build['#default_product'] = $default_product;
    }
    else {
      $build['#product_sku'] = $this->configuration['product_sku'];
    }

    return $build;
  }

  /**
   * Collect product variant related info.
   *
   * @param \Drupal\node\NodeInterface $product
   *   Product.
   *
   * @return array
   *   Info related to product variant.
   */
  private function addProductVariantsInfo(NodeInterface $product) {
    $variants_info = [];
    $variants = $product->get('field_product_variants')
      ->referencedEntities();

    foreach ($variants as $variant) {
      $media_params = $this->mediaHelper->getMediaParametersById(
        $this->mediaHelper->getEntityMainMediaId($variant)
      );
      $image_src = $image_alt = NULL;
      if (isset($media_params['src'])) {
        $image_src = $media_params['src'];
        $image_alt = $media_params['alt'];
      }

      $variants_info[] = [
        'size' => $variant->get('field_product_size')->value,
        'image_src' => $image_src,
        'image_alt' => $image_alt,
        'gtin' => $variant->get('field_product_sku')->value,
      ];
    }

    return $variants_info;
  }

  /**
   * Returns the currently active commerce vendor.
   *
   * @return string
   *   The commerce vendor provider value.
   */
  private function getCommerceVendor(): string {
    return $this->wtbGlobalConfig->get('commerce_vendor') ?? PdpHeroBlock::VENDOR_NONE;
  }

}
