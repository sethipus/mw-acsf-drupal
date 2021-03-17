<?php

namespace Drupal\Tests\salsify_integration\Unit\Plugin\QueueWorker;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\salsify_integration\Plugin\QueueWorker\SalsifyContentImport;
use Drupal\salsify_integration\ProductHelper;
use Drupal\salsify_integration\SalsifyEmailReport;
use Drupal\salsify_integration\SalsifyFields;
use Drupal\salsify_integration\SalsifyProductRepository;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\salsify_integration\Plugin\QueueWorker\SalsifyContentImport
 * @group mars
 * @group salsify_integration
 */
class SalsifyContentImportTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\Plugin\QueueWorker\SalsifyContentImport
   */
  private $salsifyContentImport;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  private $eventDispatcherMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandlerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\SalsifyFields
   */
  private $salsifyFieldsMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\Config
   */
  private $configMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeInterface
   */
  private $entityTypeMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityStorageInterface
   */
  private $entityStorageMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityInterface
   */
  private $entityMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\FieldableEntityInterface
   */
  private $fieldableEntityMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\Query\QueryInterface
   */
  private $queryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityFieldManagerInterface
   */
  private $fieldManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\field\Entity\FieldConfig
   */
  private $fieldConfigMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\ProductHelper
   */
  private $productHelperMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\SalsifyProductRepository
   */
  private $salsifyProductRepoMock;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\SalsifyEmailReport
   */
  private $salsifyEmailReportMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Queue\QueueFactory
   */
  private $queueFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ImmutableConfig
   */
  private $immutableConfigMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->salsifyContentImport = new SalsifyContentImport(
      $this->configFactoryMock,
      $this->entityTypeManagerMock,
      $this->queueFactoryMock,
      $this->loggerFactoryMock,
      $this->salsifyEmailReportMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(5))
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
            'queue',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->queueFactoryMock,
          ],
          [
            'logger.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->loggerFactoryMock,
          ],
          [
            'salsify_integration.email_report',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyEmailReportMock,
          ],
        ]
      );
    $this->salsifyContentImport::create(
      $this->containerMock,
      [],
      'plugin_id',
      'plugin_def'
    );
  }

  /**
   * Test.
   */
  public function testShouldProcessItem() {
    $data = [
      'salsify:id' => '123123',
      'GTIN' => '123123',
      'salsify:updated_at' => '123123',
      'CMS: Meta Description' => ['meta'],
      'Case Net Weight' => 'value',
      'CMS: Variety' => 'no',
      'force_update' => TRUE,
      'CMS: content type' => 'product_variant',
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
            'string_translation',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->translationMock,
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

    $this->immutableConfigMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('node');

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

    $this->salsifyEmailReportMock
      ->expects($this->once())
      ->method('saveValidationErrors');

    $this->loggerChannelMock
      ->expects($this->once())
      ->method('info');

    $this->fieldableEntityMock
      ->expects($this->any())
      ->method('getEntityType')
      ->willReturn($this->entityTypeMock);

    $this->entityTypeMock
      ->expects($this->any())
      ->method('id')
      ->willReturn('node');

    $this->salsifyContentImport->processItem($data);
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
    $this->eventDispatcherMock = $this->createMock(ContainerAwareEventDispatcher::class);
    $this->salsifyFieldsMock = $this->createMock(SalsifyFields::class);
    $this->configMock = $this->createMock(Config::class);
    $this->immutableConfigMock = $this->createMock(ImmutableConfig::class);
    $this->entityTypeMock = $this->createMock(EntityTypeInterface::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->entityMock = $this->createMock(EntityInterface::class);
    $this->queryMock = $this->createMock(QueryInterface::class);
    $this->fieldableEntityMock = $this->createMock(FieldableEntityInterface::class);
    $this->fieldManagerMock = $this->createMock(EntityFieldManagerInterface::class);
    $this->fieldConfigMock = $this->createMock(FieldConfig::class);
    $this->productHelperMock = $this->createMock(ProductHelper::class);
    $this->salsifyProductRepoMock = $this->createMock(SalsifyProductRepository::class);
    $this->loggerFactoryMock = $this->createMock(LoggerChannelFactoryInterface::class);
    $this->loggerChannelMock = $this->createMock(LoggerChannelInterface::class);
    $this->salsifyEmailReportMock = $this->createMock(SalsifyEmailReport::class);
    $this->queueFactoryMock = $this->createMock(QueueFactory::class);

    $this->loggerFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->loggerChannelMock);

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->immutableConfigMock);
  }

}

namespace Drupal\salsify_integration\Plugin\QueueWorker;

/**
 * {@inheritdoc}
 */
function t(string $string) {
  return $string;
}
