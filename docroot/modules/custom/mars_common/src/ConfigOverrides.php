<?php

namespace Drupal\mars_common;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\ConfigInstallerInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Installer\InstallerKernel;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\layout_builder\SectionComponent;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Configuration override.
 */
class ConfigOverrides implements ConfigFactoryOverrideInterface {

  /**
   * Theme configuration block id.
   */
  const THEME_CONFIGURATION_BLOCK_ID = 'theme_configuration_block';

  /**
   * Block config theme parent keys.
   */
  const BLOCK_CONFIG_THEME_PARENT_KEYS = [
    'color_settings',
    'font_settings',
    'icons_settings',
    'product_layout',
  ];

  /**
   * Theme config name.
   */
  const THEME_CONFIG = 'emulsifymars.settings';

  /**
   * Social const.
   */
  const SOCIAL = 'social';

  /**
   * Needed config override from general theme.
   */
  const NEEDED_CONFIG_OVERRIDE = [
    'color_a',
    'color_b',
    'color_c',
    'color_d',
    'color_e',
    'color_f',
    'top_nav',
    'top_nav_gradient',
    'bottom_nav',
    'card_background',
    'headline_font_path',
    'primary_font_path',
    'secondary_font_path',
    'graphic_divider',
    'brand_shape',
    'brand_borders',
    'brand_border_style',
    'brand_borders_2',
    'png_asset',
    'button_style',
    'social',
  ];

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The config installer.
   *
   * @var \Drupal\Core\Config\ConfigInstallerInterface
   */
  protected $configInstaller;

  /**
   * Set config manager as protected property.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Config\ConfigInstallerInterface $config_installer
   *   The config installer.
   */
  public function __construct(
    RouteMatchInterface $route_match,
    RequestStack $request_stack,
    ConfigFactoryInterface $config_factory,
    ConfigInstallerInterface $config_installer
  ) {
    $this->routeMatch = $route_match;
    $this->requestStack = $request_stack;
    $this->configFactory = $config_factory;
    $this->configInstaller = $config_installer;
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];
    if (!$this->isApplicableForConfigs($names)) {
      return $overrides;
    }

    $node = $this->getCurrentCampaignNode();
    if ($node) {
      $theme_configuration = $this->extractFromLayoutBuilder($node);
      if (!empty($theme_configuration)) {
        $overrides[self::THEME_CONFIG] = $this->getOverrideConfigOptions($theme_configuration);
      }
    }

    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'ConfigOverrider';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    $metadata = new CacheableMetadata();
    if (!$this->isApplicableForConfigs((array) $name)) {
      return $metadata;
    }

    $node = $this->getCurrentCampaignNode();
    if ($node) {
      $metadata->addCacheableDependency($node);
      $theme_config = $this->configFactory->get(self::THEME_CONFIG);
      $metadata->addCacheableDependency($theme_config);
    }

    return $metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject(
    $name,
    $collection = StorageInterface::DEFAULT_COLLECTION
  ) {
    return NULL;
  }

  /**
   * Extracts theme configuration fields from layout builder layout field.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return array|null
   *   The theme configuration array or null if it was not found.
   */
  private function extractFromLayoutBuilder(NodeInterface $node): ?array {
    if (!$node->hasField('layout_builder__layout')) {
      return [];
    }

    $theme_configuration = [];
    /** @var \Drupal\layout_builder\Field\LayoutSectionItemList $layoutBuilderField */
    $layoutBuilderField = $node->get('layout_builder__layout');
    /** @var \Drupal\layout_builder\Section[] $sections */
    $sections = $layoutBuilderField->getSections();

    foreach ($sections as $section) {
      $components = $section->getComponents();
      foreach ($components as $component) {
        try {
          $theme_configuration = $this->getThemeConfiguration($component);
        }
        catch (PluginException $e) {
          // Skip this component.
        }
        if (!empty($theme_configuration)) {
          return $theme_configuration;
        }
      }
    }
    return $theme_configuration;
  }

  /**
   * Extracts theme configuration from a layout builder block.
   *
   * @param \Drupal\layout_builder\SectionComponent $component
   *   Layout builder block.
   *
   * @return array|null
   *   Theme configuration.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function getThemeConfiguration(SectionComponent $component): ?array {
    $theme_configuration = NULL;

    $configuration = $component->get('configuration') ?? NULL;
    if ($configuration['id'] == self::THEME_CONFIGURATION_BLOCK_ID) {
      $theme_configuration = $configuration;
    }

    return $theme_configuration;
  }

  /**
   * Get override config options.
   *
   * @param array $theme_configuration
   *   Theme block configuration from campaign page.
   *
   * @return array
   *   Theme configuration.
   */
  private function getOverrideConfigOptions(
    array $theme_configuration = []
  ): array {
    if (empty($theme_configuration)) {
      return [];
    }
    $overrides = [];
    foreach (self::BLOCK_CONFIG_THEME_PARENT_KEYS as $key) {
      $theme_configuration = array_merge(
        $theme_configuration,
        $theme_configuration[$key] ?? []
      );
    }

    foreach (self::NEEDED_CONFIG_OVERRIDE as $item) {
      if (isset($theme_configuration[$item]) && !empty($theme_configuration[$item])) {
        if ($item == self::SOCIAL) {
          // Social links from layout builder.
          $this->fillOutSocialLinks($theme_configuration[self::SOCIAL]);
        };
        $overrides[$item] = $theme_configuration[$item];
      }
    }
    // Override logo.
    if (!empty($theme_configuration['logo_path'])) {
      $overrides['logo']['path'] = $theme_configuration['logo_path'];
    }
    return $overrides;
  }

  /**
   * Extracts the campaign node from url.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The current campaign node.
   */
  private function getCurrentCampaignNode(): ?NodeInterface {
    $current_route = $this->routeMatch;
    $request = $this->requestStack->getCurrentRequest();
    // Additional check request to avoid console errors.
    if ($current_route instanceof CurrentRouteMatch && $request instanceof Request) {
      /* @var $node \Drupal\node\NodeInterface */
      $node = $current_route->getParameter('node');
      if ($node instanceof NodeInterface && $node->bundle() === 'campaign') {
        return $node;
      }
    }
    return NULL;
  }

  /**
   * Checks if the override should run for the given config names.
   *
   * @param string[] $names
   *   The config names.
   *
   * @return bool
   *   The result.
   */
  private function isApplicableForConfigs(array $names): bool {
    return in_array(self::THEME_CONFIG, $names) &&
      !$this->configInstaller->isSyncing() &&
      !InstallerKernel::installationAttempted();
  }

  /**
   * Fill out social links.
   */
  private function fillOutSocialLinks(array &$social_layout) {
    // Social links from default theme.
    $social_theme_origin = $this->configFactory->getEditable(self::THEME_CONFIG)->getOriginal(self::SOCIAL, FALSE);
    if (!empty($social_theme_origin) && !empty($social_layout)) {
      $social_theme_origin_qty = count($social_theme_origin);
      $social_layout_qty = count($social_layout);
      if ($social_theme_origin_qty > $social_layout_qty) {
        $difference = $social_theme_origin_qty - $social_layout_qty;
        for ($i = 1; $i <= $difference; $i++) {
          $social_layout[] = NULL;
        }
      }
    }
  }

}
