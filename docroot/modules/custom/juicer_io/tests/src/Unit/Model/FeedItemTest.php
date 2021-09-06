<?php

namespace Drupal\Tests\juicer_io\Unit\Model;

use Drupal\juicer_io\Model\FeedItem;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for FeedItem class.
 *
 * @coversDefaultClass \Drupal\juicer_io\Model\FeedItem
 */
class FeedItemTest extends UnitTestCase {

  /**
   * Tests that item is converted to array correctly.
   *
   * @test
   */
  public function shouldReturnCorrectArray() {
    $image_url = 'image_url';
    $unformatted_message = 'unformatted_message';
    $link = 'link_value';
    $created_at = \DateTime::createFromFormat('Y-m-d', '2020-07-31');
    $expected_array = [
      'image' => [
        'url' => $image_url,
        'alt' => $unformatted_message,
      ],
      'link' => $link,
      'createdAt' => $created_at->format(\DateTime::RFC3339_EXTENDED),
    ];

    $item = new FeedItem($image_url, $unformatted_message, $link, $created_at);

    $this->assertEquals($expected_array, $item->toArray());
  }

  /**
   * Tests that image alt is truncated if it's too long.
   *
   * @test
   */
  public function shouldTestThatAltTextIsTruncated() {
    $unformatted_message = 'This is a string that is longer than 100 chars. Lorem Ipsum is simply dummy text of the printing and typesetting industry.';
    $expected_alt_text = 'This is a string that is longer than 100 chars. Lorem Ipsum is simply dummy text of the printing...';
    $item = new FeedItem(
      'image_url',
      $unformatted_message,
      'link_value',
      new \DateTime()
    );

    $alt_text = $item->getImageAlt();

    $this->assertEquals($expected_alt_text, $alt_text);
  }

  /**
   * Tests that image alt is removed of tags.
   *
   * @test
   */
  public function shouldTestThatTagsAreRemovedFromTheAltTag() {
    $unformatted_message = '<div><script></script><a>Test <span>text.</span></a>';
    $expected_alt_text = 'Test text.';
    $item = new FeedItem(
      'image_url',
      $unformatted_message,
      'link_value',
      new \DateTime()
    );

    $alt_text = $item->getImageAlt();

    $this->assertEquals($expected_alt_text, $alt_text);
  }

}
