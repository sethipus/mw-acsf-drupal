<?php

namespace Drupal\Tests\juicer_io\Unit\Model;

use Drupal\juicer_io\Model\Feed;
use Drupal\juicer_io\Model\FeedItem;
use Drupal\juicer_io\Model\FeedReaderInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for Feed class.
 *
 * @coversDefaultClass \Drupal\juicer_io\Model\Feed
 */
class FeedTest extends UnitTestCase {

  /**
   * Mocked feed reader object.
   *
   * @var \Drupal\juicer_io\Model\FeedReaderInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $feedReader;

  /**
   * The data that the mocked reader will read.
   *
   * @var array
   */
  private $feedData;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->feedReader = $this->createMock(FeedReaderInterface::class);
    $this->feedReader->method('read')->willReturnReference($this->feedData);
    $this->feedData = [];
  }

  /**
   * Tests that the empty items list is given if the feed is empty.
   *
   * @test
   */
  public function shouldReturnEmptyArrayIfFeedDataIsEmpty() {
    $this->feedData = [];
    $feed = new Feed($this->feedReader);

    $items = $feed->getLatestItems();

    $this->assertEquals([], $items);
  }

  /**
   * Tests that the image url will be properly parsed from feed data.
   *
   * @test
   */
  public function itemsListShouldContainFeedItem() {
    $this->addItemToFeedData(
      'image_url',
      'message',
      'url',
      '2020-06-18T08:36:08.000-07:00'
    );
    $this->addItemToFeedData(
      'image_url2',
      'message2',
      'url2',
      '2022-06-18T08:36:08.000-07:00'
    );
    $feed = new Feed($this->feedReader);

    $items = $feed->getLatestItems();

    $this->assertCount(2, $items);
    $this->assertContainsOnlyInstancesOf(FeedItem::class, $items);
  }

  /**
   * Tests that the image url will be properly parsed from feed data.
   *
   * @test
   *
   * @covers \Drupal\juicer_io\Model\FeedItem
   */
  public function feedItemShouldHaveTheProperValues() {
    $this->addItemToFeedData(
      'image_url',
      'message',
      'url',
      '2020-06-18T08:36:08.000-07:00'
    );
    $feed = new Feed($this->feedReader);

    $items = $feed->getLatestItems();

    $item = reset($items);
    $this->assertEquals('image_url', $item->getImageUrl());
    $this->assertEquals('message', $item->getImageAlt());
    $this->assertEquals('url', $item->getLink());
    $expectedTime = \DateTime::createFromFormat(
      \DateTime::RFC3339_EXTENDED,
      '2020-06-18T08:36:08.000-07:00'
    );
    $this->assertEquals(
      $expectedTime,
      $item->getCreatedAt()
    );
  }

  /**
   * Add item to the feed.
   *
   * @param string $image
   *   The image value.
   * @param string $unformatted_message
   *   The unformatted message value.
   * @param string $url
   *   The url of the item.
   * @param string $created_at
   *   When was the item created.
   */
  protected function addItemToFeedData(
    string $image,
    string $unformatted_message,
    string $url,
    string $created_at
  ): void {
    $itemData = [
      'image' => $image,
      'unformatted_message' => $unformatted_message,
      'full_url' => $url,
      'external_created_at' => $created_at,
    ];
    $this->feedData['posts']['items'][] = $itemData;
  }

}
