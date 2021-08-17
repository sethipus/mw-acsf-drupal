<?php

namespace Drupal\Tests\salsify_integration\Unit;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\salsify_integration\MulesoftConnector;
use Drupal\salsify_integration\Salsify;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\salsify_integration\Salsify
 * @group mars
 * @group salsify_integration
 */
class SalsifyTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\Salsify
   */
  private $salsify;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translationMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityFieldManagerInterface
   */
  private $fieldManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandlerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ImmutableConfig
   */
  private $configMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityInterface
   */
  private $entityMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Logger\LoggerChannelInterface
   */
  private $loggerChannelMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Cache\CacheBackendInterface
   */
  private $cacheBackendMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Queue\QueueFactory
   */
  private $queueFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\MulesoftConnector
   */
  private $mulesoftConnectorMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\GuzzleHttp\Client
   */
  private $clientMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\GuzzleHttp\Psr7\Response
   */
  private $responseMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Http\Message\StreamInterface
   */
  private $streamMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\field\Entity\FieldConfig
   */
  private $fieldConfigMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->salsify = new Salsify(
      $this->loggerFactoryMock,
      $this->configFactoryMock,
      $this->entityTypeManagerMock,
      $this->fieldManagerMock,
      $this->cacheBackendMock,
      $this->queueFactoryMock,
      $this->moduleHandlerMock,
      $this->mulesoftConnectorMock,
      $this->clientMock
    );
  }

  /**
   * Test.
   */
  public function testShouldGetProductData() {
    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('token');

    $this->clientMock
      ->expects($this->once())
      ->method('__call')
      ->willReturn($this->responseMock);

    $this->responseMock
      ->expects($this->once())
      ->method('getBody')
      ->willReturn($this->streamMock);

    $this->streamMock
      ->expects($this->once())
      ->method('__toString')
      ->willReturn('response');

    $this->mulesoftConnectorMock
      ->expects($this->once())
      ->method('transformData')
      ->willReturn([
        'attributes' => [
          [
            'salsify:id' => 'GTIN',
            'salsify:updated_at' => 123123,
            'salsify:entity_types' => ['products'],
          ],
          [
            'salsify:id' => 'Trade Item Description',
            'salsify:updated_at' => 1231233,
            'salsify:entity_types' => ['products'],
          ],
        ],
        'attribute_values' => [
          [
            'salsify:attribute_id' => 'Trade Item Description',
            'salsify:id' => 'TWIX CARAMEL KING SIZE 3.02 OUNCE',
            'salsify:name' => 'TWIX CARAMEL KING SIZE 3.02 OUNCE',
            'salsify:updated_at' => 1231233,
          ],
        ],
        'digital_assets' => [
          'salsify:id' => '00040000004059_C1N1.tif',
          'salsify:url' => 'https://tes.t/test',
        ],
        'products' => [
          [
            'salsify:id' => '00040000004059',
            'GTIN' => '00040000004059',
            'Trade Item Description' => 'TWIX CARAMEL KING SIZE 3.02 OUNCE',
          ],
        ],
        'mapping' => [],
        'market' => 'US',
      ]);

    $this->moduleHandlerMock
      ->expects($this->once())
      ->method('alter');

    $this->cacheBackendMock
      ->expects($this->once())
      ->method('set');

    $product_data = $this->salsify->getProductData();
    $this->assertNotEmpty($product_data);
    $this->assertIsArray($product_data);
  }

  /**
   * Test.
   */
  public function testShouldGetContentTypeFields() {

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'entity_field.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->fieldManagerMock,
          ],
        ]
      );

    $this->fieldManagerMock
      ->expects($this->once())
      ->method('getFieldDefinitions')
      ->willReturn([$this->fieldConfigMock]);

    $fields = $this->salsify::getContentTypeFields('node', 'product');
    $this->assertInstanceOf(FieldConfig::class, reset($fields));
  }

  /**
   * Test.
   */
  public function testShouldGetFieldMappings() {

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configFactoryMock,
          ],
        ]
      );

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->configMock);

    $this->configFactoryMock
      ->expects($this->any())
      ->method('listAll')
      ->willReturn(['config1']);

    $this->configFactoryMock
      ->expects($this->any())
      ->method('getEditable')
      ->willReturn($this->configMock);

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('MY CUSTOM FIELD');

    $this->configMock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'product',
        'method' => 'manual',
        'entity_type' => 'node',
        'salsify_data_type' => 'complex',
      ]);

    $fields = $this->salsify::getFieldMappings([
      'entity_type' => 'node',
      'bundle' => 'product',
      'method' => 'manual',
    ], 'salsify:id');
    $this->assertIsArray($fields);
    $this->assertNotEmpty($fields);
  }

  /**
   * Test.
   */
  public function testShouldCreateFieldMapping() {

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configFactoryMock,
          ],
        ]
      );

    $this->configFactoryMock
      ->expects($this->once())
      ->method('getEditable')
      ->willReturn($this->configMock);

    $this->configMock
      ->expects($this->any())
      ->method('set');

    $this->configMock
      ->expects($this->any())
      ->method('save');

    $this->moduleHandlerMock
      ->expects($this->any())
      ->method('alter');

    $this->salsify::createFieldMapping([
      'field_id' => 'salsify:url',
      'salsify_id' => 'salsify:url',
      'salsify_data_type' => 'string',
      'entity_type' => 'media',
      'bundle' => 'image',
      'field_name' => 'image',
      'method' => 'manual',
      'created' => Salsify::FIELD_MAP_CREATED,
      'changed' => Salsify::FIELD_MAP_CHANGED,
    ]);
  }

  /**
   * Test.
   */
  public function testShouldUpdateFieldMapping() {

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configFactoryMock,
          ],
        ]
      );

    $this->configFactoryMock
      ->expects($this->once())
      ->method('getEditable')
      ->willReturn($this->configMock);

    $this->configMock
      ->expects($this->any())
      ->method('set');

    $this->configMock
      ->expects($this->any())
      ->method('save');

    $this->moduleHandlerMock
      ->expects($this->any())
      ->method('alter');

    $this->salsify::updateFieldMapping([
      'field_id' => 'salsify:url',
      'salsify_id' => 'salsify:url',
      'salsify_data_type' => 'string',
      'entity_type' => 'media',
      'bundle' => 'image',
      'field_name' => 'image',
      'method' => 'manual',
      'created' => Salsify::FIELD_MAP_CREATED,
      'changed' => Salsify::FIELD_MAP_CHANGED,
    ]);
  }

  /**
   * Test.
   */
  public function testShouldDeleteFieldMapping() {

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configFactoryMock,
          ],
        ]
      );

    $this->configFactoryMock
      ->expects($this->once())
      ->method('getEditable')
      ->willReturn($this->configMock);

    $this->configMock
      ->expects($this->any())
      ->method('delete');

    $this->salsify::deleteFieldMapping([
      'field_id' => 'salsify:url',
      'salsify_id' => 'salsify:url',
      'salsify_data_type' => 'string',
      'entity_type' => 'media',
      'bundle' => 'image',
      'field_name' => 'image',
      'method' => 'manual',
      'created' => Salsify::FIELD_MAP_CREATED,
      'changed' => Salsify::FIELD_MAP_CHANGED,
    ]);
  }

  /**
   * Test.
   */
  public function testShouldRemoveFieldOptions() {
    $this->configFactoryMock
      ->expects($this->once())
      ->method('getEditable')
      ->willReturn($this->configMock);

    $this->configMock
      ->expects($this->once())
      ->method('get')
      ->willReturn(TRUE);

    $this->configMock
      ->expects($this->once())
      ->method('clear')
      ->willReturn(TRUE);

    $this->configMock
      ->expects($this->once())
      ->method('save');

    $this->salsify->removeFieldOptions('system_id');
  }

  /**
   * Test.
   */
  public function testShouldRekeyArray() {
    $array = [
      'salsify:id' => '00040000004059_C1N1.tif',
      'salsify:url' => 'https://tes.t/test',
    ];

    $new_array = $this->salsify::rekeyArray([$array], 'salsify:id');
    $this->assertIsArray($new_array);
    $this->assertNotEmpty($new_array);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->moduleHandlerMock = $this->createMock(ModuleHandlerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->configMock = $this->createMock(ImmutableConfig::class);
    $this->entityMock = $this->createMock(EntityInterface::class);
    $this->fieldManagerMock = $this->createMock(EntityFieldManagerInterface::class);
    $this->loggerFactoryMock = $this->createMock(LoggerChannelFactoryInterface::class);
    $this->loggerChannelMock = $this->createMock(LoggerChannelInterface::class);
    $this->cacheBackendMock = $this->createMock(CacheBackendInterface::class);
    $this->queueFactoryMock = $this->createMock(QueueFactory::class);
    $this->mulesoftConnectorMock = $this->createMock(MulesoftConnector::class);
    $this->clientMock = $this->createMock(Client::class);
    $this->responseMock = $this->createMock(Response::class);
    $this->streamMock = $this->createMock(StreamInterface::class);
    $this->fieldConfigMock = $this->createMock(FieldConfig::class);

    $this->loggerFactoryMock
      ->expects($this->once())
      ->method('get')
      ->willReturn($this->loggerChannelMock);

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->configMock);
  }

}
