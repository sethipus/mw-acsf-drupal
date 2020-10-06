<?php

namespace Drupal\mars_seo;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginBase;
use Drupal\mars_common\MediaHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Mars JSON LD Strategy plugins.
 */
abstract class JsonLdStrategyPluginBase extends ContextAwarePluginBase implements JsonLdStrategyInterface, ContainerFactoryPluginInterface {

  /**
   * Supported node types.
   *
   * @var string[]
   */
  protected $supportedBundles;

  /**
   * Mars Common Media Helper.
   *
   * @var \Drupal\mars_common\MediaHelper
   */
  protected $mediaHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_common.media_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MediaHelper $media_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->mediaHelper = $media_helper;
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

      return in_array($node->bundle(), $this->supportedBundles());
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
