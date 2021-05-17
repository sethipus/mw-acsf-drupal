<?php

namespace Drupal\Tests\mars_media\Unit\Media;

use Drupal\mars_media\Media\CFResizableImageUri;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for LHResizeableImageUrl.
 *
 * @covers \Drupal\mars_media\Media\CFResizableImageUri
 * @covers \Drupal\mars_media\Media\CFResizePathFragment
 */
class LHResizableImageUriTest extends UnitTestCase {

  /**
   * Test method.
   *
   * @test
   */
  public function resizeByWidthShouldNotMutateTheOriginal() {
    $url = '/relative/url/to/image.jpg';
    $url = CFResizableImageUri::createFromUriString($url);
    $before = (string) $url;
    $resized = $url->resizeByWidth(100);
    $after = (string) $url;

    $this->assertEquals($before, $after);
    $this->assertNotSame($url, $resized);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function resizeByHeightShouldNotMutateTheOriginal() {
    $url = '/relative/url/to/image.jpg';
    $url = CFResizableImageUri::createFromUriString($url);

    $before = (string) $url;
    $resized = $url->resizeByHeight(100);
    $after = (string) $url;

    $this->assertEquals($before, $after);
    $this->assertNotSame($url, $resized);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function resizeShouldNotMutateTheOriginal() {
    $url = '/relative/url/to/image.jpg';
    $url = CFResizableImageUri::createFromUriString($url);

    $before = (string) $url;
    $resized = $url->resize(100, 100);
    $after = (string) $url;

    $this->assertEquals($before, $after);
    $this->assertNotSame($url, $resized);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function shouldReturnTheUnchangedUrlIfCastedToString() {
    $relativeUrl = '/relative/url/to/image.jpg';
    $url = CFResizableImageUri::createFromUriString($relativeUrl);

    $this->assertEquals($relativeUrl, (string) $url);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function shouldAddResizeWidthToRelativeUrl() {
    $relativeUrl = '/relative/url/to/image.jpg';
    $expectedUrl = '/cdn-cgi/image/width=315,f=auto,quality=75/relative/url/to/image.jpg';
    $url = CFResizableImageUri::createFromUriString($relativeUrl);

    $url = $url->resizeByWidth('315');

    $this->assertEquals($expectedUrl, (string) $url);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function shouldAddResizeHeightToRelativeUrl() {
    $relativeUrl = '/relative/url/to/image.jpg';
    $expectedUrl = '/cdn-cgi/image/height=315,f=auto,quality=75/relative/url/to/image.jpg';
    $url = CFResizableImageUri::createFromUriString($relativeUrl);

    $url = $url->resizeByHeight('315');

    $this->assertEquals($expectedUrl, (string) $url);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function shouldAddResizeHeightAndWidthToRelativeUrl() {
    $relativeUrl = '/relative/url/to/image.jpg';
    $expectedUrl = '/cdn-cgi/image/width=100,height=300,f=auto,quality=75/relative/url/to/image.jpg';
    $url = CFResizableImageUri::createFromUriString($relativeUrl);

    $url = $url->resize('100', '300');

    $this->assertEquals($expectedUrl, (string) $url);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function shouldAddResizeHeightAndWidthWithDprToRelativeUrl() {
    $relativeUrl = '/relative/url/to/image.jpg';
    $expectedUrl = '/cdn-cgi/image/width=100,height=300,dpr=2,f=auto,quality=75/relative/url/to/image.jpg';
    $url = CFResizableImageUri::createFromUriString($relativeUrl);

    $url = $url->resizeWithHighDpr('100', '300', '2');

    $this->assertEquals($expectedUrl, (string) $url);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function shouldAddResizeHeightAndWidthWithGravityToRelativeUrl() {
    $relativeUrl = '/relative/url/to/image.jpg';
    $expectedUrl = '/cdn-cgi/image/width=100,height=300,fit=cover,g=auto,f=auto,quality=75/relative/url/to/image.jpg';
    $url = CFResizableImageUri::createFromUriString($relativeUrl);

    $url = $url->resizeWithGravity('100', '300');

    $this->assertEquals($expectedUrl, (string) $url);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function shouldAddResizeWidthToAbsoluteUrl() {
    $absolutePath = 'http://domain.hu/relative/url/to/image.jpg';
    $expectedUrl = 'http://domain.hu/cdn-cgi/image/width=315,f=auto,quality=75/relative/url/to/image.jpg';
    $url = CFResizableImageUri::createFromUriString($absolutePath);

    $url = $url->resizeByWidth('315');

    $this->assertEquals($expectedUrl, (string) $url);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function shouldAddResizeHeightToAbsoluteUrl() {
    $absolutePath = 'http://domain.hu/relative/url/to/image.jpg';
    $expectedUrl = 'http://domain.hu/cdn-cgi/image/height=315,f=auto,quality=75/relative/url/to/image.jpg';
    $url = CFResizableImageUri::createFromUriString($absolutePath);

    $url = $url->resizeByHeight('315');

    $this->assertEquals($expectedUrl, (string) $url);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function shouldAddResizeWidthAndHeightToAbsoluteUrl() {
    $absolutePath = 'http://domain.hu/relative/url/to/image.jpg';
    $expectedUrl = 'http://domain.hu/cdn-cgi/image/width=100,height=300,f=auto,quality=75/relative/url/to/image.jpg';
    $url = CFResizableImageUri::createFromUriString($absolutePath);

    $url = $url->resize('100', '300');

    $this->assertEquals($expectedUrl, (string) $url);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function fromMultipleResizesOnlyTheLastShouldBeApplied() {
    $absolutePath = 'http://domain.hu/relative/url/to/image.jpg';
    $expectedUrl = 'http://domain.hu/cdn-cgi/image/height=10,f=auto,quality=75/relative/url/to/image.jpg';
    $url = CFResizableImageUri::createFromUriString($absolutePath);

    $url = $url
      ->resize('100', '300')
      ->resizeByWidth('50')
      ->resizeByHeight('10');

    $this->assertEquals($expectedUrl, (string) $url);
  }

}
