<?php

namespace Drupal\Tests\salsify_integration\Unit;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\salsify_integration\MulesoftConnector;
use Drupal\salsify_integration\ProductFieldsMapper;
use Drupal\salsify_integration\ProductHelper;
use Drupal\salsify_integration\SalsifyEmailReport;
use Drupal\salsify_integration\SalsifyFields;
use Drupal\salsify_integration\SalsifyImportField;
use Drupal\salsify_integration\SalsifyImportTaxonomyTerm;
use Drupal\salsify_integration\SalsifyProductRepository;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\salsify_integration\SalsifyFields
 * @group mars
 * @group salsify_integration
 */
class SalsifyFieldsTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\SalsifyFields
   */
  private $salsifyFields;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\Display\EntityViewDisplayInterface
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
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\SalsifyProductRepository
   */
  private $salsifyProductRepositoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Mail\MailManagerInterface
   */
  private $mailManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\ProductHelper
   */
  private $productHelperMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\SalsifyEmailReport
   */
  private $salsifyEmailReportMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\ProductFieldsMapper
   */
  private $productFieldsMapperMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\SalsifyImportField
   */
  private $salsifyImportFieldMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\SalsifyImportTaxonomyTerm
   */
  private $salsifyImportTermMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Messenger\MessengerInterface
   */
  private $messengerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityStorageInterface
   */
  private $entityStorageMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeRepositoryInterface
   */
  private $entityTypeRepositoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Queue\QueueInterface
   */
  private $queueMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->salsifyFields = new SalsifyFields(
      $this->loggerFactoryMock,
      $this->configFactoryMock,
      $this->entityTypeManagerMock,
      $this->fieldManagerMock,
      $this->cacheBackendMock,
      $this->queueFactoryMock,
      $this->moduleHandlerMock,
      $this->mulesoftConnectorMock,
      $this->clientMock,
      $this->salsifyProductRepositoryMock,
      $this->mailManagerMock,
      $this->languageManagerMock,
      $this->productHelperMock,
      $this->salsifyEmailReportMock,
      $this->productFieldsMapperMock,
      $this->salsifyImportFieldMock,
      $this->salsifyImportTermMock,
      $this->messengerMock
    );
  }

  /**
   * Test.
   */
  public function testShouldImportProductFields() {
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
          [
            'entity_field.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->fieldManagerMock,
          ],
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'entity_type.repository',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeRepositoryMock,
          ],
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
        ]
      );

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn($this->entityMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('create')
      ->willReturn($this->entityMock);

    $this->entityMock
      ->expects($this->any())
      ->method('save');

    $this->entityTypeRepositoryMock
      ->expects($this->any())
      ->method('getEntityTypeFromClass')
      ->willReturn('entity_type');

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
      ->willReturn('salsify:updated_at');

    $this->configMock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'salsify:updated_at',
        'method' => 'manual',
        'entity_type' => 'node',
        'salsify_data_type' => 'complex',
        'field_name' => 'field_name',
        'salsify_id' => 'salsify_id',
        'changed' => time() + 1,
      ]);

    $this->moduleHandlerMock
      ->expects($this->any())
      ->method('alter');

    $this->cacheBackendMock
      ->expects($this->once())
      ->method('set');

    $this->fieldManagerMock
      ->expects($this->once())
      ->method('getFieldDefinitions')
      ->willReturn([
        'field_name' => $this->fieldConfigMock,
        'salsify_id' => $this->fieldConfigMock,
      ]);

    $result = $this->salsifyFields->importProductFields();
    $this->assertIsArray($result);
    $this->assertArrayHasKey('fields', $result);
    $this->assertNotEmpty($result['fields']);
  }

  /**
   * Test.
   */
  public function testShouldImportProductData() {
    $this->clientMock
      ->expects($this->once())
      ->method('__call')
      ->willReturn($this->responseMock);

    $this->responseMock
      ->expects($this->once())
      ->method('getBody')
      ->willReturn($this->streamMock);

    $this->productHelperMock
      ->expects($this->once())
      ->method('sortProducts');

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
          [
            'entity_field.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->fieldManagerMock,
          ],
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'entity_type.repository',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeRepositoryMock,
          ],
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
          [
            'string_translation',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->translationMock,
          ],
        ]
      );

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn($this->entityMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('create')
      ->willReturn($this->entityMock);

    $this->entityMock
      ->expects($this->any())
      ->method('save');

    $this->entityTypeRepositoryMock
      ->expects($this->any())
      ->method('getEntityTypeFromClass')
      ->willReturn('entity_type');

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
      ->willReturn('bundle');

    $this->configMock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'bundle',
        'method' => 'manual',
        'entity_type' => 'node',
        'salsify_data_type' => 'complex',
        'field_name' => 'field_name',
        'changed' => time(),
      ]);

    $this->moduleHandlerMock
      ->expects($this->any())
      ->method('alter');

    $this->cacheBackendMock
      ->expects($this->once())
      ->method('set');

    $this->fieldManagerMock
      ->expects($this->any())
      ->method('getFieldDefinitions')
      ->willReturn(['' => $this->fieldConfigMock]);

    $this->queueFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->queueMock);

    $this->queueMock
      ->expects($this->once())
      ->method('numberOfItems')
      ->willReturn(0);

    $this->queueMock
      ->expects($this->any())
      ->method('createItem');

    $this->salsifyProductRepositoryMock
      ->expects($this->any())
      ->method('unpublishProducts')
      ->willReturn([1]);

    $this->salsifyEmailReportMock
      ->expects($this->any())
      ->method('sendReport');

    $result = $this->salsifyFields->importProductData();
    $this->assertIsArray($result);
  }

  /**
   * Test.
   */
  public function testShouldImportProductDataWhenNoProducts() {
    $this->clientMock
      ->expects($this->once())
      ->method('__call')
      ->willReturn($this->responseMock);

    $this->productHelperMock
      ->expects($this->once())
      ->method('sortProducts');

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
        'products' => [],
        'mapping' => [],
        'market' => 'US',
      ]);

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
          [
            'entity_field.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->fieldManagerMock,
          ],
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'entity_type.repository',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeRepositoryMock,
          ],
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
          [
            'string_translation',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->translationMock,
          ],
        ]
      );

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn($this->entityMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('create')
      ->willReturn($this->entityMock);

    $this->entityMock
      ->expects($this->any())
      ->method('save');

    $this->entityTypeRepositoryMock
      ->expects($this->any())
      ->method('getEntityTypeFromClass')
      ->willReturn('entity_type');

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
      ->willReturn('bundle');

    $this->configMock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'bundle',
        'method' => 'manual',
        'entity_type' => 'node',
        'salsify_data_type' => 'complex',
        'field_name' => 'field_name',
        'changed' => time(),
      ]);

    $this->moduleHandlerMock
      ->expects($this->any())
      ->method('alter');

    $this->cacheBackendMock
      ->expects($this->once())
      ->method('set');

    $this->fieldManagerMock
      ->expects($this->any())
      ->method('getFieldDefinitions')
      ->willReturn(['' => $this->fieldConfigMock]);

    $this->loggerChannelMock
      ->expects($this->once())
      ->method('error');

    $result = $this->salsifyFields->importProductData();
    $this->assertIsArray($result);
  }

  /**
   * Test.
   */
  public function testShouldImportProductDataWhenException() {
    $this->clientMock
      ->expects($this->once())
      ->method('__call')
      ->willReturn($this->responseMock);

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('bundle');

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'string_translation',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->translationMock,
          ],
        ]
      );

    $this->responseMock
      ->expects($this->once())
      ->method('getBody')
      ->willThrowException(new MissingDataException('message'));

    $this->loggerChannelMock
      ->expects($this->any())
      ->method('error');

    $result = $this->salsifyFields->importProductData();
    $this->assertIsArray($result);
  }

  /**
   * Test.
   */
  public function testShouldAddChildLinks() {
    $mapping = [
      '123' => [
        '124' => ProductHelper::PRODUCT_VARIANT_CONTENT_TYPE,
        '125' => ProductHelper::PRODUCT_CONTENT_TYPE,
      ],
    ];
    $product = [
      'GTIN' => '123',
    ];

    $this->salsifyFields->addChildLinks($mapping, $product);
    $this->assertArrayHasKey('CMS: Child variants', $product);
    $this->assertArrayHasKey('CMS: Child products', $product);
    $this->assertSame(124, reset($product['CMS: Child variants']));
    $this->assertSame(125, reset($product['CMS: Child products']));
  }

  /**
   * Test.
   */
  public function testShouldPrepareTermData() {
    $product_data = [
      'fields' => [
        'bundle' => [
          'values' => [2 => 2, 3 => 3, 4 => 4],
        ],
        'salsify_id' => ['salsify_id'],
      ],
    ];

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
          [
            'entity_field.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->fieldManagerMock,
          ],
        ]
      );

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('bundle');

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
      ->willReturn('bundle');

    $this->configMock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'bundle',
        'method' => 'manual',
        'entity_type' => 'node',
        'salsify_data_type' => 'complex',
        'salsify_id' => 'salsify_id',
        'field_name' => 'field_name',
        'changed' => time(),
      ]);

    $this->fieldManagerMock
      ->expects($this->any())
      ->method('getFieldDefinitions')
      ->willReturn(['field_name' => $this->fieldConfigMock]);

    $this->fieldConfigMock
      ->expects($this->any())
      ->method('getType')
      ->willReturn('entity_reference');

    $this->fieldConfigMock
      ->expects($this->any())
      ->method('getSetting')
      ->willReturnMap(
        [
          [
            'handler',
            'default:taxonomy_term',
          ],
          [
            'handler_settings',
            ['target_bundles' => ['bundle']],
          ],
        ]
      );

    $this->salsifyImportTermMock
      ->expects($this->any())
      ->method('processSalsifyTaxonomyTermItems');

    $this->salsifyFields->prepareTermData($product_data);
  }

  /**
   * Test.
   */
  public function testShouldCreateFieldMachineName() {
    $field_name = 'field_name';
    $field_machine_names = [
      'field_name',
      'field_name_2',
      'salsifysync_fieldname',
    ];

    $new_filed_name = $this->salsifyFields::createFieldMachineName(
      $field_name,
      $field_machine_names
    );
    $this->assertSame(
      'salsifysync_fieldname_0',
      $new_filed_name,
    );
  }

  /**
   * Test.
   */
  public function testShouldCreateFieldMachineNameWhenSalsifyField() {
    $field_name = 'salsify:id';
    $field_machine_names = [
      'field_name',
      'field_name_2',
      'salsifysync_fieldname',
    ];

    $new_filed_name = $this->salsifyFields::createFieldMachineName(
      $field_name,
      $field_machine_names
    );
    $this->assertSame(
      'salsify_id',
      $new_filed_name,
    );
  }

  /**
   * Test.
   */
  public function testShouldCreateDynamicField() {

    $salsify_data = [
      'salsify:created_at' => "now",
      'date_updated' => "now",
      'salsify:data_type' => 'integer',
      'salsify:name' => 'salsify_name',
      'salsify:attribute_group' => 'number',
      'salsify:system_id' => 'system_id',
      'salsify:id' => 'salsify:id',
    ];

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
          [
            'entity_field.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->fieldManagerMock,
          ],
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'entity_type.repository',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeRepositoryMock,
          ],
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
        ]
      );

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn(NULL);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('create')
      ->willReturn($this->entityMock);

    $this->entityMock
      ->expects($this->any())
      ->method('save');

    $this->entityTypeRepositoryMock
      ->expects($this->any())
      ->method('getEntityTypeFromClass')
      ->willReturn('entity_type');

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->configMock);

    $this->configFactoryMock
      ->expects($this->any())
      ->method('getEditable')
      ->willReturn($this->configMock);

    $this->configMock
      ->expects($this->any())
      ->method('set');

    $this->configMock
      ->expects($this->any())
      ->method('save');

    $this->salsifyFields::createDynamicField(
      $salsify_data,
      'field_name',
      'node',
      'product_variant'
    );

    $this->entityMock
      ->expects($this->any())
      ->method('setComponent')
      ->willReturnSelf();

    $this->salsifyFields::createDynamicField(
      $salsify_data,
      'salsifysync_field_name',
      'node',
      'product_variant'
    );
  }

  /**
   * Test.
   */
  public function testShouldGetFieldSettingsByType() {
    $method = $this->getMethod(
      SalsifyFields::class,
      'getFieldSettingsByType'
    );
    $salsify_data = [
      'salsify:data_type' => 'digital_asset',
      'salsify:attribute_group' => 'Images',
      'salsify:name' => 'salsify_name',
      'salsify:system_id' => 'system_id',
      'values' => [
        [
          'salsify:id' => '123123123',
          'salsify:name' => 'salsifyname',
        ],
      ],
    ];
    $result = $method->invokeArgs($this->salsifyFields, [
      $salsify_data,
      'node',
      'product_variant',
      'field_name',
    ]);
    $this->assertSame(
      'image',
      $result['field_storage']['type']
    );

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
      ->method('getEditable')
      ->willReturn($this->configMock);

    $this->configMock
      ->expects($this->any())
      ->method('set')
      ->willReturnSelf();

    $this->configMock
      ->expects($this->any())
      ->method('save')
      ->willReturnSelf();

    $salsify_data['salsify:data_type'] = 'enumerated';
    $result = $method->invokeArgs($this->salsifyFields, [
      $salsify_data,
      'node',
      'product_variant',
      'field_name',
    ]);
    $this->assertSame(
      'list_string',
      $result['field_storage']['type']
    );

    $salsify_data['salsify:data_type'] = 'date';
    $result = $method->invokeArgs($this->salsifyFields, [
      $salsify_data,
      'node',
      'product_variant',
      'field_name',
    ]);
    $this->assertSame(
      'datetime',
      $result['field_storage']['type']
    );

    $salsify_data['salsify:data_type'] = 'boolean';
    $result = $method->invokeArgs($this->salsifyFields, [
      $salsify_data,
      'node',
      'product_variant',
      'field_name',
    ]);
    $this->assertSame(
      'boolean',
      $result['field_storage']['type']
    );

    $salsify_data['salsify:data_type'] = 'rich_text';
    $result = $method->invokeArgs($this->salsifyFields, [
      $salsify_data,
      'node',
      'product_variant',
      'field_name',
    ]);
    $this->assertSame(
      'text_long',
      $result['field_storage']['type']
    );

    $salsify_data['salsify:data_type'] = 'html';
    $result = $method->invokeArgs($this->salsifyFields, [
      $salsify_data,
      'node',
      'product_variant',
      'field_name',
    ]);
    $this->assertSame(
      'string_long',
      $result['field_storage']['type']
    );

    $salsify_data['salsify:data_type'] = 'link';
    $result = $method->invokeArgs($this->salsifyFields, [
      $salsify_data,
      'node',
      'product_variant',
      'field_name',
    ]);
    $this->assertSame(
      'link',
      $result['field_storage']['type']
    );

    $salsify_data['salsify:data_type'] = 'number';
    $result = $method->invokeArgs($this->salsifyFields, [
      $salsify_data,
      'node',
      'product_variant',
      'field_name',
    ]);
    $this->assertSame(
      'integer',
      $result['field_storage']['type']
    );
  }

  /**
   * Test.
   */
  public function testShouldCreateFieldViewDisplay() {
    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
        ]
      );

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn(NULL);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('create')
      ->willReturn($this->entityMock);

    $this->entityMock
      ->expects($this->once())
      ->method('setComponent')
      ->willReturnSelf();

    $this->entityMock
      ->expects($this->once())
      ->method('save')
      ->willReturnSelf();

    $this->salsifyFields::createFieldViewDisplay(
      'node',
      'product_variant',
      'field_name',
      'view_mode'
    );
  }

  /**
   * Test.
   */
  public function testShouldCreateFieldFormDisplay() {
    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
        ]
      );

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn(NULL);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('create')
      ->willReturn($this->entityMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('save');

    $this->entityMock
      ->expects($this->once())
      ->method('setComponent')
      ->willReturnSelf();

    $this->entityMock
      ->expects($this->once())
      ->method('save')
      ->willReturnSelf();

    $this->salsifyFields::createFieldViewDisplay(
      'node',
      'product_variant',
      'field_name',
      'enumerated'
    );
  }

  /**
   * Allow to test protected or private methods for class.
   *
   * @param string $class_type
   *   A class type.
   * @param string $method_name
   *   A method name.
   *
   * @return \ReflectionMethod
   *   A method that can be accessible.
   *
   * @throws \ReflectionException
   */
  protected function getMethod($class_type, $method_name) {
    $class = new \ReflectionClass($class_type);
    $method = $class->getMethod($method_name);
    $method->setAccessible(TRUE);
    return $method;
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
    $this->entityMock = $this->createMock(EntityViewDisplayInterface::class);
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
    $this->salsifyProductRepositoryMock = $this->createMock(SalsifyProductRepository::class);
    $this->mailManagerMock = $this->createMock(MailManagerInterface::class);
    $this->languageManagerMock = $this->createMock(LanguageManagerInterface::class);
    $this->productHelperMock = $this->createMock(ProductHelper::class);
    $this->salsifyEmailReportMock = $this->createMock(SalsifyEmailReport::class);
    $this->productFieldsMapperMock = $this->createMock(ProductFieldsMapper::class);
    $this->salsifyImportFieldMock = $this->createMock(SalsifyImportField::class);
    $this->salsifyImportTermMock = $this->createMock(SalsifyImportTaxonomyTerm::class);
    $this->messengerMock = $this->createMock(MessengerInterface::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->entityTypeRepositoryMock = $this->createMock(EntityTypeRepositoryInterface::class);
    $this->queueMock = $this->createMock(QueueInterface::class);

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
