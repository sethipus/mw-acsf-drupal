<?php

namespace Drupal\Tests\salsify_integration\Unit\Plugin\Derivative;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\salsify_integration\Plugin\Derivative\DynamicLocalTasks;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\salsify_integration\Plugin\Derivative\DynamicLocalTasks
 * @group mars
 * @group salsify_integration
 */
class DynamicLocalTasksTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\Plugin\Derivative\DynamicLocalTasks
   */
  private $localTasks;

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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->localTasks = new DynamicLocalTasks();
  }

  /**
   * Test.
   */
  public function testShouldGetDerivativeDefinitions() {
    $base_plugin_definition = [
      'id' => 'plugin_id',
    ];

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
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
        ]
      );

    $this->moduleHandlerMock
      ->expects($this->once())
      ->method('moduleExists')
      ->willReturn(TRUE);

    $this->entityTypeManagerMock
      ->expects($this->once())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->once())
      ->method('loadMultiple')
      ->willReturn([
        'type' => $this->entityMock,
      ]);

    $this->entityMock
      ->expects($this->once())
      ->method('label')
      ->willReturn('label');

    $derivatives = $this->localTasks
      ->getDerivativeDefinitions($base_plugin_definition);
    $this->assertIsArray($derivatives['plugin_id.type']);
    $this->assertSame(
      'label',
      $derivatives['plugin_id.type']['title']
    );
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->moduleHandlerMock = $this->createMock(ModuleHandlerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->entityMock = $this->createMock(EntityInterface::class);
  }

}
