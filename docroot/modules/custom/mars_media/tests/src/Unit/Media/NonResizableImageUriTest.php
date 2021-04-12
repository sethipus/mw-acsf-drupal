<?php

namespace Drupal\Tests\mars_media\Unit\Media;

use Drupal\mars_media\Media\NonResizableImageUri;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for NonResizableImageUri.
 *
 * @covers \Drupal\mars_media\Media\NonResizableImageUri
 */
class NonResizableImageUriTest extends UnitTestCase {

  /**
   * Test method.
   *
   * @test
   */
  public function resizeByWidthShouldBeNoop() {
    $url = '/relative/url/to/image.jpg';
    $url = new NonResizableImageUri($url);
    $before = (string) $url;
    $url->resizeByWidth(100);
    $after = (string) $url;

    $this->assertEquals($before, $after);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function resizeByHeightShouldBeNoop() {
    $url = '/relative/url/to/image.jpg';
    $url = new NonResizableImageUri($url);

    $before = (string) $url;
    $url->resizeByHeight(100);
    $after = (string) $url;

    $this->assertEquals($before, $after);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function resizeShouldBeNoop() {
    $url = '/relative/url/to/image.jpg';
    $url = new NonResizableImageUri($url);

    $before = (string) $url;
    $url->resize(100, 100);
    $after = (string) $url;

    $this->assertEquals($before, $after);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function resizeWithHighDprShouldBeNoop() {
    $url = '/relative/url/to/image.jpg';
    $url = new NonResizableImageUri($url);

    $before = (string) $url;
    $url->resizeWithHighDpr(100, 100, 2);
    $after = (string) $url;

    $this->assertEquals($before, $after);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function resizeWithGravityShouldBeNoop() {
    $url = '/relative/url/to/image.jpg';
    $url = new NonResizableImageUri($url);

    $before = (string) $url;
    $url->resizeWithGravity(100, 100);
    $after = (string) $url;

    $this->assertEquals($before, $after);
  }

}
