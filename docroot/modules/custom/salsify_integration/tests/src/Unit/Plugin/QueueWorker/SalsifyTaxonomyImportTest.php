<?php

namespace Drupal\Tests\salsify_integration\Unit\Plugin\QueueWorker;

use Drupal\salsify_integration\Plugin\QueueWorker\SalsifyTaxonomyImport;
use Drupal\salsify_integration\SalsifyImportTaxonomyTerm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\salsify_integration\Plugin\QueueWorker\SalsifyTaxonomyImport
 * @group mars
 * @group salsify_integration
 */
class SalsifyTaxonomyImportTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\Plugin\QueueWorker\SalsifyTaxonomyImport
   */
  private $salsifyTaxonomyImport;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\SalsifyImportTaxonomyTerm
   */
  private $salsifyImportTaxonomyMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->salsifyTaxonomyImport = new SalsifyTaxonomyImport(
      $this->salsifyImportTaxonomyMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(1))
      ->method('get')
      ->willReturnMap(
        [
          [
            'salsify_integration.salsify_import_taxonomy',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyImportTaxonomyMock,
          ],
        ]
      );

    $this->salsifyTaxonomyImport::create(
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
      'field_mapping' => ['mapping'],
      'salisfy_ids' => [1, 2, 3],
      'salsify_field_data' => ['field_data'],
    ];

    $this->salsifyImportTaxonomyMock
      ->expects($this->once())
      ->method('processSalsifyTaxonomyTermItems');

    $this->salsifyTaxonomyImport->processItem($data);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->salsifyImportTaxonomyMock = $this->createMock(SalsifyImportTaxonomyTerm::class);
  }

}
