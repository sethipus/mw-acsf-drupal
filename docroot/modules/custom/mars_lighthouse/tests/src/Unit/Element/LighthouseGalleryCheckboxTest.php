<?php

namespace Drupal\Tests\mars_lighthouse\Unit\Element;

use Drupal\mars_lighthouse\Element\LighthouseGalleryCheckbox;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\mars_lighthouse\Element\LighthouseGalleryCheckbox
 */
class LighthouseGalleryCheckboxTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_lighthouse\Element\LighthouseGalleryCheckbox
   */
  private $checkboxClass;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->checkboxClass = new LighthouseGalleryCheckbox(
      [],
      'lighthouse_gallery_checkbox',
      [
        'provider'    => 'test',
        'admin_label' => 'test_checkbox',
        'auto_select' => FALSE,
      ]);
  }

  /**
   * Test.
   *
   * @test
   * @covers \Drupal\mars_lighthouse\Element\LighthouseGalleryCheckbox::getInfo
   */
  public function testGetInfo() {
    $info = $this->checkboxClass->getInfo();
    $this->assertCount(6, $info);
    $array_keys = [
      '#input',
      '#return_value',
      '#process',
      '#pre_render',
      '#theme',
      '#title_display',
    ];
    foreach ($array_keys as $array_key) {
      $this->assertArrayHasKey($array_key, $info);
      $this->assertNotEmpty($info[$array_key]);
    }
    $this->assertEquals('lighthouse_gallery_item', $info['#theme']);
  }

}
