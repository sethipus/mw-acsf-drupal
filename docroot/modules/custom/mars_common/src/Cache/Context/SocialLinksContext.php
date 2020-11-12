<?php

namespace Drupal\mars_common\Cache\Context;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\layout_builder\SectionComponent;
use Drupal\mars_common\ConfigOverrides;
use Drupal\node\NodeInterface;

/**
 * Defines the SocialLinksContext service, for "per site" caching.
 *
 * Cache context ID: 'social_links'.
 */
class SocialLinksContext implements CacheContextInterface {

  /**
   * Cache context name for social links.
   */
  const CACHE_CONTEXT_NAME_SOCIAL_LINKS = 'social_links';

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new SocialLinksContext class.
   */
  public function __construct(RouteMatchInterface $route_match, ConfigFactoryInterface $config_factory) {
    $this->routeMatch = $route_match;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Social Links');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $node = $this->routeMatch->getParameter('node');
    $social = [];
    if ($node instanceof NodeInterface && $node->bundle() === 'campaign') {
      $theme_override_from_layout = $this->extractFromLayoutBuilder($node);
      if (!empty($theme_override_from_layout) && isset($theme_override_from_layout['social'])) {
        $social = $theme_override_from_layout['social'];
        foreach ($social as &$item) {
          unset($item['remove_social']);
        }
      }
    }
    else {
      $social = $this->configFactory->get(ConfigOverrides::THEME_CONFIG)->get(ConfigOverrides::SOCIAL);
    }
    $context = json_encode($social);
    return $context;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
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
    if ($configuration['id'] == ConfigOverrides::THEME_CONFIGURATION_BLOCK_ID) {
      $theme_configuration = $configuration;
    }

    return $theme_configuration;
  }

}
