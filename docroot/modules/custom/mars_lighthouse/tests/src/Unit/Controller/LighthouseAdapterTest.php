<?php

namespace Drupal\Tests\mars_lighthouse\Unit\Controller;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_lighthouse\Controller\LighthouseAdapter;
use Drupal\mars_lighthouse\LighthouseClientInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_lighthouse\Controller\LighthouseAdapter
 * @group mars
 * @group mars_lighthouse
 */
class LighthouseAdapterTest extends UnitTestCase {

  const SAMPLE_DATA = [
    'urls' => [
      '001orig' => 'image.png',
    ],
    'assetName' => 'LH image 1',
    'assetId' => 'a8c7cd6sfd7s876f0fsf98',
  ];

  const EXPECTED_SAMPLE_DATA = [
    'urls' => [
      '001orig' => 'image.png.jpeg',
    ],
    'assetName' => 'LH image 1.jpeg',
    'assetId' => 'a8c7cd6sfd7s876f0fsf98',
  ];

  /**
   * System under test.
   *
   * @var \Drupal\mars_lighthouse\Controller\LighthouseAdapter
   */
  private $controller;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Lighthouse client.
   *
   * @var \Drupal\mars_lighthouse\LighthouseClientInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $lighthouseClientMock;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cacheMock;

  /**
   * Media entity storage.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $entityTypeManagerMock;

  /**
   * Media entity storage.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactoryMock;

  /**
   * File entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $entityStorageMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    $this->entityStorageMock->method('loadByProperties')->willReturnMap([
      [],
    ]);
    $this->entityTypeManagerMock->method('getStorage')->willReturnMap([
      ['media', $this->entityStorageMock],
      ['file', $this->entityStorageMock],
    ]);
    $this->containerMock->set('entity_type.manager', $this->entityTypeManagerMock);
    \Drupal::setContainer($this->containerMock);
    $this->controller = new LighthouseAdapter(
      $this->lighthouseClientMock,
      $this->cacheMock,
      $this->configFactoryMock,
      $this->entityTypeManagerMock
    );
  }

  /**
   * Test.
   *
   * @test
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(4))
      ->method('get')
      ->willReturnMap(
        [
          [
            'lighthouse.client',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->lighthouseClientMock,
          ],
          [
            'cache.default',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->cacheMock,
          ],
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configFactoryMock,
          ],
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
        ],
      );
    $this->controller::create($this->containerMock);
  }

  /**
   * Test.
   *
   * @test
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::prepareImageExtension()
   */
  public function testPrepareImageExtension() {
    $sample_data = self::SAMPLE_DATA;
    $this->controller->prepareImageExtension($sample_data);
    $this->assertArrayEquals(self::EXPECTED_SAMPLE_DATA, $sample_data);
  }

  /**
   * Test.
   *
   * @test
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::getMediaDataList()
   */
  public function testGetMediaDataList() {
    $count = 0;
    $response = $this->controller->getMediaDataList($count);
    $this->assertEquals(0, $count);
    $this->assertEmpty($response);
  }

  /**
   * Test.
   *
   * @test
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::getMediaEntity()
   */
  public function testGetMediaEntity() {
    $this->assertEquals(NULL, $this->controller->getMediaEntity('a8c7cd6sfd7s876f0fsf98'));
  }

  /**
   * Test.
   *
   * @test
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::getBrands()
   */
  public function testGetBrands() {
    $expected_options = ['' => '-- Any --'];
    $this->assertEquals($expected_options, $this->controller->getBrands());
  }

  /**
   * Test.
   *
   * @test
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::getMarkets()
   */
  public function testGetMarkets() {
    $expected_options = ['' => '-- Any --'];
    $this->assertEquals($expected_options, $this->controller->getMarkets());
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->lighthouseClientMock = $this->createMock(LighthouseClientInterface::class);
    $this->cacheMock = $this->createMock(CacheBackendInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
  }

}
