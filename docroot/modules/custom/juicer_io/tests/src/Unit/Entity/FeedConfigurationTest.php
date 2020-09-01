<?php

declare(strict_types=1);

namespace Drupal\Tests\juicer_io\Unit\Entity;

use Drupal\juicer_io\Entity\FeedConfiguration;
use Drupal\Tests\UnitTestCase;

/**
 * Test for the FeedConfig entity.
 *
 * @coversDefaultClass \Drupal\juicer_io\Entity\FeedConfiguration
 */
class FeedConfigurationTest extends UnitTestCase {

  /**
   * Test getUrl returns the correct value.
   *
   * @test
   */
  public function shouldReturnCorrectUrl() {
    $expected_url_value = 'https://www.juicer.io/api/feeds/feed_id_value';
    $feed_id = 'feed_id_value';

    $feed_configuration = new FeedConfiguration(
      [
        'feed_id' => $feed_id,
      ],
      'juicer_io_feed'
    );

    $url = $feed_configuration->getUrl();
    $this->assertEquals($expected_url_value, $url);
  }

}
