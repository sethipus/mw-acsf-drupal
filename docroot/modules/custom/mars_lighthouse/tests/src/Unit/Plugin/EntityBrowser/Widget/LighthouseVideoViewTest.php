<?php

namespace Drupal\Tests\mars_lighthouse\Unit\Plugin\EntityBrowser\Widget;

use Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget\LighthouseVideoView;

/**
 * @coversDefaultClass \Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget\LighthouseVideoView
 * @group mars
 * @group mars_lighthouse
 */
class LighthouseVideoViewTest extends LighthouseViewBaseTest {

  const MEDIA_TYPE = 'video';

  /**
   * System under test.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget\LighthouseVideoView
   */
  protected $viewClass;

  /**
   * Media entity storage.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactoryMock;

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
    $this->viewClass = new LighthouseVideoView(
      $this->configuration,
      'lighthouse_video_view',
      $definitions,
      $this->eventDispatcherMock,
      $this->entityTypeManagerMock,
      $this->validationManagerMock,
      $this->lighthouseAdapterMock,
      $this->pageManagerMock,
      $this->currentRequestMock,
      $this->configFactoryMock,
    );
    \Drupal::setContainer($this->containerMock);
  }

  /**
   * Test current media type.
   *
   * @test
   *
   * @covers \Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget\LighthouseVideoView::getForm
   */
  public function testIsVideoView() {
    $this->assertEquals(static::MEDIA_TYPE, $this->viewClass->getMediaType());
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
      'lighthouse_video_view',
      [
        'provider'    => 'test',
        'admin_label' => 'test',
        'auto_select' => FALSE,
      ],
    );
  }

}
