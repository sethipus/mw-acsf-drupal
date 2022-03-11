<?php

namespace Drupal\mars_product;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_product\Plugin\Block\PdpHeroBlock;

/**
 * Helper class for Product related logic.
 */
class ProductHelper {

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ProductHelper constructor.
   *
   * @param \Drupal\mars_common\LanguageHelper $language_helper
   *   The Language helper service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    LanguageHelper $language_helper,
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->languageHelper = $language_helper;
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Returns the main variant of a content if it's a product.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface|null $contentEntity
   *   The content entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   Returns the main variant of the product or NULL if it's not a product.
   */
  public function mainVariant(
    ?ContentEntityInterface $contentEntity
  ): ?ContentEntityInterface {
    if ($contentEntity === NULL ||
      !in_array($contentEntity->bundle(), ['product', 'product_multipack'])
    ) {
      return NULL;
    }

    try {
      $variants = $contentEntity
        ->get('field_product_variants')
        ->referencedEntities();

      /** @var \Drupal\Core\Entity\ContentEntityInterface $main_variant */
      $main_variant = NULL;
      $main_variant_size = NULL;
      $grp_images = [];
      foreach ($variants as $variant) {
        $is_master = $variant->get('field_product_family_master')->value;
        $size = $variant->get('field_product_size')->value;
        $size = (is_numeric($size)) ? (float) $size : (is_string($size) ?
          explode(' ', $size)[0] : NULL);
        $is_display_first = $variant->get('field_pdt_var_grp_primary')->value;
        $grp_images[] = $is_display_first ? $variant->get('field_product_variant_grp_image')->first()->target_id : '';

        if ($is_master) {
          $main_variant = $variant;
          break;
        }
        elseif ($main_variant && !is_numeric($main_variant_size) &&
          is_numeric($size)) {

          $main_variant = $variant;
          $main_variant_size = $size;
        }
        elseif ($main_variant && is_numeric($main_variant_size) &&
           is_numeric($size) && ($size < $main_variant_size)) {

          $main_variant = $variant;
          $main_variant_size = $size;
        }
        elseif (!$main_variant) {
          $main_variant = $variant;
          $main_variant_size = $size;
        }

      }

      if ($main_variant instanceof ContentEntityInterface) {
        $main_variant = $this->languageHelper->getTranslation($main_variant);
      }
      if (count($grp_images) > 1) {
        $grp_image = reset(array_filter($grp_images));
        $main_variant->set('field_product_variant_grp_image', $grp_image);
      }
    }
    catch (MissingDataException $e) {
      $main_variant = NULL;
    }

    return $main_variant;
  }

  /**
   * Returns all parent products by variant.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $variant
   *   The product variant.
   *
   * @return array
   *   Array of products.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getProductsByVariant(ContentEntityInterface $variant): array {
    $node_storage = $this->entityTypeManager->getStorage('node');

    $product_ids = $node_storage->getQuery()
      ->condition('type', 'product')
      ->condition('field_product_variants', $variant->id(), '=')
      ->execute();

    return $node_storage->loadMultiple($product_ids);
  }

  /**
   * Returns the currently active commerce vendor.
   *
   * @return string
   *   The commerce vendor provider value.
   *
   * @todo Check if private method getCommerceVendor() in WhereToBuyBlock and PdpHeroBlock are needed.
   */
  public function getCommerceVendor(): string {
    return $this->configFactory->get('mars_product.wtb.settings')->get('commerce_vendor') ?? PdpHeroBlock::VENDOR_NONE;
  }

  /**
   * Returns the field name of widget id based on commerce vendor.
   *
   * @param string $display
   *   The place where widget should be displayed.
   *
   * @return string
   *   The widget id value.
   */
  public function getWidgetIdField(string $display): string {
    if ($this->getCommerceVendor() == PdpHeroBlock::VENDOR_SMART_COMMERCE) {
      if ($display == 'product_card') {
        return 'button_widget_id';
      }
      else {
        return 'carousel_widget_id';
      }
    }
    else {
      return 'widget_id';
    }
  }

  /**
   * Helper function to form SKU .
   *
   * @param string $sku
   *   The raw SKU value.
   *
   * @return string
   *   The SKU value.
   *
   * @todo Remove after Smart Commerce will prepare UPC ids to our SKU format.
   */
  public function formatSku(string $sku): string {
    if ($this->getCommerceVendor() == PdpHeroBlock::VENDOR_SMART_COMMERCE) {
      if (strlen($sku) == 14 && strpos($sku, '000') === 0) {
        return substr($sku, 2);
      }
    }
    return $sku;
  }

}
