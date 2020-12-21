<?php

namespace Drupal\Tests\mars_content_hub\Unit\EventSubscriber\DependencyCollector;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\depcalc\DependencyCalculatorEvents;
use Drupal\depcalc\Event\SectionComponentDependenciesEvent;
use Drupal\layout_builder\SectionComponent;
use Drupal\mars_content_hub\EventSubscriber\DependencyCollector\RecipeFeatureBlockCollector;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_content_hub\EventSubscriber\DependencyCollector\RecipeFeatureBlockCollector
 * @group mars
 * @group mars_content_hub
 */
class RecipeFeatureBlockCollectorTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_content_hub\EventSubscriber\DependencyCollector\RecipeFeatureBlockCollector
   */
  private $collector;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\depcalc\Event\SectionComponentDependenciesEvent
   */
  private $sectionComponentDependenciesEventMock;

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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->collector = new RecipeFeatureBlockCollector(
      $this->entityTypeManagerMock
    );
  }

  /**
   * Test.
   */
  public function testShouldGetSubscribedEvents() {
    $events = $this->collector::getSubscribedEvents();
    $this->assertIsArray($events);
    $this->assertNotEmpty($events);
    $this->assertArrayHasKey(DependencyCalculatorEvents::SECTION_COMPONENT_DEPENDENCIES_EVENT, $events);
  }

  /**
   * Test.
   */
  public function testShouldOnCalculateSectionComponentDependencies() {
    $this->sectionComponentDependenciesEventMock
      ->expects($this->once())
      ->method('getComponent')
      ->willReturn(
        $this->sectionComponentMock
      );

    $this->sectionComponentMock
      ->expects($this->once())
      ->method('get')
      ->willReturn([
        'recipe_id' => 1,
      ]);

    $this->sectionComponentMock
      ->expects($this->once())
      ->method('getPluginId')
      ->willReturn('recipe_feature_block');

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn($this->entityMock);

    $this->sectionComponentDependenciesEventMock
      ->expects($this->atLeast(1))
      ->method('addEntityDependency');

    $this->collector->onCalculateSectionComponentDependencies(
      $this->sectionComponentDependenciesEventMock
    );
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->entityMock = $this->createMock(EntityInterface::class);
    $this->sectionComponentDependenciesEventMock = $this->createMock(SectionComponentDependenciesEvent::class);
    $this->sectionComponentMock = $this->createMock(SectionComponent::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
  }

}
