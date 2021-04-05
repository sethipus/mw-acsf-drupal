<?php

namespace Drupal\Tests\mars_lighthouse\Unit\Controller;

use Drupal\Component\Serialization\Yaml;
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

  const LIGHTHOUSE_SINGLE_ASSET_JSON_DATA = '{
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
  }';

  const BRANDS_MOCK_DATA = [
    'dove' => 'Dove',
    'twix' => 'Twix',
    'bens' => 'Ben`s',
    'galaxy' => 'Galaxy',
  ];

  const MARKETS_MOCK_DATA = [
    'usa' => 'United States',
    'uk' => 'United Kingdom',
    'ua' => 'Ukraine',
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
    $config_path = '/modules/custom/mars_lighthouse/config/install/mars_lighthouse.mapping.yml';
    if (!strstr(getcwd(), 'docroot')) {
      $config_path = '/docroot' . $config_path;
    }
    $this->configFactoryMock = $this->getConfigFactoryStub([
      'mars_lighthouse.mapping' => Yaml::decode(file_get_contents(getcwd() . $config_path)),
    ]);
    $this->containerMock->set('entity_type.manager', $this->entityTypeManagerMock);
    $this->containerMock->set('config.factory', $this->configFactoryMock);
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
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::changeExtension
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
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::prepareMediaDataList
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
    $asset_names_to_verify = [
      'test 1',
      'test 2',
      'test 3',
      'test4.gif',
    ];
    $response_asset_names = array_map(function ($asset) {
      return $asset['name'];
    }, $response);
    $this->assertArrayEquals($asset_names_to_verify, $response_asset_names);
  }

  /**
   * Test.
   *
   * @test
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::getMediaEntity
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::createMediaEntity
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::createFileEntity
   */
  public function testGetMediaEntity() {
    // Validate method returns existing media entity.
    $this->entityStorageMock
      ->expects($this->at(0))
      ->method('loadByProperties')
      ->with(['field_external_id' => 'a8c7cd6sfd7s876f0fsf98'])
      ->willReturn(
        [
          $this->mediaMock,
          ['test' => 'test'],
        ]
      );
    $this->entityStorageMock
      ->expects($this->at(1))
      ->method('loadByProperties')
      ->with(['field_external_id' => 'a000000000000000000000'])
      ->willReturn(
        []
      );
    $existing_media_entity = $this->controller->getMediaEntity('a8c7cd6sfd7s876f0fsf98');
    $this->assertNotEmpty($existing_media_entity);
    $this->assertInstanceOf(MediaInterface::class, $existing_media_entity);

    // Validate method returns empty result.
    $this->assertEmpty($this->controller->getMediaEntity('a000000000000000000000'));

    // Validate method returns a generated media entity.
    $this->lighthouseClientMock
      ->expects($this->any())
      ->method('getAssetById')
      ->willReturn(json_decode(static::LIGHTHOUSE_SINGLE_ASSET_JSON_DATA, TRUE));
    $entity_mock = $this->createMock(MediaInterface::class);
    $entity_mock->expects($this->any())->method('id')->willReturn(1);
    $this->entityStorageMock
      ->expects($this->any())
      ->method('create')
      ->willReturn(
        $entity_mock
      );
    $generated_media_entity = $this->controller->getMediaEntity('a000000000000000000001');
    $this->assertNotEmpty($generated_media_entity);
    $this->assertInstanceOf(MediaInterface::class, $generated_media_entity);
  }

  /**
   * Test.
   *
   * @test
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::getBrands
   */
  public function testGetBrands() {
    // Validate method returns default select options.
    $expected_options = ['' => '-- Any --'];
    $this->assertEquals($expected_options, $this->controller->getBrands());

    // Validate method returns options from client.
    $this->lighthouseClientMock
      ->expects($this->any())
      ->method('getBrands')
      ->willReturn(static::BRANDS_MOCK_DATA);
    $rebuilt_mock_array = [];
    foreach (static::BRANDS_MOCK_DATA as $v) {
      $rebuilt_mock_array[$v] = $v;
    }
    $this->assertEquals(array_merge($expected_options, $rebuilt_mock_array), $this->controller->getBrands());
  }

  /**
   * Test.
   *
   * @test
   * @covers \Drupal\mars_lighthouse\Controller\LighthouseAdapter::getMarkets
   */
  public function testGetMarkets() {
    // Validate method returns default select options.
    $expected_options = ['' => '-- Any --'];
    $this->assertEquals($expected_options, $this->controller->getMarkets());

    // Validate method returns options from client.
    $this->lighthouseClientMock
      ->expects($this->any())
      ->method('getMarkets')
      ->willReturn(static::MARKETS_MOCK_DATA);
    $rebuilt_mock_array = [];
    foreach (static::MARKETS_MOCK_DATA as $v) {
      $rebuilt_mock_array[$v] = $v;
    }
    $this->assertEquals(array_merge($expected_options, $rebuilt_mock_array), $this->controller->getMarkets());
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
