<?php

namespace Drupal\Tests\salsify_integration\Unit\EventSubscriber;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\salsify_integration\EventSubscriber\SalsifySubscriber;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\salsify_integration\EventSubscriber\SalsifySubscriber
 * @group mars
 * @group salsify_integration
 */
class SalsifySubscriberTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\EventSubscriber\SalsifySubscriber
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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Queue\QueueFactory
   */
  private $queueFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Queue\QueueInterface
   */
  private $queueMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigCrudEvent
   */
  private $eventMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\Config
   */
  private $configMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->subscriber = new SalsifySubscriber(
      $this->queueFactoryMock
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
            'queue',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->queueFactoryMock,
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
  public function testShouldCheckContentTypeFields() {
    $this->eventMock
      ->expects($this->once())
      ->method('getConfig')
      ->willReturn($this->configMock);

    $this->configMock
      ->expects($this->once())
      ->method('getName')
      ->willReturn('salsify_integration.settings');

    $this->eventMock
      ->expects($this->once())
      ->method('isChanged')
      ->willReturn(TRUE);

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('test_data');

    $this->configMock
      ->expects($this->any())
      ->method('getOriginal')
      ->willReturn('test_original_data');

    $this->queueFactoryMock
      ->expects($this->once())
      ->method('get')
      ->willReturn($this->queueMock);

    $this->queueMock
      ->expects($this->once())
      ->method('createItem');

    $this->subscriber->checkContentTypeFields($this->eventMock);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->queueFactoryMock = $this->createMock(QueueFactory::class);
    $this->eventMock = $this->createMock(ConfigCrudEvent::class);
    $this->configMock = $this->createMock(Config::class);
    $this->queueMock = $this->createMock(QueueInterface::class);
  }

}
