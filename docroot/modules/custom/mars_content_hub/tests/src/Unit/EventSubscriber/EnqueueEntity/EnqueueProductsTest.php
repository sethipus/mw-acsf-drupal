<?php

namespace Drupal\Tests\mars_content_hub\Unit\EventSubscriber\EnqueueEntity;

use Acquia\ContentHubClient\Settings;
use Drupal\acquia_contenthub_publisher\ContentHubPublisherEvents;
use Drupal\mars_content_hub\Event\ContentHubEntityEligibilityEvent;
use Drupal\Core\Entity\EntityInterface;
use Drupal\mars_content_hub\EventSubscriber\EnqueueEntity\EnqueueProducts;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_content_hub\EventSubscriber\EnqueueEntity\EnqueueProducts
 * @group mars
 * @group mars_content_hub
 */
class EnqueueProductsTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_content_hub\EventSubscriber\EnqueueEntity\EnqueueProducts
   */
  private $enqueueProducts;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Acquia\ContentHubClient\Settings
   */
  private $settingsMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityInterface
   */
  private $entityMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\acquia_contenthub_publisher\Event\ContentHubEntityEligibilityEvent
   */
  private $contentHubEntityEligibilityEventMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->enqueueProducts = new EnqueueProducts(
      $this->settingsMock
    );
  }

  /**
   * Test.
   */
  public function testShouldGetSubscribedEvents() {
    $events = $this->enqueueProducts::getSubscribedEvents();
    $this->assertIsArray($events);
    $this->assertNotEmpty($events);
    $this->assertArrayHasKey(ContentHubPublisherEvents::ENQUEUE_CANDIDATE_ENTITY, $events);
  }

  /**
   * Test.
   */
  public function testShouldOnEnqueueCandidateEntity() {
    $this->contentHubEntityEligibilityEventMock
      ->expects($this->once())
      ->method('getEntity')
      ->willReturn($this->entityMock);

    $this->entityMock
      ->expects($this->once())
      ->method('bundle')
      ->willReturn('product');

    $this->contentHubEntityEligibilityEventMock
      ->expects($this->once())
      ->method('setEligibility');

    $this->contentHubEntityEligibilityEventMock
      ->expects($this->once())
      ->method('stopPropagation');

    $this->enqueueProducts->onEnqueueCandidateEntity(
      $this->contentHubEntityEligibilityEventMock
    );
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->settingsMock = $this->createMock(Settings::class);
    $this->entityMock = $this->createMock(EntityInterface::class);
    $this->contentHubEntityEligibilityEventMock = $this->createMock(ContentHubEntityEligibilityEvent::class);
  }

}
