<?php

namespace Drupal\Tests\salsify_integration\Unit\EventSubscriber;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\salsify_integration\Event\SalsifyGetEntityTypesEvent;
use Drupal\salsify_integration\EventSubscriber\SalsifyConfigEntityTypeSubscriber;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\salsify_integration\EventSubscriber\SalsifyConfigEntityTypeSubscriber
 * @group mars
 * @group salsify_integration
 */
class SalsifyConfigEntityTypeSubscriberTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\EventSubscriber\SalsifyConfigEntityTypeSubscriber
   */
  private $subscriber;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandlerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\Event\SalsifyGetEntityTypesEvent
   */
  private $eventMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeInterface
   */
  private $entityTypeMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->subscriber = new SalsifyConfigEntityTypeSubscriber(
      $this->moduleHandlerMock,
      $this->entityTypeManagerMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap(
        [
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
        ]
      );
    $this->subscriber::create($this->containerMock);
  }

  /**
   * Test.
   */
  public function testShouldGetSubscribedEvents() {
    $events = $this->subscriber::getSubscribedEvents();
    $this->assertIsArray($events);
  }

  /**
   * Test.
   */
  public function testShouldAddEntityTypes() {
    $this->eventMock
      ->expects($this->once())
      ->method('getEntityTypesList')
      ->willReturn([]);

    $this->entityTypeManagerMock
      ->expects($this->once())
      ->method('getDefinitions')
      ->willReturn([
        $this->entityTypeMock,
      ]);

    $this->moduleHandlerMock
      ->expects($this->any())
      ->method('moduleExists')
      ->willReturn(TRUE);

    $this->entityTypeMock
      ->expects($this->once())
      ->method('getProvider')
      ->willReturn('eck');

    $this->entityTypeMock
      ->expects($this->once())
      ->method('getGroup')
      ->willReturn('content');

    $this->entityTypeMock
      ->expects($this->once())
      ->method('id')
      ->willReturn('id');

    $this->entityTypeMock
      ->expects($this->once())
      ->method('getLabel')
      ->willReturn('label');

    $this->containerMock
      ->expects($this->exactly(1))
      ->method('get')
      ->willReturnMap(
        [
          [
            'string_translation',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->translationMock,
          ],
        ]
      );

    $this->eventMock
      ->expects($this->once())
      ->method('setEntityTypesList');

    $this->subscriber->addEntityTypes($this->eventMock);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->moduleHandlerMock = $this->createMock(ModuleHandlerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->eventMock = $this->createMock(SalsifyGetEntityTypesEvent::class);
    $this->entityTypeMock = $this->createMock(EntityTypeInterface::class);
  }

}
