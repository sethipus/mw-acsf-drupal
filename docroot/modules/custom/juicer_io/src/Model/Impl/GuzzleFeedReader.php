<?php

namespace Drupal\juicer_io\Model\Impl;

use Drupal\juicer_io\Model\FeedConfigurationInterface;
use Drupal\juicer_io\Model\FeedException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use Drupal\juicer_io\Model\FeedReaderInterface;
use Psr\Http\Message\UriInterface;

/**
 * Guzzle based feed reader implementation.
 */
class GuzzleFeedReader implements FeedReaderInterface {

  /**
   * The feed configuration object.
   *
   * @var \Drupal\juicer_io\Entity\FeedConfiguration
   */
  private $configuration;

  /**
   * Guzzle client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  private $client;

  /**
   * Guzzle based feed readers constructor.
   *
   * @param \Drupal\juicer_io\Model\FeedConfigurationInterface $configuration
   *   The feed configuration.
   * @param \GuzzleHttp\ClientInterface $client
   *   The guzzle client.
   */
  public function __construct(
    FeedConfigurationInterface $configuration,
    ClientInterface $client
  ) {
    $this->client = $client;
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function read(array $types, ?int $max): array {
    $uri = $this->createUri($types, $max);

    try {
      $response = $this->client->request('GET', $uri);
    }
    catch (GuzzleException $e) {
      throw FeedException::wrap(
        $e,
        'Failed to get feed data.'
      );
    }

    try {
      $feedData = json_decode(
        $response->getBody(),
        TRUE,
        512,
        JSON_THROW_ON_ERROR
      );
    }
    catch (\JsonException $e) {
      throw FeedException::wrap(
        $e,
        'Error happened during decoding the feed data.'
      );
    }

    return $feedData;
  }

  /**
   * Creates an uri with the given parameters.
   *
   * @param array $types
   *   Array of allowed types. No restriction if empty.
   * @param int|null $max
   *   Number of max items. If null then unlimited.
   *
   * @return \Psr\Http\Message\UriInterface
   *   The generated uri.
   */
  private function createUri(array $types, ?int $max): UriInterface {
    $feedUri = new Uri($this->configuration->getUrl());
    if (!empty($types)) {
      $feedUri = Uri::withQueryValue($feedUri, 'filter', implode(',', $types));
    }
    if ($max) {
      $feedUri = Uri::withQueryValues($feedUri, [
        'per' => $max,
        'page' => 1,
      ]);
    }
    return $feedUri;
  }

}
