<?php

namespace Drupal\Tests\salsify_integration\Unit;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\salsify_integration\ProductHelper;
use Drupal\salsify_integration\Salsify;
use Drupal\salsify_integration\SalsifyImportField;
use Drupal\salsify_integration\SalsifyImportMedia;
use Drupal\salsify_integration\SalsifyImportTaxonomyTerm;
use Drupal\salsify_integration\SalsifyProductRepository;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\salsify_integration\SalsifyImportField
 * @group mars
 * @group salsify_integration
 */
class SalsifyImportFieldTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\SalsifyImportField
   */
  private $salsifyImportField;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Cache\CacheBackendInterface
   */
  private $cacheBackendMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\Salsify
   */
  private $salsifyMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\SalsifyProductRepository
   */
  private $salsifyProductRepoMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\ProductHelper
   */
  private $productHelperMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\SalsifyImportMedia
   */
  private $salsifyImportMediaMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\SalsifyImportTaxonomyTerm
   */
  private $salsifyImportTaxonomyTermMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityFieldManagerInterface
   */
  private $fieldManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityStorageInterface
   */
  private $entityStorageMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\Query\QueryInterface
   */
  private $queryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\FieldableEntityInterface
   */
  private $fieldableEntityMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\field\Entity\FieldConfig
   */
  private $fieldConfigMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeInterface
   */
  private $entityTypeMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\field\FieldStorageConfigInterface
   */
  private $fieldStorageConfigMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\FieldItemListInterface
   */
  private $fieldItemListMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->salsifyImportField = new SalsifyImportField(
      $this->configFactoryMock,
      $this->entityTypeManagerMock,
      $this->cacheBackendMock,
      $this->salsifyMock,
      $this->moduleHandlerMock,
      $this->salsifyProductRepoMock,
      $this->productHelperMock,
      $this->salsifyImportMediaMock,
      $this->salsifyImportTaxonomyTermMock
    );
  }

  /**
   * Test.
   */
  public function testShouldProcessSalsifyItem() {
    $items = [
      [
        'salsify:id' => '123123',
        'GTIN' => '123123',
        'salsify:updated_at' => '123123',
        'CMS: Meta Description' => ['meta'],
        'Case Net Weight' => 'value',
        'CMS: Variety' => 'no',
        'CMS: content type' => ProductHelper::PRODUCT_CONTENT_TYPE,
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
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'entity_field.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->fieldManagerMock,
          ],
          [
            'salsify_integration.product_data_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->productHelperMock,
          ],
          [
            'salsify_integration.salsify_product_repository',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyProductRepoMock,
          ],
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
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

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('salsify_id');

    $this->configMock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'product_variant',
      ]);

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('getQuery')
      ->willReturn($this->queryMock);

    $this->queryMock
      ->expects($this->any())
      ->method('condition')
      ->willReturn($this->queryMock);

    $this->queryMock
      ->expects($this->any())
      ->method('execute')
      ->willReturn(['123']);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn($this->fieldableEntityMock);

    $this->fieldableEntityMock
      ->expects($this->any())
      ->method('set');

    $this->fieldManagerMock
      ->expects($this->any())
      ->method('getFieldDefinitions')
      ->willReturn([$this->fieldConfigMock]);

    $this->productHelperMock
      ->expects($this->any())
      ->method('validateDataRecord')
      ->willReturn(TRUE);

    $this->salsifyProductRepoMock
      ->expects($this->any())
      ->method('updateParentEntities');

    $this->moduleHandlerMock
      ->expects($this->any())
      ->method('alter');

    $this->fieldableEntityMock
      ->expects($this->any())
      ->method('save');

    $result = $this->salsifyImportField::processSalsifyItem(
      $items[0],
      TRUE,
      ProductHelper::PRODUCT_CONTENT_TYPE
    );
    $this->assertIsArray($result);
  }

  /**
   * Add common mocks for mapping salsify field -> drupal filed process.
   */
  private function getMocksWhenProcessOfFieldMapping() {

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->configMock);

    $this->configFactoryMock
      ->expects($this->any())
      ->method('listAll')
      ->willReturn(['config1']);

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('field_name');

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('getQuery')
      ->willReturn($this->queryMock);

    $this->queryMock
      ->expects($this->any())
      ->method('condition')
      ->willReturn($this->queryMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn($this->fieldableEntityMock);

    $this->fieldableEntityMock
      ->expects($this->any())
      ->method('set');

    $this->fieldManagerMock
      ->expects($this->any())
      ->method('getFieldDefinitions')
      ->willReturn(['field_name' => $this->fieldConfigMock]);

    $this->salsifyProductRepoMock
      ->expects($this->any())
      ->method('updateParentEntities');

    $this->moduleHandlerMock
      ->expects($this->any())
      ->method('alter');

    $this->fieldableEntityMock
      ->expects($this->any())
      ->method('save');

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getDefinition')
      ->willReturn($this->entityTypeMock);

    $this->entityTypeMock
      ->expects($this->any())
      ->method('getKeys')
      ->willReturn([
        'label' => 'label',
        'bundle' => 'product',
        'created' => strtotime('now'),
        'changed' => strtotime('now'),
        'status' => 'status',
      ]);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('create')
      ->willReturn($this->fieldableEntityMock);

    $this->fieldableEntityMock
      ->expects($this->any())
      ->method('getTypedData');

    $this->moduleHandlerMock
      ->expects($this->any())
      ->method('moduleExists')
      ->willReturn(TRUE);

    $this->salsifyImportMediaMock
      ->expects($this->any())
      ->method('processSalsifyMediaItem')
      ->willReturn($this->fieldableEntityMock);

    $this->fieldableEntityMock
      ->expects($this->any())
      ->method('id')
      ->willReturn('123');
  }

  /**
   * Test.
   */
  public function testShouldProcessSalsifyItemWhenString() {
    $items = [
      [
        'salsify:id' => '123123',
        'GTIN' => '123123',
        'salsify:updated_at' => 'now',
        'salsify:created_at' => 'now',
        'CMS: Meta Description' => ['meta'],
        'Case Net Weight' => 'value',
        'CMS: Variety' => 'no',
        'CMS: content type' => ProductHelper::PRODUCT_CONTENT_TYPE,
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
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'entity_field.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->fieldManagerMock,
          ],
          [
            'salsify_integration.product_data_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->productHelperMock,
          ],
          [
            'salsify_integration.salsify_product_repository',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyProductRepoMock,
          ],
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
          [
            'salsify_integration.salsify_import_media',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyImportMediaMock,
          ],
        ]
      );

    $this->getMocksWhenProcessOfFieldMapping();

    $this->productHelperMock
      ->expects($this->any())
      ->method('validateDataRecord')
      ->willReturn(TRUE);

    $this->queryMock
      ->expects($this->any())
      ->method('execute')
      ->willReturn(0);

    $this->configMock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'product',
        'method' => 'manual',
        'entity_type' => 'node',
        'salsify_data_type' => 'string',
        'salsify_id' => 'Case Net Weight',
        'field_name' => 'field_name',
      ]);

    $this->fieldConfigMock
      ->expects($this->any())
      ->method('getType')
      ->willReturn('string');

    $this->fieldConfigMock
      ->expects($this->any())
      ->method('getFieldStorageDefinition')
      ->willReturn($this->fieldStorageConfigMock);

    $this->fieldStorageConfigMock
      ->expects($this->any())
      ->method('getSetting')
      ->willReturn(3);

    $result = $this->salsifyImportField::processSalsifyItem(
      $items[0],
      TRUE,
      ProductHelper::PRODUCT_CONTENT_TYPE
    );
    $this->assertIsArray($result);
  }

  /**
   * Test.
   */
  public function testShouldProcessSalsifyItemWhenListString() {
    $items = [
      [
        'salsify:id' => '123123',
        'GTIN' => '123123',
        'salsify:updated_at' => 'now',
        'salsify:created_at' => 'now',
        'CMS: Meta Description' => ['meta'],
        'Case Net Weight' => 'value',
        'CMS: Variety' => 'no',
        'CMS: content type' => ProductHelper::PRODUCT_CONTENT_TYPE,
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
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'entity_field.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->fieldManagerMock,
          ],
          [
            'salsify_integration.product_data_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->productHelperMock,
          ],
          [
            'salsify_integration.salsify_product_repository',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyProductRepoMock,
          ],
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
          [
            'salsify_integration.salsify_import_media',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyImportMediaMock,
          ],
        ]
      );

    $this->getMocksWhenProcessOfFieldMapping();

    $this->productHelperMock
      ->expects($this->any())
      ->method('validateDataRecord')
      ->willReturn(TRUE);

    $this->queryMock
      ->expects($this->any())
      ->method('execute')
      ->willReturn(0);

    $this->fieldConfigMock
      ->expects($this->any())
      ->method('getType')
      ->willReturn('list_string');

    $this->fieldConfigMock
      ->expects($this->any())
      ->method('getFieldStorageDefinition')
      ->willReturn($this->fieldStorageConfigMock);

    $this->fieldStorageConfigMock
      ->expects($this->any())
      ->method('getSetting')
      ->willReturn([]);

    $this->fieldStorageConfigMock
      ->expects($this->any())
      ->method('setSetting');

    $this->fieldStorageConfigMock
      ->expects($this->any())
      ->method('save');

    $this->configMock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'product',
        'method' => 'manual',
        'entity_type' => 'node',
        'salsify_data_type' => 'string',
        'salsify_id' => 'Case Net Weight',
        'field_name' => 'field_name',
      ]);

    $result = $this->salsifyImportField::processSalsifyItem(
      $items[0],
      TRUE,
      ProductHelper::PRODUCT_CONTENT_TYPE
    );
    $this->assertIsArray($result);
  }

  /**
   * Test.
   */
  public function testShouldProcessSalsifyItemWhenEnumerated() {
    $items = [
      [
        'salsify:id' => '123123',
        'GTIN' => '123123',
        'salsify:updated_at' => 'now',
        'salsify:created_at' => 'now',
        'CMS: Meta Description' => ['meta'],
        'Case Net Weight' => 'value',
        'CMS: Variety' => 'no',
        'CMS: content type' => ProductHelper::PRODUCT_CONTENT_TYPE,
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
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'entity_field.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->fieldManagerMock,
          ],
          [
            'salsify_integration.product_data_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->productHelperMock,
          ],
          [
            'salsify_integration.salsify_product_repository',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyProductRepoMock,
          ],
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
          [
            'salsify_integration.salsify_import_media',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyImportMediaMock,
          ],
          [
            'salsify_integration.salsify_import_taxonomy',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyImportTaxonomyTermMock,
          ],
        ]
      );

    $this->getMocksWhenProcessOfFieldMapping();

    $this->productHelperMock
      ->expects($this->any())
      ->method('validateDataRecord')
      ->willReturn(TRUE);

    $this->queryMock
      ->expects($this->any())
      ->method('execute')
      ->willReturn(0);

    $this->fieldConfigMock
      ->expects($this->any())
      ->method('getType')
      ->willReturn('entity_reference');

    $this->salsifyImportTaxonomyTermMock
      ->expects($this->any())
      ->method('getTaxonomyTerms')
      ->willReturn([$this->fieldableEntityMock]);

    $this->configMock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'product',
        'method' => 'manual',
        'entity_type' => 'node',
        'salsify_data_type' => 'enumerated',
        'salsify_id' => 'Case Net Weight',
        'field_name' => 'field_name',
      ]);

    $result = $this->salsifyImportField::processSalsifyItem(
      $items[0],
      TRUE,
      ProductHelper::PRODUCT_CONTENT_TYPE
    );
    $this->assertIsArray($result);
  }

  /**
   * Test.
   */
  public function testShouldProcessSalsifyItemWhenEntityRef() {
    $items = [
      [
        'salsify:id' => '123123',
        'GTIN' => '123123',
        'salsify:updated_at' => 'now',
        'salsify:created_at' => 'now',
        'CMS: Meta Description' => ['meta'],
        'Case Net Weight' => 'value',
        'CMS: Variety' => 'no',
        'CMS: content type' => ProductHelper::PRODUCT_CONTENT_TYPE,
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
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'entity_field.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->fieldManagerMock,
          ],
          [
            'salsify_integration.product_data_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->productHelperMock,
          ],
          [
            'salsify_integration.salsify_product_repository',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyProductRepoMock,
          ],
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
          [
            'salsify_integration.salsify_import_media',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyImportMediaMock,
          ],
          [
            'salsify_integration.salsify_import_taxonomy',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyImportTaxonomyTermMock,
          ],
        ]
      );

    $this->getMocksWhenProcessOfFieldMapping();

    $this->productHelperMock
      ->expects($this->any())
      ->method('validateDataRecord')
      ->willReturn(TRUE);

    $this->queryMock
      ->expects($this->any())
      ->method('execute')
      ->willReturn([1]);

    $this->fieldConfigMock
      ->expects($this->any())
      ->method('getType')
      ->willReturn('entity_reference');

    $this->fieldConfigMock
      ->expects($this->any())
      ->method('getSetting')
      ->willReturn([]);

    $this->salsifyImportTaxonomyTermMock
      ->expects($this->any())
      ->method('getTaxonomyTerms')
      ->willReturn([$this->fieldableEntityMock]);

    $this->configMock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'product',
        'method' => 'manual',
        'entity_type' => 'node',
        'salsify_data_type' => 'entity_ref',
        'salsify_id' => 'Case Net Weight',
        'field_name' => 'field_name',
      ]);

    $result = $this->salsifyImportField::processSalsifyItem(
      $items[0],
      TRUE,
      ProductHelper::PRODUCT_CONTENT_TYPE
    );
    $this->assertIsArray($result);
  }

  /**
   * Test.
   */
  public function testShouldProcessSalsifyItemWhenMetaTag() {
    $items = [
      [
        'salsify:id' => '123123',
        'GTIN' => '123123',
        'salsify:updated_at' => 'now',
        'salsify:created_at' => 'now',
        'CMS: Meta Description' => ['meta'],
        'Case Net Weight' => 'value',
        'CMS: Variety' => 'no',
        'CMS: content type' => ProductHelper::PRODUCT_CONTENT_TYPE,
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
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'entity_field.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->fieldManagerMock,
          ],
          [
            'salsify_integration.product_data_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->productHelperMock,
          ],
          [
            'salsify_integration.salsify_product_repository',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyProductRepoMock,
          ],
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
          [
            'salsify_integration.salsify_import_media',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyImportMediaMock,
          ],
          [
            'salsify_integration.salsify_import_taxonomy',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyImportTaxonomyTermMock,
          ],
        ]
      );

    $this->getMocksWhenProcessOfFieldMapping();

    $this->productHelperMock
      ->expects($this->any())
      ->method('validateDataRecord')
      ->willReturn(TRUE);

    $this->queryMock
      ->expects($this->any())
      ->method('execute')
      ->willReturn([1]);

    $this->fieldConfigMock
      ->expects($this->any())
      ->method('getType')
      ->willReturn('metatag');

    $this->fieldableEntityMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->fieldItemListMock);

    $this->fieldItemListMock
      ->expects($this->any())
      ->method('__get')
      ->willReturn('a:1:{i:0;s:4:"test";}');

    $this->configMock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'product',
        'method' => 'manual',
        'entity_type' => 'node',
        'salsify_data_type' => 'complex',
        'salsify_id' => 'Case Net Weight',
        'field_name' => 'field_name',
      ]);

    $result = $this->salsifyImportField::processSalsifyItem(
      $items[0],
      TRUE,
      ProductHelper::PRODUCT_CONTENT_TYPE
    );
    $this->assertIsArray($result);
  }

  /**
   * Test.
   */
  public function testShouldProcessSalsifyItemWhenMedia() {
    $items = [
      [
        'salsify:id' => '123123',
        'GTIN' => '123123',
        'salsify:updated_at' => 'now',
        'salsify:created_at' => 'now',
        'CMS: Meta Description' => ['meta'],
        'Case Net Weight' => 'value',
        'CMS: Variety' => 'no',
        'CMS: content type' => ProductHelper::PRODUCT_CONTENT_TYPE,
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
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'entity_field.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->fieldManagerMock,
          ],
          [
            'salsify_integration.product_data_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->productHelperMock,
          ],
          [
            'salsify_integration.salsify_product_repository',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyProductRepoMock,
          ],
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
          [
            'salsify_integration.salsify_import_media',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyImportMediaMock,
          ],
          [
            'salsify_integration.salsify_import_taxonomy',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyImportTaxonomyTermMock,
          ],
        ]
      );

    $this->getMocksWhenProcessOfFieldMapping();

    $this->productHelperMock
      ->expects($this->any())
      ->method('validateDataRecord')
      ->willReturn(TRUE);

    $this->queryMock
      ->expects($this->any())
      ->method('execute')
      ->willReturn([1]);

    $this->fieldConfigMock
      ->expects($this->any())
      ->method('getType')
      ->willReturn('metatag');

    $this->fieldableEntityMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->fieldItemListMock);

    $this->configMock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'product',
        'method' => 'manual',
        'entity_type' => 'node',
        'salsify_data_type' => 'digital_asset',
        'salsify_id' => 'Case Net Weight',
        'field_name' => 'field_name',
      ]);

    $result = $this->salsifyImportField::processSalsifyItem(
      $items[0],
      TRUE,
      ProductHelper::PRODUCT_CONTENT_TYPE
    );
    $this->assertIsArray($result);
  }

  /**
   * Test.
   */
  public function testShouldProcessSalsifyItemWhenNoValid() {
    $items = [
      [
        'salsify:id' => '123123',
        'GTIN' => '123123',
        'salsify:updated_at' => 'now',
        'salsify:created_at' => 'now',
        'CMS: Meta Description' => ['meta'],
        'Case Net Weight' => 'value',
        'CMS: Variety' => 'no',
        'CMS: content type' => ProductHelper::PRODUCT_CONTENT_TYPE,
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
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'entity_field.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->fieldManagerMock,
          ],
          [
            'salsify_integration.product_data_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->productHelperMock,
          ],
          [
            'salsify_integration.salsify_product_repository',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyProductRepoMock,
          ],
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
          [
            'salsify_integration.salsify_import_media',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyImportMediaMock,
          ],
          [
            'salsify_integration.salsify_import_taxonomy',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyImportTaxonomyTermMock,
          ],
        ]
      );

    $this->getMocksWhenProcessOfFieldMapping();

    $this->productHelperMock
      ->expects($this->any())
      ->method('validateDataRecord')
      ->willReturn(FALSE);

    $this->queryMock
      ->expects($this->any())
      ->method('execute')
      ->willReturn([1]);

    $this->fieldConfigMock
      ->expects($this->any())
      ->method('getType')
      ->willReturn('metatag');

    $this->fieldableEntityMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->fieldItemListMock);

    $this->configMock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'product',
        'method' => 'manual',
        'entity_type' => 'node',
        'salsify_data_type' => 'digital_asset',
        'salsify_id' => 'Case Net Weight',
        'field_name' => 'field_name',
      ]);

    $result = $this->salsifyImportField::processSalsifyItem(
      $items[0],
      TRUE,
      ProductHelper::PRODUCT_CONTENT_TYPE
    );
    $this->assertIsArray($result);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->moduleHandlerMock = $this->createMock(ModuleHandlerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->configMock = $this->createMock(ImmutableConfig::class);
    $this->cacheBackendMock = $this->createMock(CacheBackendInterface::class);
    $this->salsifyProductRepoMock = $this->createMock(SalsifyProductRepository::class);
    $this->productHelperMock = $this->createMock(ProductHelper::class);
    $this->salsifyImportMediaMock = $this->createMock(SalsifyImportMedia::class);
    $this->salsifyImportTaxonomyTermMock = $this->createMock(SalsifyImportTaxonomyTerm::class);
    $this->fieldManagerMock = $this->createMock(EntityFieldManagerInterface::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->queryMock = $this->createMock(QueryInterface::class);
    $this->fieldableEntityMock = $this->createMock(FieldableEntityInterface::class);
    $this->fieldConfigMock = $this->createMock(FieldConfig::class);
    $this->salsifyMock = $this->createMock(Salsify::class);
    $this->entityTypeMock = $this->createMock(EntityTypeInterface::class);
    $this->fieldStorageConfigMock = $this->createMock(FieldStorageConfigInterface::class);
    $this->fieldItemListMock = $this->createMock(FieldItemListInterface::class);

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->configMock);

    $this->fieldableEntityMock
      ->expects($this->any())
      ->method('getEntityType')
      ->willReturn($this->entityTypeMock);

    $this->entityTypeMock
      ->expects($this->any())
      ->method('id')
      ->willReturn('node');
  }

}
