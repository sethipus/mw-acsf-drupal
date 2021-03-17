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
use Drupal\mars_content_hub\EventSubscriber\UnserializeContentField\MediaFieldUnserializer;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_content_hub\EventSubscriber\UnserializeContentField\MediaFieldUnserializer
 * @group mars
 * @group mars_content_hub
 */
class MediaFieldUnserializerTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_content_hub\EventSubscriber\UnserializeContentField\MediaFieldUnserializer
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
    $this->serializer = new MediaFieldUnserializer(
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
   * Test.
   */
  public function testShouldOnSerializeContentFieldWhenInlineBlock() {
    $this->eventMock
      ->expects($this->any())
      ->method('getField')
      ->willReturn([
        'value' => [
          'en' => [
            [
              'section' => [
                'layout_id' => 'layout_id',
                'layout_settings' => [],
                'components' => [
                  [
                    'uuid' => 'componentuuid',
                    'region' => 'region',
                    'configuration' => [
                      'id' => 'plugin_id',
                    ],
                    'additional' => [],
                    'weight' => 100,
                  ],
                ],
                'third_party_settings' => [],
              ],
            ],
          ],
        ],
      ]);

    $this->eventMock
      ->expects($this->any())
      ->method('getFieldMetadata')
      ->willReturn([
        'type' => 'layout_section',
      ]);

    $this->fieldItemListMock
      ->expects($this->any())
      ->method('getFieldDefinition')
      ->willReturn(
        $this->fieldDefinitionMock
      );

    $this->fieldDefinitionMock
      ->expects($this->any())
      ->method('getType')
      ->willReturn('layout_section');

    $this->eventMock
      ->expects($this->once())
      ->method('getFieldName')
      ->willReturn('field_name');

    $this->eventMock
      ->expects($this->once())
      ->method('setValue');

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
      ->method('loadByProperties')
      ->willReturn([$this->entityMock]);

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

    $this->serializer->onUnserializeContentField(
      $this->eventMock
    );
  }

  /**
   * Test.
   */
  public function testShouldOnSerializeContentFieldWhenRecipeFeature() {
    $this->eventMock
      ->expects($this->any())
      ->method('getField')
      ->willReturn([
        'value' => [
          'en' => [
            [
              'section' => [
                'layout_id' => 'layout_id',
                'layout_settings' => [],
                'components' => [
                  [
                    'uuid' => 'componentuuid',
                    'region' => 'region',
                    'configuration' => [
                      'id' => 'recipe_feature_block',
                      'recipe_id' => 123,
                    ],
                    'additional' => [],
                    'weight' => 100,
                  ],
                ],
                'third_party_settings' => [],
              ],
            ],
          ],
        ],
      ]);

    $this->eventMock
      ->expects($this->any())
      ->method('getFieldMetadata')
      ->willReturn([
        'type' => 'layout_section',
      ]);

    $this->eventMock
      ->expects($this->any())
      ->method('getStack')
      ->willReturn(
        $this->stackMock
      );

    $this->stackMock
      ->expects($this->any())
      ->method('getDependency')
      ->willReturn(
        $this->dependencyMock
      );

    $this->stackMock
      ->expects($this->any())
      ->method('hasDependency')
      ->willReturn(TRUE);

    $this->dependencyMock
      ->expects($this->any())
      ->method('getEntity')
      ->willReturn(
        $this->entityMock
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
      ->willReturn('layout_section');

    $this->eventMock
      ->expects($this->any())
      ->method('getFieldName')
      ->willReturn('field_name');

    $this->eventMock
      ->expects($this->once())
      ->method('setValue');

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
      ->method('loadByProperties')
      ->willReturn([$this->entityMock]);

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

    $this->serializer->onUnserializeContentField(
      $this->eventMock
    );
  }

  /**
   * Test.
   */
  public function testShouldOnSerializeContentFieldWhenOtherPlugins() {
    $this->eventMock
      ->expects($this->any())
      ->method('getField')
      ->willReturn([
        'value' => [
          'en' => [
            [
              'section' => [
                'layout_id' => 'layout_id',
                'layout_settings' => [],
                'components' => [
                  [
                    'uuid' => 'componentuuid',
                    'region' => 'region',
                    'configuration' => [
                      'id' => 'another_id',
                      'recipe_id' => 123,
                      'media' => ['media:123'],
                    ],
                    'additional' => [],
                    'weight' => 100,
                  ],
                ],
                'third_party_settings' => [],
              ],
            ],
          ],
        ],
      ]);

    $this->eventMock
      ->expects($this->any())
      ->method('getFieldMetadata')
      ->willReturn([
        'type' => 'layout_section',
      ]);

    $this->eventMock
      ->expects($this->any())
      ->method('getStack')
      ->willReturn(
        $this->stackMock
      );

    $this->stackMock
      ->expects($this->any())
      ->method('getDependency')
      ->willReturn(
        $this->dependencyMock
      );

    $this->stackMock
      ->expects($this->any())
      ->method('hasDependency')
      ->willReturn(TRUE);

    $this->dependencyMock
      ->expects($this->any())
      ->method('getEntity')
      ->willReturn(
        $this->entityMock
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
      ->willReturn('layout_section');

    $this->eventMock
      ->expects($this->any())
      ->method('getFieldName')
      ->willReturn('field_name');

    $this->eventMock
      ->expects($this->once())
      ->method('setValue');

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
      ->method('loadByProperties')
      ->willReturn([$this->entityMock]);

    $this->entityMock
      ->expects($this->once())
      ->method('id')
      ->willReturn(123);

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

    $this->serializer->onUnserializeContentField(
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
