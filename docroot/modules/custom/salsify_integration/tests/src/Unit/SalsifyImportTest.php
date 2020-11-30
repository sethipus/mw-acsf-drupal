<?php

namespace Drupal\Tests\salsify_integration\Unit;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\salsify_integration\ProductHelper;
use Drupal\salsify_integration\Salsify;
use Drupal\salsify_integration\SalsifyImport;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\salsify_integration\SalsifyImport
 * @group mars
 * @group salsify_integration
 */
class SalsifyImportTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\SalsifyImport
   */
  private $salsifyImport;

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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->salsifyImport = new SalsifyImport(
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

    $result = $this->salsifyImport::processSalsifyItem(
      [],
      TRUE,
      ProductHelper::PRODUCT_CONTENT_TYPE
    );
    $this->assertNotEmpty($result);
    $this->assertIsArray($result);
    $this->assertArrayHasKey('import_result', $result);
  }

  /**
   * Test.
   */
  public function testShouldGetFieldOptions() {
    $field = [
      'salsify_data_type' => 'link',
    ];
    $field_data = [];

    $options = $this->salsifyImport::getFieldOptions(
      $field,
      $field_data
    );
    $this->assertNotEmpty($options);
    $this->assertIsArray($options);

    $field['salsify_data_type'] = 'date';
    $options = $this->salsifyImport::getFieldOptions(
      $field,
      $field_data
    );
    $this->assertNotEmpty($options);
    $this->assertIsArray($options);

    $field['salsify_data_type'] = 'enumerated';
    $options = $this->salsifyImport::getFieldOptions(
      $field,
      'value'
    );
    $this->assertNotEmpty($options);
    $this->assertIsArray($options);

    $field['salsify_data_type'] = 'rich_text';
    $options = $this->salsifyImport::getFieldOptions(
      $field,
      'value'
    );
    $this->assertNotEmpty($options);
    $this->assertIsArray($options);
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

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->configMock);
  }

}
