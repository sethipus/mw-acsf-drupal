<?php

namespace Drupal\Tests\salsify_integration\Unit;

use Drupal\salsify_integration\MulesoftConnector;
use Drupal\salsify_integration\ProductHelper;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\salsify_integration\MulesoftConnector
 * @group mars
 * @group salsify_integration
 */
class MulesoftConnectorTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\MulesoftConnector
   */
  private $connector;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\ProductHelper
   */
  private $productHelperMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    $this->connector = new MulesoftConnector(
      $this->productHelperMock
    );
  }

  /**
   * Test.
   */
  public function testShouldTransformData() {
    $response = '{"products":["product"],"data":["data"],"country":["country"]}';

    $this->productHelperMock
      ->expects($this->once())
      ->method('filterProductsInResponse')
      ->willReturn($response);

    $this->productHelperMock
      ->expects($this->once())
      ->method('filterProductFields')
      ->willReturn($response);

    $this->productHelperMock
      ->expects($this->once())
      ->method('addProducts')
      ->willReturn($response);

    $this->productHelperMock
      ->expects($this->once())
      ->method('addProductMultipacks')
      ->willReturn($response);

    $this->productHelperMock
      ->expects($this->once())
      ->method('getAttributesByProducts')
      ->willReturn(['attributes']);

    $this->productHelperMock
      ->expects($this->once())
      ->method('getAttributeValuesByProducts')
      ->willReturn(['attribute_values']);

    $this->productHelperMock
      ->expects($this->once())
      ->method('getDigitalAssetsByProducts')
      ->willReturn(['digital_assets']);

    $data = $this->connector->transformData($response);
    $this->assertIsArray($data);
    $this->assertNotEmpty($data['market']);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->productHelperMock = $this->createMock(ProductHelper::class);
  }

}
