<?php

namespace Drupal\Tests\juicer_io\Unit\Model;

use Drupal\juicer_io\Model\Feed;
use Drupal\juicer_io\Model\FeedConfigurationInterface;
use Drupal\juicer_io\Model\FeedFactory;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for feed factory.
 */
class FeedFactoryTest extends TestCase {

  /**
   * Tests that the factory creates feed objects.
   *
   * @test
   */
  public function shouldCreateFeedFromConfig() {
    $guzzle_service = $this->createMock(Client::class);
    $factory = new FeedFactory($guzzle_service);
    $config = $this->createMock(FeedConfigurationInterface::class);

    $feed = $factory->initFeed($config);

    $this->assertInstanceOf(Feed::class, $feed);
  }

}
