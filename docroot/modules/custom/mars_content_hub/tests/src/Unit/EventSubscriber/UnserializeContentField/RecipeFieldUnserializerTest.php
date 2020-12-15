<?php

namespace Drupal\Tests\mars_content_hub\Unit\EventSubscriber\UnserializeContentField;

use Acquia\ContentHubClient\CDF\CDFObject;
use Drupal\acquia_contenthub\AcquiaContentHubEvents;
use Drupal\acquia_contenthub\Event\UnserializeCdfEntityFieldEvent;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\depcalc\DependencyStack;
use Drupal\depcalc\DependentEntityWrapperInterface;
use Drupal\layout_builder\Plugin\Block\InlineBlock;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
use Drupal\mars_content_hub\EventSubscriber\UnserializeContentField\RecipeFieldUnserializer;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_content_hub\EventSubscriber\UnserializeContentField\RecipeFieldUnserializer
 * @group mars
 * @group mars_content_hub
 */
class RecipeFieldUnserializerTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_content_hub\EventSubscriber\UnserializeContentField\RecipeFieldUnserializer
   */
  private $serializer;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|EntityTypeManager
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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\acquia_contenthub\Event\UnserializeCdfEntityFieldEvent
   */
  private $eventMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityStorageInterface
   */
  private $entityStorageMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\FieldItemList
   */
  private $fieldItemListMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\FieldDefinitionInterface
   */
  private $fieldDefinitionMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Acquia\ContentHubClient\CDF\CDFObject
   */
  private $cdfObjectMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\TypedData\TypedDataInterface
   */
  private $typedDataMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\layout_builder\Section
   */
  private $sectionMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\layout_builder\SectionComponent
   */
  private $sectionComponentMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Block\BlockManager
   */
  private $blockManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Block\BlockBase
   */
  private $blockPluginMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Block\BlockBase
   */
  private $blockPluginMock2;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\depcalc\DependencyStack
   */
  private $stackMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\depcalc\DependentEntityWrapperInterface
   */
  private $dependencyMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->serializer = new RecipeFieldUnserializer(
      $this->entityTypeManagerMock
    );
  }

  /**
   * Test.
   */
  public function testShouldGetSubscribedEvents() {
    $events = $this->serializer::getSubscribedEvents();
    $this->assertIsArray($events);
    $this->assertNotEmpty($events);
    $this->assertArrayHasKey(AcquiaContentHubEvents::UNSERIALIZE_CONTENT_ENTITY_FIELD, $events);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManager::class);
    $this->entityMock = $this->createMock(Node::class);
    $this->eventMock = $this->createMock(UnserializeCdfEntityFieldEvent::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->fieldItemListMock = $this->createMock(FieldItemList::class);
    $this->fieldDefinitionMock = $this->createMock(FieldDefinitionInterface::class);
    $this->cdfObjectMock = $this->createMock(CDFObject::class);
    $this->typedDataMock = $this->createMock(TypedDataInterface::class);
    $this->sectionMock = $this->createMock(Section::class);
    $this->blockManagerMock = $this->createMock(BlockManager::class);
    $this->blockPluginMock = $this->createMock(InlineBlock::class);
    $this->blockPluginMock2 = $this->createMock(BlockBase::class);
    $this->stackMock = $this->createMock(DependencyStack::class);
    $this->dependencyMock = $this->createMock(DependentEntityWrapperInterface::class);
    $this->sectionComponentMock = new SectionComponent(
      'uuid123',
      'region'
    );
  }

}
