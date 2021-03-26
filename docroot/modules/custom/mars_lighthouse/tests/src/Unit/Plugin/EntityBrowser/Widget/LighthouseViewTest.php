<?php

namespace Drupal\Tests\mars_lighthouse\Unit\Plugin\EntityBrowser\Widget;

use Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget\LighthouseView;

/**
 * @coversDefaultClass \Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget\LighthouseView
 * @group mars
 * @group mars_lighthouse
 */
class LighthouseViewTest extends LighthouseViewBaseTest {

  const MEDIA_TYPE = 'image';

  /**
   * System under test.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget\LighthouseView
   */
  protected $viewClass;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
      'auto_select' => FALSE,
    ];

    $this->viewClass = new LighthouseView(
      $this->configuration,
      'lighthouse_view',
      $definitions,
      $this->eventDispatcherMock,
      $this->entityTypeManagerMock,
      $this->validationManagerMock,
      $this->lighthouseAdapterMock,
      $this->pageManagerMock,
      $this->currentRequestMock
    );
    \Drupal::setContainer($this->containerMock);
  }

  /**
   * Test current media type.
   *
   * @test
   *
   * @covers \Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget\LighthouseView::getMediaType
   */
  public function testIsLightHouseImageView() {
    $this->assertEquals(self::MEDIA_TYPE, $this->viewClass->getMediaType());
  }

  /**
   * Test.
   *
   * @test
   */
  public function testShouldInstantiateProperly() {
    parent::testShouldInstantiateProperly();
    $this->viewClass::create(
      $this->containerMock,
      $this->configuration,
      'lighthouse_view',
      [
        'provider'    => 'test',
        'admin_label' => 'test',
        'auto_select' => FALSE,
      ],
    );
  }

}
