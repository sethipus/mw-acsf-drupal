<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\MediaHelper;
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
   * Price spider id.
   */
  const VENDOR_PRICE_SPIDER = 'price_spider';

  /**
   * Commerce connector id.
   */
  const VENDOR_COMMERCE_CONNECTOR = 'commerce_connector';

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

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
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    LanguageManagerInterface $language_manager,
    EntityTypeManagerInterface $entity_manager,
    MediaHelper $media_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $config_factory;
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_manager;
    $this->mediaHelper = $media_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('entity_type.manager'),
      $container->get('mars_common.media_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

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

    $form['widget_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Widget id'),
      '#default_value' => $this->configuration['widget_id'],
      '#required' => TRUE,
    ];

    $form['product_gtin'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product Gtin'),
      '#description' => $this->t('Optional, if empty, get from first product (variation) from site.'),
      '#default_value' => $this->configuration['product_gtin'],
      '#states' => [
        'visible' => [
          [':input[name="settings[commerce_vendor]"]' => ['value' => self::VENDOR_PRICE_SPIDER]],
        ],
      ],
    ];

    $form['data_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token'),
      '#default_value' => $this->configuration['data_token'],
      '#states' => [
        'visible' => [
          [':input[name="settings[commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
        'required' => [
          [':input[name="settings[commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

    $form['data_subid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SubId'),
      '#default_value' => $this->configuration['data_subid'],
      '#states' => [
        'visible' => [
          [':input[name="settings[commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

    $form['product_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product ID'),
      '#default_value' => $this->configuration['product_id'],
      '#states' => [
        'visible' => [
          [':input[name="settings[commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

    $form['cta_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA title'),
      '#default_value' => $this->configuration['cta_title'],
      '#states' => [
        'visible' => [
          [':input[name="settings[commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

    $form['button_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Commerce Connector: button type'),
      '#default_value' => $this->configuration['button_type'],
      '#options' => [
        'my_own' => $this->t('My own button'),
        'commerce_connector' => $this->t('Commerce Connector button'),
      ],
      '#states' => [
        'visible' => [
          [':input[name="settings[commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

    $form['data_locale'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Commerce connector data locale'),
      '#default_value' => $this->configuration['data_locale'],
      '#states' => [
        'visible' => [
          [':input[name="settings[commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
        'required' => [
          [':input[name="settings[commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
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
      'commerce_vendor' => $config['commerce_vendor'] ?? '',
      'widget_id' => $config['widget_id'] ?? '',
      'data_token' => $config['data_token'] ?? '',
      'data_subid' => $config['data_subid'] ?? '',
      'cta_title' => $config['cta_title'] ?? '',
      'product_id' => $config['product_id'] ?? '',
      'button_type' => $config['button_type'] ?? '',
      'data_locale' => $config['data_locale'] ?? '',
      'product_gtin' => $config['product_gtin'] ?? '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#theme'] = 'where_to_buy_block';
    $this->pageAttachments($build);

    $build['#commerce_vendor'] = $this->configuration['commerce_vendor'];
    $build['#product_id'] = $this->configuration['product_id'];
    $build['#cta_title'] = $this->configuration['cta_title'];
    $build['#button_type'] = $this->configuration['button_type'];
    $build['#widget_id'] = $this->configuration['widget_id'];
    $build['#data_subid'] = $this->configuration['data_subid'];
    $build['#data_token'] = $this->configuration['data_token'];
    $build['#data_locale'] = $this->configuration['data_locale'];
    $build['#product_gtin'] = $this->configuration['product_gtin'];

    $locale = $this->languageManager->getCurrentLanguage()->getId();
    $build['#data_displaylanguage'] = $locale;

    $build['#attached']['drupalSettings']['wtb_block'] = [
      'commerce_vendor' => $this->configuration['commerce_vendor'],
      'widget_id' => $this->configuration['widget_id'],
      'data_subid' => $this->configuration['data_subid'],
      'data_token' => $this->configuration['data_token'],
      'data_locale' => $this->configuration['data_locale'],
      'data_displaylanguage' => $locale,
    ];

    $products = $this->entityTypeManager->getStorage('node')
      ->loadByProperties([
        'type' => 'product',
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

      /* @var \Drupal\node\NodeInterface $variant */
      $media_override_id = $variant->get('field_product_key_image_override')
        ->target_id;
      $media_params = $this->mediaHelper->getMediaParametersById($media_override_id);

      // Override media missing or has error try the normal version.
      if ($media_params['error'] ?? FALSE) {
        $media_id = $variant->get('field_product_key_image')->target_id;
        $media_params = $this->mediaHelper->getMediaParametersById($media_id);
      }

      $image_src = $image_alt = NULL;
      // Override media and the normal version both failed, we should skip this.
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
            'content' => $this->configuration['widget_id'],
          ],
        ],
        'ps-country' => [
          '#tag' => 'meta',
          '#attributes' => [
            'name' => 'ps-country',
            'content' => $this->config->get('system.date')
              ->get('country.default'),
          ],
        ],
        'ps-language' => [
          '#tag' => 'meta',
          '#attributes' => [
            'name' => 'ps-language',
            'content' => strtolower($this->languageManager->getCurrentLanguage()
              ->getId()),
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
