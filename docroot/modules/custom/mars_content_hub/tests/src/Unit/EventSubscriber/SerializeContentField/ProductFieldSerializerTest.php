<?php

namespace Drupal\Tests\mars_content_hub\Unit\EventSubscriber\SerializeContentField;

use Acquia\ContentHubClient\CDF\CDFObject;
use Drupal\acquia_contenthub\AcquiaContentHubEvents;
use Drupal\acquia_contenthub\Event\SerializeCdfEntityFieldEvent;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
use Drupal\mars_content_hub\EventSubscriber\SerializeContentField\ProductFieldSerializer;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_content_hub\EventSubscriber\SerializeContentField\ProductFieldSerializer
 * @group mars
 * @group mars_content_hub
 */
class ProductFieldSerializerTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_content_hub\EventSubscriber\SerializeContentField\ProductFieldSerializer
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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\node\NodeInterface
   */
  private $nodeMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\acquia_contenthub\Event\SerializeCdfEntityFieldEvent
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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->serializer = new ProductFieldSerializer(
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
    $this->assertArrayHasKey(AcquiaContentHubEvents::SERIALIZE_CONTENT_ENTITY_FIELD, $events);
  }

  /**
   * Test.
   */
  public function testShouldOnSerializeContentFieldWhenRecommendations() {
    $this->eventMock
      ->expects($this->any())
      ->method('getField')
      ->willReturn(
        $this->fieldItemListMock
      );

    $this->fieldItemListMock
      ->expects($this->any())
      ->method('getFieldDefinition')
      ->willReturn(
        $this->fieldDefinitionMock
      );

    $this->fieldDefinitionMock
      ->expects($this->any())
      ->method('getType')
      ->willReturn($this->serializer::FIELD_TYPE);

    $this->eventMock
      ->expects($this->once())
      ->method('getCdf')
      ->willReturn(
        $this->cdfObjectMock
      );

    $this->eventMock
      ->expects($this->once())
      ->method('getFieldName')
      ->willReturn('field_name');

    $this->cdfObjectMock
      ->expects($this->once())
      ->method('getMetadata')
      ->willReturn([
        'field' => [
          'field_name' => ['type' => NULL],
        ],
      ]);

    $this->cdfObjectMock
      ->expects($this->once())
      ->method('setMetadata');

    $this->eventMock
      ->expects($this->once())
      ->method('getEntity')
      ->willReturn($this->entityMock);

    $this->entityMock
      ->expects($this->once())
      ->method('getTranslationLanguages')
      ->willReturn([
        'en' => 'en',
      ]);

    $this->eventMock
      ->expects($this->once())
      ->method('getFieldTranslation')
      ->willReturn($this->fieldItemListMock);

    $this->fieldItemListMock
      ->expects($this->once())
      ->method('isEmpty')
      ->willReturn(FALSE);

    $this->fieldItemListMock
      ->expects($this->once())
      ->method('getIterator')
      ->willReturn(new \ArrayIterator([
        $this->typedDataMock,
      ]));

    $this->typedDataMock
      ->expects($this->once())
      ->method('getValue')
      ->willReturn([
        'section' => $this->sectionMock,
      ]);

    $components = [
      $this->sectionComponentMock,
    ];

    $this->sectionMock
      ->expects($this->any())
      ->method('getComponents')
      ->willReturn($components);

    $this->entityTypeManagerMock
      ->expects($this->once())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->once())
      ->method('load')
      ->willReturn($this->entityMock);

    $referenced_entity = new \stdClass();
    $referenced_entity->entity = $this->nodeMock;

    $this->nodeMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->fieldItemListMock);

    $this->fieldItemListMock
      ->expects($this->any())
      ->method('__get')
      ->willReturn('gtin123');

    $this->entityMock->field_product_variants = [$referenced_entity];

    $this->entityMock
      ->expects($this->any())
      ->method('__get')
      ->willReturn([$referenced_entity]);

    $this->sectionComponentMock->setConfiguration([
      'recipe_id' => 123,
      'id' => 'recommendations_module',
      'population_plugin_id' => 'manual',
      'population_plugin_configuration' => [
        'nodes' => [
          'key' => 1234,
        ],
      ],
    ]);

    $this->serializer->onSerializeContentField(
      $this->eventMock
    );
  }

  /**
   * Test.
   */
  public function testShouldOnSerializeContentFieldWhenPairUp() {
    $this->eventMock
      ->expects($this->any())
      ->method('getField')
      ->willReturn(
        $this->fieldItemListMock
      );

    $this->fieldItemListMock
      ->expects($this->any())
      ->method('getFieldDefinition')
      ->willReturn(
        $this->fieldDefinitionMock
      );

    $this->fieldDefinitionMock
      ->expects($this->any())
      ->method('getType')
      ->willReturn($this->serializer::FIELD_TYPE);

    $this->eventMock
      ->expects($this->once())
      ->method('getCdf')
      ->willReturn(
        $this->cdfObjectMock
      );

    $this->eventMock
      ->expects($this->once())
      ->method('getFieldName')
      ->willReturn('field_name');

    $this->cdfObjectMock
      ->expects($this->once())
      ->method('getMetadata')
      ->willReturn([
        'field' => [
          'field_name' => ['type' => NULL],
        ],
      ]);

    $this->cdfObjectMock
      ->expects($this->once())
      ->method('setMetadata');

    $this->eventMock
      ->expects($this->once())
      ->method('getEntity')
      ->willReturn($this->entityMock);

    $this->entityMock
      ->expects($this->once())
      ->method('getTranslationLanguages')
      ->willReturn([
        'en' => 'en',
      ]);

    $this->eventMock
      ->expects($this->once())
      ->method('getFieldTranslation')
      ->willReturn($this->fieldItemListMock);

    $this->fieldItemListMock
      ->expects($this->once())
      ->method('isEmpty')
      ->willReturn(FALSE);

    $this->fieldItemListMock
      ->expects($this->once())
      ->method('getIterator')
      ->willReturn(new \ArrayIterator([
        $this->typedDataMock,
      ]));

    $this->typedDataMock
      ->expects($this->once())
      ->method('getValue')
      ->willReturn([
        'section' => $this->sectionMock,
      ]);

    $components = [
      $this->sectionComponentMock,
    ];

    $this->sectionMock
      ->expects($this->any())
      ->method('getComponents')
      ->willReturn($components);

    $this->entityTypeManagerMock
      ->expects($this->once())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->once())
      ->method('load')
      ->willReturn($this->entityMock);

    $referenced_entity = new \stdClass();
    $referenced_entity->entity = $this->nodeMock;

    $this->nodeMock
      ->expects($this->once())
      ->method('get')
      ->willReturn($this->fieldItemListMock);

    $this->fieldItemListMock
      ->expects($this->once())
      ->method('__get')
      ->willReturn('gtin123');

    $this->entityMock->field_product_variants = [$referenced_entity];

    $this->entityMock
      ->expects($this->once())
      ->method('__get')
      ->willReturn([$referenced_entity]);

    $this->sectionComponentMock->setConfiguration([
      'recipe_id' => 123,
      'id' => 'product_content_pair_up_block',
      'population_plugin_id' => 'manual',
      'population_plugin_configuration' => [
        'nodes' => [
          'key' => 1234,
        ],
      ],
      'product' => 123,
    ]);

    $this->serializer->onSerializeContentField(
      $this->eventMock
    );
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManager::class);
    $this->entityMock = $this->createMock(Node::class);
    $this->nodeMock = $this->createMock(NodeInterface::class);
    $this->eventMock = $this->createMock(SerializeCdfEntityFieldEvent::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->fieldItemListMock = $this->createMock(FieldItemList::class);
    $this->fieldDefinitionMock = $this->createMock(FieldDefinitionInterface::class);
    $this->cdfObjectMock = $this->createMock(CDFObject::class);
    $this->typedDataMock = $this->createMock(TypedDataInterface::class);
    $this->sectionMock = $this->createMock(Section::class);
    $this->sectionComponentMock = new SectionComponent(
      'uuid123',
      'region'
    );
  }

}
