<?php

namespace Drupal\Tests\salsify_integration\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\salsify_integration\ProductFieldsMapper;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\salsify_integration\ProductFieldsMapper
 * @group mars
 * @group salsify_integration
 */
class ProductFieldsMapperTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\ProductFieldsMapper
   */
  private $productFieldsMapper;

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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->productFieldsMapper = new ProductFieldsMapper();
  }

  /**
   * Test.
   */
  public function testShouldAddProductFieldsMappingWhenProductVariant() {
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
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
        ]
      );

    $this->moduleHandlerMock
      ->expects($this->any())
      ->method('alter');

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
      ->method('set');

    $this->configMock
      ->expects($this->any())
      ->method('save');

    $this->configMock
      ->expects($this->exactly(5))
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'product',
        'method' => 'manual',
        'entity_type' => 'node',
        'salsify_data_type' => 'complex',
      ]);

    $this->productFieldsMapper->addProductFieldsMapping('node');
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->moduleHandlerMock = $this->createMock(ModuleHandlerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->configMock = $this->createMock(ImmutableConfig::class);
  }

}
