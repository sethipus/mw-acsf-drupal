<?php

namespace Drupal\Tests\mars_content_hub\Unit\EventSubscriber\SerializeContentField;

use Acquia\ContentHubClient\CDF\CDFObject;
use Drupal\acquia_contenthub\AcquiaContentHubEvents;
use Drupal\acquia_contenthub\Event\SerializeCdfEntityFieldEvent;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\layout_builder\Plugin\Block\InlineBlock;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
use Drupal\mars_content_hub\EventSubscriber\SerializeContentField\MediaFieldSerializer;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_content_hub\EventSubscriber\SerializeContentField\MediaFieldSerializer
 * @group mars
 * @group mars_content_hub
 */
class MediaFieldSerializerTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_content_hub\EventSubscriber\SerializeContentField\MediaFieldSerializer
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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->serializer = new MediaFieldSerializer(
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
  public function testShouldOnSerializeContentFieldWhenInlineBlock() {
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

    $this->eventMock
      ->expects($this->once())
      ->method('setFieldData');

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
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('loadRevision')
      ->willReturn($this->entityMock);

    $this->sectionComponentMock->setConfiguration([
      'recipe_id' => 123,
      'id' => 'plugin_id',
    ]);

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'plugin.manager.block',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->blockManagerMock,
          ],
        ]
      );

    $this->serializer->onSerializeContentField(
      $this->eventMock
    );
  }

  /**
   * Test.
   */
  public function testShouldOnSerializeContentFieldWhenNonInlineBlock() {
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

    $this->eventMock
      ->expects($this->once())
      ->method('setFieldData');

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
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('loadRevision')
      ->willReturn($this->entityMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn($this->entityMock);

    $this->entityMock
      ->expects($this->once())
      ->method('uuid')
      ->willReturn('uuid123');

    $this->sectionComponentMock->setConfiguration([
      'recipe_id' => 123,
      'id' => 'plugin_id',
      'files' => ['media:123'],
    ]);

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'plugin.manager.block',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->blockManagerMock,
          ],
        ]
      );

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
    $this->entityMock = $this->createMock(TranslatableInterface::class);
    $this->eventMock = $this->createMock(SerializeCdfEntityFieldEvent::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->fieldItemListMock = $this->createMock(FieldItemList::class);
    $this->fieldDefinitionMock = $this->createMock(FieldDefinitionInterface::class);
    $this->cdfObjectMock = $this->createMock(CDFObject::class);
    $this->typedDataMock = $this->createMock(TypedDataInterface::class);
    $this->sectionMock = $this->createMock(Section::class);
    $this->blockManagerMock = $this->createMock(BlockManager::class);
    $this->blockPluginMock = $this->createMock(InlineBlock::class);
    $this->blockPluginMock2 = $this->createMock(BlockBase::class);
    $this->sectionComponentMock = new SectionComponent(
      'uuid123',
      'region'
    );
  }

}
