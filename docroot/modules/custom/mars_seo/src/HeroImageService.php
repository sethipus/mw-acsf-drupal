<?php

namespace Drupal\mars_seo;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\layout_builder\SectionComponent;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_common\Utils\NodeLBComponentIterator;
use Drupal\mars_seo\Form\OpenGraphSettingForm;
use Drupal\node\NodeInterface;

/**
 * Hero image service.
 */
class HeroImageService {

  const CONFIG_KEY_VARIATION = 'background_type_field';

  const CONFIG_KEY_IMAGE = 'hero_image_field';

  /**
   * Blocks structure with hero image.
   */
  const HERO_BLOCK_TYPES = [
    'homepage_hero_block' => [
      self::CONFIG_KEY_VARIATION => 'block_type',
      self::CONFIG_KEY_IMAGE => 'background_image',
    ],
    'parent_page_header' => [
      self::CONFIG_KEY_VARIATION => 'background_options',
      self::CONFIG_KEY_IMAGE => 'background_image',
    ],
  ];

  /**
   * Mars Common Media Helper.
   *
   * @var \Drupal\mars_media\MediaHelper
   */
  private $mediaHelper;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Constructs a HeroImageService object.
   *
   * @param \Drupal\mars_media\MediaHelper $media_helper
   *   The media helper service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(
    MediaHelper $media_helper,
    ConfigFactoryInterface $config_factory
  ) {
    $this->mediaHelper = $media_helper;
    $this->configFactory = $config_factory;
  }

  /**
   * Create the cacheable metadata for the hero image calculation of the node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cacheable metadata.
   */
  public function getCacheableMetadata(NodeInterface $node) {
    $metadata = new CacheableMetadata();

    if ($node) {
      $metadata->addCacheableDependency($node);
    }

    $metadata->addCacheableDependency($this->getOpenGraphConfig());

    return $metadata;
  }

  /**
   * Returns hero image url for a given node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return string|null
   *   Hero image url.
   */
  public function getHeroImageUrl(NodeInterface $node): ?string {
    $media_url = $this->mediaHelper->getMediaUrl($this->getHeroImageId($node));

    if (!$media_url) {
      return NULL;
    }

    return $media_url;
  }

  /**
   * Returns the media id for the hero image.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return string|null
   *   The id of the hero image or null if it was not found.
   */
  private function getHeroImageId(NodeInterface $node): ?string {
    $hero_image_id = $this->mediaHelper->getEntityMainMediaId($node);
    if ($hero_image_id !== NULL) {
      return $hero_image_id;
    }
    $hero_image_id = $this->extractFromLayoutBuilder($node);
    if ($hero_image_id !== NULL) {
      return $hero_image_id;
    }
    return $this->getDefaultImage();
  }

  /**
   * Returns the default image id, or null if was not set.
   *
   * @return string|null
   *   The media id or null if it was not set.
   */
  private function getDefaultImage(): ?string {
    $config = $this->getOpenGraphConfig();
    $image = $config->get('image');
    return $this->mediaHelper->getIdFromEntityBrowserSelectValue($image);
  }

  /**
   * Extracts hero image id from layout builder layout field.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return string|null
   *   The hero media id or null if it was not found.
   */
  private function extractFromLayoutBuilder(NodeInterface $node): ?string {
    $media_id = NULL;
    foreach (new NodeLBComponentIterator($node) as $component) {
      try {
        $media_id = $this->getHeroImageFromBlock($component);
      }
      catch (PluginException $e) {
        // Skip this component.
      }
      if ($media_id !== NULL) {
        return $media_id;
      }
    }
    return $media_id;
  }

  /**
   * Extracts hero image from a layout builder block.
   *
   * @param \Drupal\layout_builder\SectionComponent $component
   *   Layout builder block.
   *
   * @return string|null
   *   The media id if it's a hero block with an image.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function getHeroImageFromBlock(SectionComponent $component): ?string {
    $media_id = NULL;
    $block_type = $component->getPluginId();

    if ($this->blockIsHeroBlock($block_type)) {
      $configuration = $component->get('configuration');
      $variation_config_name = self::HERO_BLOCK_TYPES[$block_type][self::CONFIG_KEY_VARIATION];
      $image_config_name = self::HERO_BLOCK_TYPES[$block_type][self::CONFIG_KEY_IMAGE];

      if (($configuration[$variation_config_name] ?? NULL) === 'image') {
        $image_config_value = $configuration[$image_config_name] ?? NULL;
        $media_id = $this->mediaHelper->getIdFromEntityBrowserSelectValue($image_config_value);
      }
    }

    return $media_id;
  }

  /**
   * Checks if the given block is a supported hero block type.
   *
   * @param string $block_type
   *   The type of the block.
   *
   * @return bool
   *   True if the block is a hero block, false otherwise.
   */
  private function blockIsHeroBlock(string $block_type): bool {
    $hero_block_types = array_keys(self::HERO_BLOCK_TYPES);
    return in_array($block_type, $hero_block_types);
  }

  /**
   * Returns the OG config object.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The OG config object.
   */
  private function getOpenGraphConfig(): ImmutableConfig {
    return $this->configFactory->get(OpenGraphSettingForm::SETTINGS);
  }

}
