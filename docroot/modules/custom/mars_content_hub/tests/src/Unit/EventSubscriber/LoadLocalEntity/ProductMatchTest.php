<?php

namespace Drupal\Tests\mars_content_hub\Unit\EventSubscriber\LoadLocalEntity;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDFAttribute;
use Drupal\acquia_contenthub\AcquiaContentHubEvents;
use Drupal\acquia_contenthub\Event\LoadLocalEntityEvent;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\depcalc\DependencyStack;
use Drupal\layout_builder\SectionComponent;
use Drupal\mars_content_hub\EventSubscriber\LoadLocalEntity\ProductMatch;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_content_hub\EventSubscriber\LoadLocalEntity\ProductMatch
 * @group mars
 * @group mars_content_hub
 */
class ProductMatchTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_content_hub\EventSubscriber\LoadLocalEntity\ProductMatch
   */
  private $productMatch;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityInterface
   */
  private $entityMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\acquia_contenthub\Event\LoadLocalEntityEvent
   */
  private $eventMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Acquia\ContentHubClient\CDF\CDFObject
   */
  private $cdfObjectMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Acquia\ContentHubClient\CDFAttribute
   */
  private $cdfAttributeMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\layout_builder\SectionComponent
   */
  private $sectionComponentMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityStorageInterface
   */
  private $entityStorageMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\depcalc\DependencyStack
   */
  private $dependencyStackMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->productMatch = new ProductMatch(
      $this->entityTypeManagerMock
    );
  }

  /**
   * Test.
   */
  public function testShouldGetSubscribedEvents() {
    $events = $this->productMatch::getSubscribedEvents();
    $this->assertIsArray($events);
    $this->assertNotEmpty($events);
    $this->assertArrayHasKey(AcquiaContentHubEvents::LOAD_LOCAL_ENTITY, $events);
  }

  /**
   * Test.
   */
  public function testShouldOnLoadLocalEntity() {
    $this->eventMock
      ->expects($this->once())
      ->method('getCdf')
      ->willReturn(
        $this->cdfObjectMock
      );

    $this->cdfObjectMock
      ->expects($this->any())
      ->method('getUuid')
      ->willReturn(
        '123123123'
      );

    $data = new \stdClass();
    $data->salsify_id = new \stdClass();
    $data->salsify_id->value = new \stdClass();
    $data->salsify_id->value->en = [
      1231231232,
    ];

    $this->entityTypeManagerMock
      ->expects($this->once())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->once())
      ->method('loadByProperties')
      ->willReturn([
        $this->entityMock,
      ]);

    $this->entityMock
      ->expects($this->once())
      ->method('uuid')
      ->willReturn('123123123');

    $this->eventMock
      ->expects($this->once())
      ->method('setEntity');

    $this->cdfObjectMock
      ->expects($this->any())
      ->method('getMetadata')
      ->willReturn([
        'default_language' => 'en',
        'data' => base64_encode(json_encode(
          $data
        )),
      ]);

    $this->eventMock
      ->expects($this->any())
      ->method('getStack')
      ->willReturn(
        $this->dependencyStackMock
      );

    $this->dependencyStackMock
      ->expects($this->any())
      ->method('addDependency');

    $this->dependencyStackMock
      ->expects($this->once())
      ->method('hasDependency')
      ->willReturn(FALSE);

    $this->cdfObjectMock
      ->expects($this->once())
      ->method('getAttribute')
      ->willReturn(
        $this->cdfAttributeMock
      );

    $this->cdfAttributeMock
      ->expects($this->any())
      ->method('getValue')
      ->willReturn([
        CDFObject::LANGUAGE_UNDETERMINED => 'product',
      ]);

    $this->productMatch->onLoadLocalEntity(
      $this->eventMock
    );
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->entityMock = $this->createMock(EntityInterface::class);
    $this->eventMock = $this->createMock(LoadLocalEntityEvent::class);
    $this->sectionComponentMock = $this->createMock(SectionComponent::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->cdfObjectMock = $this->createMock(CDFObject::class);
    $this->cdfAttributeMock = $this->createMock(CDFAttribute::class);
    $this->dependencyStackMock = $this->createMock(DependencyStack::class);
  }

}
