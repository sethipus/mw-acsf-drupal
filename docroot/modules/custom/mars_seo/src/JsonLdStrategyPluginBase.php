<?php

namespace Drupal\mars_seo;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginTrait;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\mars_media\MediaHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Mars JSON LD Strategy plugins.
 */
abstract class JsonLdStrategyPluginBase extends PluginBase implements JsonLdStrategyInterface, ContainerFactoryPluginInterface {

  use ContextAwarePluginTrait;

  /**
   * Supported node types.
   *
   * @var string[]
   */
  protected $supportedBundles;

  /**
   * Mars Common Media Helper.
   *
   * @var \Drupal\mars_media\MediaHelper
   */
  protected $mediaHelper;

  /**
   * Url generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_media.media_helper'),
      $container->get('url_generator'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MediaHelper $media_helper,
    UrlGeneratorInterface $url_generator,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->mediaHelper = $media_helper;
    $this->urlGenerator = $url_generator;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable() {
    try {
      /** @var \Drupal\node\NodeInterface $node */
      $node = $this->getContextValue('node');

      return empty($this->supportedBundles()) || in_array($node->bundle(), $this->supportedBundles());
    }
    catch (PluginException $e) {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function supportedBundles() {
    return $this->supportedBundles ?? [];
  }

  /**
   * {@inheritdoc}
   */
  abstract public function getStructuredData();

}
