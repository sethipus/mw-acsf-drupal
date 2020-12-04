<?php

namespace Drupal\Tests\mars_google_analytics\Unit\DataCollector;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\mars_google_analytics\DataCollector\ProductsDataCollector;
use Drupal\mars_google_analytics\Entity\EntityDecorator;
use Drupal\mars_google_analytics\Entity\EntityManagerWrapper;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_google_analytics\DataCollector\ProductsDataCollector
 * @group mars
 * @group mars_google_analytics
 */
class ProductsDataCollectorTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_google_analytics\DataCollector\ProductsDataCollector
   */
  private $collector;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_google_analytics\Entity\EntityManagerWrapper
   */
  private $entityManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_google_analytics\Entity\EntityDecorator
   */
  private $entityDecoratorMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\node\NodeInterface
   */
  private $nodeMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\EntityReferenceFieldItemListInterface
   */
  private $fieldItemListMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->collector = new ProductsDataCollector(
      $this->entityManagerMock
    );
  }

  /**
   * Test.
   */
  public function testShouldGetDataLayerId() {
    $id = $this->collector->getDataLayerId();
    $this->assertIsString($id);
  }

  /**
   * Test.
   */
  public function testShouldCollect() {
    $this->entityManagerMock
      ->expects($this->once())
      ->method('getRendered')
      ->willReturn($this->entityDecoratorMock);

    $this->entityDecoratorMock
      ->expects($this->once())
      ->method('getEntities')
      ->willReturn([$this->nodeMock]);

    $this->nodeMock
      ->expects($this->once())
      ->method('bundle')
      ->willReturn('product');

    $this->nodeMock
      ->expects($this->once())
      ->method('hasField')
      ->willReturn(TRUE);

    $this->fieldItemListMock
      ->expects($this->once())
      ->method('referencedEntities')
      ->willReturn([$this->nodeMock]);

    $this->fieldItemListMock
      ->expects($this->any())
      ->method('__get')
      ->willReturn('123123');

    $this->nodeMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->fieldItemListMock);

    $this->collector->collect();
  }

  /**
   * Test.
   */
  public function testShouldGetGaData() {
    $this->entityManagerMock
      ->expects($this->once())
      ->method('getRendered')
      ->willReturn($this->entityDecoratorMock);

    $this->entityDecoratorMock
      ->expects($this->once())
      ->method('getEntities')
      ->willReturn([$this->nodeMock]);

    $this->nodeMock
      ->expects($this->once())
      ->method('bundle')
      ->willReturn('product');

    $this->nodeMock
      ->expects($this->once())
      ->method('hasField')
      ->willReturn(TRUE);

    $this->fieldItemListMock
      ->expects($this->once())
      ->method('referencedEntities')
      ->willReturn([$this->nodeMock]);

    $this->fieldItemListMock
      ->expects($this->any())
      ->method('__get')
      ->willReturn('123123');

    $this->nodeMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->fieldItemListMock);

    $this->collector->collect();
    $ga_data = $this->collector->getGaData();
    $this->assertNotEmpty($ga_data);
  }

  /**
   * Test.
   */
  public function testShouldGetAndAddRenderedProducts() {
    $this->collector->addRenderedProduct('123123');
    $products = $this->collector->getRenderedProducts();
    $this->assertNotEmpty($products);
    $this->assertSame('123123', $products[123123]);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->entityManagerMock = $this->createMock(EntityManagerWrapper::class);
    $this->nodeMock = $this->createMock(NodeInterface::class);
    $this->entityDecoratorMock = $this->createMock(EntityDecorator::class);
    $this->fieldItemListMock = $this->createMock(EntityReferenceFieldItemListInterface::class);
  }

}
