<?php

namespace Drupal\Tests\salsify_integration\Unit\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\field\Entity\FieldConfig;
use Drupal\salsify_integration\Plugin\QueueWorker\SalsifyEntityTypeUpdate;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\salsify_integration\Plugin\QueueWorker\SalsifyEntityTypeUpdate
 * @group mars
 * @group salsify_integration
 */
class SalsifyEntityTypeUpdateTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\Plugin\QueueWorker\SalsifyEntityTypeUpdate
   */
  private $salsifyEntityTypeUpdate;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManagerMock;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityFieldManagerInterface
   */
  private $entityFieldManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Queue\QueueFactory
   */
  private $queueFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\Query\QueryInterface
   */
  private $queryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\FieldDefinitionInterface
   */
  private $fieldDefMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\field\Entity\FieldConfig
   */
  private $fieldConfigMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\FieldStorageDefinitionInterface
   */
  private $fieldStorageDefMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ImmutableConfig
   */
  private $configMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeRepositoryInterface
   */
  private $entityTypeRepoMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandlerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  private $entityViewDisplayMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->salsifyEntityTypeUpdate = new SalsifyEntityTypeUpdate(
      $this->entityFieldManagerMock,
      $this->entityTypeManagerMock,
      $this->queueFactoryMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(3))
      ->method('get')
      ->willReturnMap(
        [
          [
            'entity_field.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityFieldManagerMock,
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
        ]
      );

    $this->salsifyEntityTypeUpdate::create(
      $this->containerMock,
      [],
      'pludin_id',
      'plugin_def'
    );
  }

  /**
   * Test.
   */
  public function testShouldProcessItem() {
    $data = [
      'original' => [
        'entity_type' => 'node',
        'bundle' => 'product',
      ],
      'current' => [
        'entity_type' => 'node',
        'bundle' => 'product_variant',
      ],
    ];

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'entity_type.repository',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeRepoMock,
          ],
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
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

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->once())
      ->method('getQuery')
      ->willReturn($this->queryMock);

    $this->queryMock
      ->expects($this->once())
      ->method('exists')
      ->willReturn($this->queryMock);

    $this->queryMock
      ->expects($this->once())
      ->method('execute')
      ->willReturn([1]);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('loadMultiple')
      ->willReturn([$this->entityMock]);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn($this->entityViewDisplayMock);

    $this->entityViewDisplayMock
      ->expects($this->any())
      ->method('setComponent')
      ->willReturn($this->entityViewDisplayMock);

    $this->entityViewDisplayMock
      ->expects($this->any())
      ->method('save');

    $this->entityMock
      ->expects($this->any())
      ->method('delete');

    $this->entityFieldManagerMock
      ->expects($this->once())
      ->method('getFieldDefinitions')
      ->willReturn([
        'field_name' => $this->fieldConfigMock,
      ]);

    $this->entityFieldManagerMock
      ->expects($this->once())
      ->method('getFieldStorageDefinitions')
      ->willReturn([
        'field_name' => $this->fieldStorageDefMock,
      ]);

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
      ->method('delete');

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('field_name');

    $this->configMock
      ->expects($this->any())
      ->method('set');

    $this->configMock
      ->expects($this->any())
      ->method('save');

    $this->configMock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'product',
        'method' => 'manual',
        'entity_type' => 'node',
        'salsify_data_type' => 'complex',
      ]);

    $this->fieldStorageDefMock
      ->expects($this->any())
      ->method('getType')
      ->willReturn('type');

    $this->fieldStorageDefMock
      ->expects($this->any())
      ->method('getSettings')
      ->willReturn(['settings']);

    $this->entityTypeRepoMock
      ->expects($this->any())
      ->method('getEntityTypeFromClass')
      ->willReturn('entity_type');

    $this->entityStorageMock
      ->expects($this->any())
      ->method('create')
      ->willReturn($this->entityMock);

    $this->entityMock
      ->expects($this->any())
      ->method('save');

    $this->fieldConfigMock
      ->expects($this->any())
      ->method('getName')
      ->willReturn('field_name');

    $this->fieldConfigMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('label');

    $this->fieldConfigMock
      ->expects($this->any())
      ->method('delete');

    $this->moduleHandlerMock
      ->expects($this->any())
      ->method('alter');

    $this->salsifyEntityTypeUpdate->processItem($data);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->entityMock = $this->createMock(EntityInterface::class);
    $this->entityFieldManagerMock = $this->createMock(EntityFieldManagerInterface::class);
    $this->queueFactoryMock = $this->createMock(QueueFactory::class);
    $this->queryMock = $this->createMock(QueryInterface::class);
    $this->fieldDefMock = $this->createMock(FieldDefinitionInterface::class);
    $this->fieldStorageDefMock = $this->createMock(FieldStorageDefinitionInterface::class);
    $this->configMock = $this->createMock(ImmutableConfig::class);
    $this->entityTypeRepoMock = $this->createMock(EntityTypeRepositoryInterface::class);
    $this->moduleHandlerMock = $this->createMock(ModuleHandlerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->fieldConfigMock = $this->createMock(FieldConfig::class);
    $this->entityViewDisplayMock = $this->createMock(EntityViewDisplayInterface::class);
  }

}
