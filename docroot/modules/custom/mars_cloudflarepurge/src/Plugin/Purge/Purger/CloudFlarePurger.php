<?php

namespace Drupal\mars_cloudflarepurge\Plugin\Purge\Purger;

use Drupal\mars_cloudflarepurge\PurgeCloudFlareCache;
use Drupal\mars_cloudflarepurge\EventSubscriber\CloudFlareCacheTagHeaderGenerator;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\purge\Plugin\Purge\Purger\PurgerBase;
use Drupal\purge\Plugin\Purge\Purger\PurgerInterface;
use Drupal\purge\Plugin\Purge\Invalidation\InvalidationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;
use Drupal\node\NodeInterface;

/**
 * CloudFlare purger.
 *
 * @PurgePurger(
 *   id = "customcloudflare",
 *   label = @Translation("CloudFlare Purger"),
 *   description = @Translation("Custom Purger for CloudFlare."),
 *   types = {"tag", "url", "everything"},
 *   multi_instance = FALSE,
 * )
 */
class CloudFlarePurger extends PurgerBase implements PurgerInterface {
  // Max Number of.
  const MAX_TAG_PURGES_PER_REQUEST = 30;

  // Purge Request type.
  const REQUEST_TYPE_PURGE_TAG = 'TAG';
  const REQUEST_TYPE_PURGE_URL = 'FILE/URL';
  const REQUEST_TYPE_PURGE_EVERYTHING = 'EVERYTHING';

  /**
   * The settings configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('logger.factory')->get('mars_cloudflarepurge'),
    );
  }

  /**
   * Constructs a \Drupal\Component\Plugin\CloudFlarePurger.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   *
   * @throws \LogicException
   *   Thrown if $configuration['id'] is missing, see Purger\Service::createId.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $config_factory->get('cloudflarepurge.settings');
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function routeTypeToMethod($type) {
    $methods = [
      'everything' => 'invalidate',
      'tag'  => 'invalidate',
      'url'  => 'invalidate',
    ];

    return isset($methods[$type]) ? $methods[$type] : 'invalidate';
  }

  /**
   * {@inheritdoc}
   */
  public function invalidate(array $invalidations) {
    $chunks = array_chunk($invalidations, self::MAX_TAG_PURGES_PER_REQUEST);
    $has_invalidations = count($invalidations) > 0;
    $user = \Drupal::currentUser()->isAuthenticated();
    if (!$user) {
      $has_invalidations = FALSE;
    }
    if (!$has_invalidations) {
      foreach ($invalidations as $invalidation) {
        $invalidation->setState(InvalidationInterface::NOT_SUPPORTED);
      }
      $this->logger->warning('No Authenticated user or Invalidate found to purge in cloudflare purger');
      return;
    }
    foreach ($chunks as $chunk) {
      $this->purgeChunk($chunk);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasRuntimeMeasurement() {
    return TRUE;
  }

  /**
   * Purges a chunk of tags.
   *
   * Integration point between purge and CloudFlare.  Purge requires state
   * tracking on each item purged.  This function provides that accounting and
   * calls CloudflareCurl Request.
   *
   * CloudFlare only allows us to purge 30 tags at once.
   *
   * @param array $invalidations
   *   Chunk of purge module invalidation objects to purge via CloudFlare.
   */
  private function purgeChunk(array &$invalidations) {
    $zoneId = $this->config->get('zone_id');
    $authorization = $this->config->get('authorization');

    $targets_to_purge = [];

    // This method is unfortunately a bit verbose due to the fact that we
    // need to update the purge states as we proceed.
    foreach ($invalidations as $invalidation) {
      $invalidation->setState(InvalidationInterface::PROCESSING);
      $targets_to_purge[] = $invalidation->getExpression();
    }

    try {
      $invalidation_type = $invalidations[0]->getPluginId();
      // Cloudflare purge for specific tags.
      if ($invalidation_type == 'tag') {
        // Also invalidate the cache tags as hashes, to automatically also work.
        // For responses that exceed CloudFlare's Cache-Tag header limit.
        // Checking if current request is node or not.
        $node = \Drupal::routeMatch()->getParameter('node');
        if ($node instanceof NodeInterface) {
          $node_url = $node->toUrl()->toString();
          $targets_to_purge = ["node:" . $node->Id(), $node_url];
        }
        $targets_to_purge = CloudFlareCacheTagHeaderGenerator::cacheTagsToHashes($targets_to_purge);
        $this->purgeTags($zoneId, $authorization, $targets_to_purge);
      }
      // Cloudflare purge for specific URL.
      elseif ($invalidation_type == 'url') {
        $this->purgeIndividualFiles($zoneId, $authorization, $targets_to_purge);
      }

      // Cloudflare purge for everythings.
      elseif ($invalidation_type == 'everything') {
        $this->purgeAllFiles($zoneId, $authorization);
      }

      foreach ($invalidations as $invalidation) {
        $invalidation->setState(InvalidationInterface::SUCCEEDED);
      }
    }
    catch (\Exception $e) {
      foreach ($invalidations as $invalidation) {
        $invalidation->setState(InvalidationInterface::FAILED);
      }

      // We only want to log a single watchdog error per request. This prevents
      // the log from being flooded.
      $this->logger->error('Cloudflare purger : Getting error' . $e->getMessage());
    }
  }

  /**
   * Purges tags from CloudFlare.
   *
   * @param string $zone_id
   *   The zoneId for the zone to access.
   * @param string $authorization
   *   The authorization for cloudflareAPI access.
   * @param array $tags
   *   The list of tags to purge.
   */
  private function purgeTags($zone_id, $authorization, array $tags) {
    $this->logger->info('Cloudflare purger: purgeTags function is calling');
    $data = json_encode(['tags' => $tags]);
    PurgeCloudFlareCache::makeRequest(self::REQUEST_TYPE_PURGE_TAG, $zone_id, $authorization, $data);
  }

  /**
   * Purges specific url from CloudFlare.
   *
   * @param string $zone_id
   *   The zoneId for the zone to access.
   * @param string $authorization
   *   The authorization for cloudflareAPI access.
   * @param array $files
   *   Files to purge.
   */
  private function purgeIndividualFiles($zone_id, $authorization, array $files) {
    $this->logger->info('Cloudflare purger: purgeIndividualFiles function is calling');
    $data = json_encode(['files' => $files]);
    PurgeCloudFlareCache::makeRequest(self::REQUEST_TYPE_PURGE_URL, $zone_id, $authorization, $data);
  }

  /**
   * Purges everything from CloudFlare.
   *
   * @param string $zone_id
   *   The zoneId for the zone to access.
   * @param string $authorization
   *   The authorization for cloudflareAPI access.
   */
  private function purgeAllFiles($zone_id, $authorization) {
    $this->logger->info('Cloudflare purger: purgeAllFiles function is calling');
    $data = json_encode(['purge_everything' => TRUE]);
    PurgeCloudFlareCache::makeRequest(self::REQUEST_TYPE_PURGE_EVERYTHING, $zone_id, $authorization, $data);
  }

}
