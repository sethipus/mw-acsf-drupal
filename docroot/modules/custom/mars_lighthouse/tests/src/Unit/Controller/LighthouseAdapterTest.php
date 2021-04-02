<?php

namespace Drupal\Tests\mars_lighthouse\Unit\Controller;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_lighthouse\Controller\LighthouseAdapter;
use Drupal\mars_lighthouse\LighthouseClientInterface;
use Drupal\media\MediaInterface;
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

  const LIGHTHOUSE_SEARCH_MOCK_JSON_DATA = '[
    {
        "urls": {
            "001video_thumbnail": "id_0000000000000000000000000000001\/prid_001tnmd",
            "001orig": "id_0000000000000000000000000000001\/name_test1.gif\/test1.gif",
            "001default_video": "id_0000000000000000000000000000001",
            "001tnmd": "id_0000000000000000000000000000001\/prid_001tnmd",
            "001default": "id_0000000000000000000000000000001"
        },
        "origAssetId": "0000000000000000000000000000001",
        "assetId": "0000000000000000000000000000001",
        "assetName": "test 1",
        "subType": "Animated"
    },
    {
        "urls": {
            "001video_thumbnail": "id_0000000000000000000000000000002\/prid_001tnmd",
            "001orig": "id_0000000000000000000000000000003\/name_test2.gif\/test2.gif",
            "001default_video": "id_0000000000000000000000000000002",
            "001tnmd": "id_0000000000000000000000000000002\/prid_001tnmd",
            "001default": "id_0000000000000000000000000000002"
        },
        "origAssetId": "0000000000000000000000000000002",
        "assetId": "0000000000000000000000000000002",
        "assetName": "test 2",
        "subType": "Animated"
    },
    {
        "urls": {
            "001video_thumbnail": "id_0000000000000000000000000000003\/prid_001tnmd",
            "001orig": "id_0000000000000000000000000000003\/name_test3.gif\/test3.gif",
            "001default_video": "id_0000000000000000000000000000003",
            "001tnmd": "id_0000000000000000000000000000003\/prid_001tnmd",
            "001default": "id_0000000000000000000000000000003"
        },
        "origAssetId": "0000000000000000000000000000003",
        "assetId": "0000000000000000000000000000003",
        "assetName": "test 3",
        "subType": "Animated"
    },
    {
        "urls": {
            "001video_thumbnail": "id_0000000000000000000000000000004\/prid_001tnmd",
            "001orig": "id_0000000000000000000000000000004\/name_test4.gif\/test4.gif",
            "001default_video": "id_0000000000000000000000000000004",
            "001tnmd": "id_0000000000000000000000000000004\/prid_001tnmd",
            "001default": "id_0000000000000000000000000000004"
        },
        "origAssetId": "0000000000000000000000000000004",
        "subBrand": "N\/A",
        "assetId": "0000000000000000000000000000004",
        "assetName": "test4.gif",
        "subType": "GIFs"
    }
]';

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
   * File entity storage.
   *
   * @var \Drupal\media\MediaInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $mediaMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturnMap([
        ['file', $this->entityStorageMock],
        ['media', $this->entityStorageMock],
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
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::prepareImageExtension
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
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::getMediaDataList
   */
  public function testGetMediaDataList() {
    // Validate empty search.
    $count = 0;
    $response = $this->controller->getMediaDataList($count);
    $this->assertEquals(0, $count);
    $this->assertEmpty($response);
    // Validate assets list with found results.
    $this->lighthouseClientMock->expects($this->any())->method('search')->willReturn(
      json_decode(static::LIGHTHOUSE_SEARCH_MOCK_JSON_DATA, TRUE)
    );
    $response = $this->controller->getMediaDataList($count);
    $this->assertCount(4, $response);
    $asset_ids_to_verify = [
      '0000000000000000000000000000001',
      '0000000000000000000000000000002',
      '0000000000000000000000000000003',
      '0000000000000000000000000000004',
    ];
    $response_asset_ids = array_map(function ($asset) {
      return $asset['assetId'];
    }, $response);
    $this->assertArrayEquals($asset_ids_to_verify, $response_asset_ids);
  }

  /**
   * Test.
   *
   * @test
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::getMediaEntity
   */
  public function testGetMediaEntity() {
    $this->entityStorageMock
      ->expects($this->at(0))
      ->method('loadByProperties')
      ->with(['field_external_id' => 'a8c7cd6sfd7s876f0fsf98'])
      ->willReturn(
        [
          $this->mediaMock,
          $this->mediaMock,
        ]
      );

    $this->entityStorageMock
      ->expects($this->at(1))
      ->method('loadByProperties')
      ->with(['field_external_id' => 'a000000000000000000000'])
      ->willReturn(
        []
      );
    $media_entity = $this->controller->getMediaEntity('a8c7cd6sfd7s876f0fsf98');
    $this->assertNotEmpty($media_entity);
    $this->assertEmpty($this->controller->getMediaEntity('a000000000000000000000'));
  }

  /**
   * Test.
   *
   * @test
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::getBrands
   */
  public function testGetBrands() {
    $expected_options = ['' => '-- Any --'];
    $this->assertEquals($expected_options, $this->controller->getBrands());
  }

  /**
   * Test.
   *
   * @test
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::getMarkets
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
    $this->mediaMock = $this->createMock(MediaInterface::class);
  }

}
