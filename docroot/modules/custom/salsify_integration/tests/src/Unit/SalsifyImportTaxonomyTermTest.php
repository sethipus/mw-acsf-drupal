<?php

namespace Drupal\Tests\salsify_integration\Unit;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\salsify_integration\Salsify;
use Drupal\salsify_integration\SalsifyImportTaxonomyTerm;
use Drupal\taxonomy\Entity\Term;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\salsify_integration\SalsifyImportTaxonomyTerm
 * @group mars
 * @group salsify_integration
 */
class SalsifyImportTaxonomyTermTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\SalsifyImportTaxonomyTerm
   */
  private $salsifyImportTaxonomyTerm;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\taxonomy\Entity\Term
   */
  private $termMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\Query\QueryInterface
   */
  private $queryMock;

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
    $this->salsifyImportTaxonomyTerm = new SalsifyImportTaxonomyTerm(
      $this->configFactoryMock,
      $this->entityTypeManagerMock,
      $this->cacheBackendMock,
      $this->salsifyMock,
      $this->moduleHandlerMock
    );
  }

  /**
   * Test.
   */
  public function testShouldProcessSalsifyItem() {
    $field = [
      'salsify_id' => 'field_salsify_id',
    ];
    $salsify_field_data = [];

    $this->salsifyMock
      ->expects($this->once())
      ->method('getProductData')
      ->willReturn([
        'fields' => [
          'field_salsify_id' => [
            'salsify:created_at' => 'now',
            'date_updated' => 'now',
            'salsify:data_type' => 'data_type',
            'salsify:system_id' => 'field_salsify_id',
            'salsify:id' => 'field_salsify_id',
            'values' => [
              'value' => [
                'salsify:name' => 'other value',
              ],
              '5' => [
                'salsify:name' => 'other value',
                'salsify:id' => 'id',
              ],
              '6' => [
                'salsify:name' => 'other value',
                'salsify:id' => 'id',
              ],
              '7' => [
                'salsify:name' => 'other value',
                'salsify:id' => 'id',
              ],
            ],
          ],
        ],
      ]);

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
      ->expects($this->any())
      ->method('load')
      ->willReturn($this->entityMock);

    $this->entityStorageMock
      ->expects($this->once())
      ->method('getQuery')
      ->willReturn($this->queryMock);

    $this->entityStorageMock
      ->expects($this->once())
      ->method('loadMultiple')
      ->willReturn([
        $this->termMock,
      ]);

    $this->entityStorageMock
      ->expects($this->exactly(3))
      ->method('create')
      ->willReturn($this->termMock);

    $this->termMock
      ->expects($this->any())
      ->method('save');

    $this->termMock
      ->expects($this->any())
      ->method('__get')
      ->willReturn($this->fieldItemListMock);

    $this->termMock
      ->expects($this->any())
      ->method('set');

    $this->termMock
      ->expects($this->any())
      ->method('save');

    $this->fieldItemListMock
      ->expects($this->any())
      ->method('__get')
      ->willReturn('value');

    $this->queryMock
      ->expects($this->any())
      ->method('condition')
      ->willReturnSelf();

    $this->queryMock
      ->expects($this->once())
      ->method('execute')
      ->willReturn([1, 2, 3]);

    $this->salsifyImportTaxonomyTerm
      ->processSalsifyTaxonomyTermItems(
      'vocabulary_id',
        $field,
        [5, 6, 7],
        $salsify_field_data
    );
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
    $this->salsifyMock = $this->createMock(Salsify::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->entityMock = $this->createMock(EntityInterface::class);
    $this->queryMock = $this->createMock(QueryInterface::class);
    $this->termMock = $this->createMock(Term::class);
    $this->fieldItemListMock = $this->createMock(FieldItemListInterface::class);

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->configMock);
  }

}
