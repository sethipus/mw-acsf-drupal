<?php

namespace Drupal\Tests\mars_lighthouse\Unit\Element;

use Drupal\mars_lighthouse\Element\LighthouseGalleryRadio;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\mars_lighthouse\Element\LighthouseGalleryRadio
 */
class LighthouseGalleryRadioTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_lighthouse\Element\LighthouseGalleryRadio
   */
  private $radioClass;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->radioClass = new LighthouseGalleryRadio(
      [],
      'lighthouse_gallery_radio',
      [
        'provider'    => 'test',
        'admin_label' => 'test_radio',
        'auto_select' => FALSE,
      ]);
  }

  /**
   * Test.
   *
   * @test
   * @covers \Drupal\mars_lighthouse\Element\LighthouseGalleryRadio::getInfo
   */
  public function testGetInfo() {
    $info = $this->radioClass->getInfo();
    $this->assertCount(6, $info);
    $array_keys = [
      '#input',
      '#default_value',
      '#process',
      '#pre_render',
      '#theme',
      '#title_display',
    ];
    foreach ($array_keys as $array_key) {
      $this->assertArrayHasKey($array_key, $info);
      if ($array_key === '#default_value') {
        $this->assertEmpty($info[$array_key]);
        continue;
      }
      $this->assertNotEmpty($info[$array_key]);
    }
    $this->assertEquals('lighthouse_gallery_item', $info['#theme']);
  }

}
