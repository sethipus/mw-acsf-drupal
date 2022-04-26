<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_product\Plugin\Block\PdpHeroBlock;
use Drupal\node\NodeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
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
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Media helper.
   *
   * @var \Drupal\mars_media\MediaHelper
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
    ConfigFactoryInterface $config_factory,
    ImmutableConfig $wtb_global_config
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_manager;
    $this->mediaHelper = $media_helper;
    $this->wtbGlobalConfig = $wtb_global_config;
    $this->configFactory = $config_factory;
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
      $container->get('mars_media.media_helper'),
      $container->get('config.factory'),
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
    if ($selected_vendor !== PdpHeroBlock::VENDOR_MIK_MAK) {
      $form['widget_id'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Widget id'),
        '#default_value' => $this->configuration['widget_id'],
        '#required' => TRUE,
      ];
    }
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

      $products = $this->sortProductsByWeight($this->getProductsList());
      // Render the product list table with a ability to change product order in
      // the product dropdown.
      $headers = [
        'title' => $this->t('Product label'),
        'id' => $this->t('Product ID'),
        'weight' => $this->t('Display order'),
      ];
      $form['products_list_weight'] = [
        '#type' => 'table',
        '#header' => $headers,
        '#caption' => $this->t('<h3>Where To By products order</h3><span style="color:#ff4700; display: block; padding-bottom: 15px;"><b>Warning:</b> The first item in the list will be considered as a <b>default product</b></span>'),
        '#attributes' => [
          'id' => 'products-order',
        ],
        '#tabledrag' => [
          [
            'action' => 'order',
            'relationship' => 'sibling',
            'group' => 'weight',
          ],
        ],
      ];
      // Add product rows to the table.
      foreach ($products as $product) {
        $form['products_list_weight'][$product->id()] = [
          '#attributes' => [
            'class' => ['draggable'],
          ],
          'label' => [
            '#markup' => Link::fromTextAndUrl(
              $product->label(),
              Url::fromRoute(
                'entity.node.canonical',
                ['node' => $product->id()],
                ['attributes' => ['target' => '_blank']]
              ))->toString(),
          ],
          'id' => ['#markup' => $product->id()],
          'weight' => [
            '#type' => 'weight',
            '#default_value' => !empty($this->configuration['products_list_weight'][$product->id()]) ? $this->configuration['products_list_weight'][$product->id()]['weight'] : $product->id(),
            '#title' => $this->t('Weight for @product', ['@product' => $product->label()]),
            '#title_display' => 'invisible',
            '#attributes' => [
              'class' => ['weight'],
            ],
          ],
        ];
      }

      $form['data_displaylanguage'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Commerce connector data display language'),
        '#default_value' => $this->configuration['data_displaylanguage'],
        '#description' => $this->t('Please use this field to specify widget display language once it is different from the site common language. Field value format sample for German sites: <b>de</b>'),
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
    // MIkMak settings.
    elseif ($selected_vendor === PdpHeroBlock::VENDOR_MIK_MAK) {
      $form['mikmak_product_sku'] = [
        '#type' => 'textfield',
        '#title' => $this->t("Product SKUs"),
        '#description' => $this->t('if left empty all product SKUs mapped with MikMak will show OR add a comma seperated SKUs with no space that products only it show. eg., 054800423392,054800423385,..'),
        '#default_value' => $this->configuration['mikmak_product_sku'],
        '#required' => FALSE,
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
      'data_displaylanguage' => $config['data_displaylanguage'] ?? '',
      'product_sku' => $config['product_sku'] ?? '',
      'mikmak_product_sku' => $config['mikmak_product_sku'] ?? '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#theme'] = 'where_to_buy_block';

    $commerce_vendor = $this->getCommerceVendor();
    $build['#widget_id'] = $this->configuration['widget_id'];
    $build['#commerce_vendor'] = $commerce_vendor;
    switch ($commerce_vendor) {
      case PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR:
        $commerce_connector_settings = $this->getCommerceVendorInfo($commerce_vendor);
        $hide_size_dropdown = !empty($commerce_connector_settings) ? $commerce_connector_settings['hide_size_dropdown'] : '';
        $build['#data_subid'] = $this->configuration['data_subid'];
        $build['#data_token'] = $this->configuration['data_token'];
        $build['#data_locale'] = $this->configuration['data_locale'];

        $locale = $this->languageManager->getCurrentLanguage()->getId();
        $build['#data_displaylanguage'] = !empty($this->configuration['data_displaylanguage']) ? $this->configuration['data_displaylanguage'] : $locale;

        $build['#attached']['drupalSettings']['wtb_block'] = [
          'widget_id' => $this->configuration['widget_id'],
          'data_subid' => $this->configuration['data_subid'],
          'data_token' => $this->configuration['data_token'],
          'data_locale' => $this->configuration['data_locale'],
          'data_displaylanguage' => !empty($this->configuration['data_displaylanguage']) ? $this->configuration['data_displaylanguage'] : $locale,
        ];

        /** @var \Drupal\node\Entity\Node[] $products */
        $products = $this->sortProductsByWeight($this->getProductsList());

        $products_for_render = [];
        foreach ($products as $product) {
          $variants_info = $this->addProductVariantsInfo($product);
          if (empty($variants_info) || empty($variants_info[0]['size'])) {
            continue;
          }
          // Variant data for json encoded string.
          $variants_for_json = array_map(function ($variant_item) {
            unset($variant_item['image_src'], $variant_item['image_alt']);
            return $variant_item;
          }, $variants_info);

          $products_for_render[] = [
            'id' => $product->id(),
            'title' => $product->label(),
            'variants' => $variants_info,
            'variants_json' => json_encode($variants_for_json),
          ];
        }
        $build['#products'] = $products_for_render;
        $build['#hide_size_dropdown'] = $hide_size_dropdown ? true : false;
        break;

      case PdpHeroBlock::VENDOR_SMART_COMMERCE:
        $build = [];
        break;

      case PdpHeroBlock::VENDOR_MIK_MAK:
        $build['#mikmak_product_sku'] = $this->configuration['mikmak_product_sku'];
        break;

      default:
        $build['#product_sku'] = $this->configuration['product_sku'];
        break;
    }

    // Disable caching for the block as it can be changed at any time.
    $build['#cache'] = [
      'max-age' => 0,
    ];

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
        $image_src = (string) $media_params['src'];
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
      $commerce_vendor_settings = !empty($commerce_vendor_settings) && !$commerce_vendor_settings->isNew() ? $commerce_vendor_settings->getRawData() : [];
      return !empty($commerce_vendor_settings['settings']) ? $commerce_vendor_settings['settings'] : [];
    }
    return [];
  }

  /**
   * Provides product list to render.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]|\Drupal\node\Entity\Node[]
   *   Returns product list to render.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getProductsList() {
    /** @var \Drupal\node\Entity\Node[] $products */
    $products = $this->entityTypeManager->getStorage('node')
      ->loadByProperties([
        'type' => ['product_multipack'],
      ]);

    // Get product ids which are not included to multipacks.
    $products_query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'product', '=');
    $or_condition_group = $products_query->orConditionGroup();
    $or_condition_group
      ->notExists('field_product_generated')
      ->condition('field_product_generated', 0, '=');
    $products_ids = $products_query->condition($or_condition_group)->execute();
    // Load found products by their ids.
    $products += $this->entityTypeManager->getStorage('node')->loadMultiple($products_ids);
    return $products;
  }

  /**
   * Provides sorted list of products by their weight.
   *
   * @param \Drupal\Core\Entity\EntityInterface[]|\Drupal\node\Entity\Node[] $products
   *   Product entities list.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]|\Drupal\node\Entity\Node[]
   *   Returns sorted products list.
   */
  private function sortProductsByWeight(array $products) {
    if (!empty($this->configuration['products_list_weight'])) {
      $weights = $this->configuration['products_list_weight'];
      usort($products, function ($a, $b) use ($weights) {
        return $weights[$a->id()]['weight'] > $weights[$b->id()]['weight'];
      });
    }
    return $products;
  }

}
