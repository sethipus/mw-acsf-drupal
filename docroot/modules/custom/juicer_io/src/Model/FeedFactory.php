<?php

namespace Drupal\juicer_io\Model;

use GuzzleHttp\ClientInterface;
use Drupal\juicer_io\Model\Impl\GuzzleFeedReader;

/**
 * Factory class to create feed objects from configurations.
 */
class FeedFactory {

  /**
   * The guzzle service.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  private $guzzleClient;

  /**
   * FeedFactory constructor.
   *
   * @param \GuzzleHttp\ClientInterface $guzzleClient
   *   The guzzle service.
   */
  public function __construct(ClientInterface $guzzleClient) {
    $this->guzzleClient = $guzzleClient;
  }

  /**
   * Creates a feed object based on the given config.
   *
   * @param \Drupal\juicer_io\Model\FeedConfigurationInterface $config
   *   The configuration for the feed.
   *
   * @return \Drupal\juicer_io\Model\Feed
   *   The created feed object.
   */
  public function initFeed(FeedConfigurationInterface $config): Feed {
    return new Feed(new GuzzleFeedReader($config, $this->guzzleClient));
  }

}
