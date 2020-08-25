<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\mars_common\Plugin\Field\FieldType\CarouselItem;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\TypedData\FieldItemDataDefinitionInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;

/**
 * @coversDefaultClass \Drupal\mars_common\Plugin\Field\FieldType\CarouselItem
 * @group mars
 * @group mars_common
 */
class CarouselItemTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_common\Plugin\Field\FieldType\CarouselItem
   */
  private $carouselItem;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\TypedData\FieldItemDataDefinitionInterface
   */
  private $definitionMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\TypedData\DataDefinitionInterface
   */
  private $dataDefinitionMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\TypedData\TypedDataManagerInterface
   */
  private $typedDataManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\TypedData\TypedDataInterface
   */
  private $typedDataMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\FieldStorageDefinitionInterface
   */
  private $fieldStorageMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $this->carouselItem = new CarouselItem(
      $this->definitionMock
    );
  }

  /**
   * Test.
   */
  public function testSchema() {
    $schema = $this->carouselItem::schema($this->fieldStorageMock);
    $this->assertIsArray($schema);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->definitionMock = $this->createMock(FieldItemDataDefinitionInterface::class);
    $this->dataDefinitionMock = $this->createMock(DataDefinitionInterface::class);
    $this->typedDataManagerMock = $this->createMock(TypedDataManagerInterface::class);
    $this->typedDataMock = $this->createMock(TypedDataInterface::class);
    $this->fieldStorageMock = $this->createMock(FieldStorageDefinitionInterface::class);

    $this->definitionMock
      ->expects($this->once())
      ->method('getPropertyDefinitions')
      ->willReturn([
        'target_id' => $this->dataDefinitionMock,
        'entity' => $this->dataDefinitionMock,
        'desc' => $this->dataDefinitionMock,
      ]);

    $this->dataDefinitionMock
      ->expects($this->any())
      ->method('isComputed')
      ->willReturn(TRUE);

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'typed_data_manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->typedDataManagerMock,
          ],
        ]
      );

    $this->typedDataManagerMock
      ->expects($this->any())
      ->method('getPropertyInstance')
      ->willReturn($this->typedDataMock);

  }

}
