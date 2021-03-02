<?php

namespace Drupal\Tests\salsify_integration\Unit;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\node\Entity\Node;
use Drupal\salsify_integration\ProductHelper;
use Drupal\salsify_integration\SalsifyProductRepository;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\salsify_integration\SalsifyProductRepository
 * @group mars
 * @group salsify_integration
 */
class SalsifyProductRepositoryTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\SalsifyProductRepository
   */
  private $salsifyProductRepoImport;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\Query\QueryInterface
   */
  private $queryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\FieldableEntityInterface
   */
  private $entityMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\node\Entity\Node
   */
  private $nodeMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\FieldItemList
   */
  private $fieldItemListMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->salsifyProductRepoImport = new SalsifyProductRepository(
      $this->entityTypeManagerMock
    );
  }

  /**
   * Test.
   */
  public function testShouldupdateParentEntities() {
    $product_data = [
      'Parent GTIN' => '12313',
      'CMS: content type' => ProductHelper::PRODUCT_VARIANT_CONTENT_TYPE,
    ];

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('getQuery')
      ->willReturn($this->queryMock);

    $this->entityStorageMock
      ->expects($this->once())
      ->method('loadMultiple')
      ->willReturn([$this->nodeMock]);

    $this->nodeMock
      ->expects($this->any())
      ->method('hasField')
      ->willReturn(TRUE);

    $this->nodeMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->fieldItemListMock);

    $this->nodeMock
      ->expects($this->any())
      ->method('set');

    $this->nodeMock
      ->expects($this->any())
      ->method('save');

    $this->fieldItemListMock
      ->expects($this->any())
      ->method('getValue')
      ->willReturn([['target_id' => 123]]);

    $this->entityMock
      ->expects($this->any())
      ->method('id')
      ->willReturn('id');

    $this->queryMock
      ->expects($this->once())
      ->method('condition')
      ->willReturnSelf();

    $this->queryMock
      ->expects($this->once())
      ->method('execute')
      ->willReturn([1, 2, 3]);

    $this->salsifyProductRepoImport->updateParentEntities(
      $product_data,
      $this->entityMock
    );
  }

  /**
   * Test.
   */
  public function testShouldUnpublishProducts() {
    $products = [1, 2, 3];

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('getQuery')
      ->willReturn($this->queryMock);

    $this->entityStorageMock
      ->expects($this->once())
      ->method('loadMultiple')
      ->willReturn([$this->nodeMock]);

    $this->entityStorageMock
      ->expects($this->once())
      ->method('delete');

    $this->nodeMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->fieldItemListMock);

    $this->nodeMock
      ->expects($this->any())
      ->method('save');

    $this->fieldItemListMock
      ->expects($this->any())
      ->method('__get')
      ->willReturn(10);

    $this->queryMock
      ->expects($this->any())
      ->method('condition')
      ->willReturnSelf();

    $this->queryMock
      ->expects($this->once())
      ->method('execute')
      ->willReturn([1, 2, 3]);

    $result = $this->salsifyProductRepoImport->unpublishProducts(
      $products
    );
    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
    $this->assertSame(
      10,
      $result[0]
    );
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->queryMock = $this->createMock(QueryInterface::class);
    $this->entityMock = $this->createMock(FieldableEntityInterface::class);
    $this->fieldItemListMock = $this->createMock(FieldItemList::class);
    $this->nodeMock = $this->createMock(Node::class);
  }

}
