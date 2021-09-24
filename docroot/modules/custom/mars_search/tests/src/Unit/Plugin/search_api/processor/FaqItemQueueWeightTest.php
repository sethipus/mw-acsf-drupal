<?php

namespace Drupal\Tests\mars_search\Unit\Plugin\search_api\processor;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\entityqueue\EntitySubqueueInterface;
use Drupal\mars_search\Plugin\search_api\processor\FaqItemQueueWeight;
use Drupal\node\NodeInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorProperty;
use Drupal\search_api\Utility\FieldsHelperInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_search\Plugin\search_api\processor\FaqItemQueueWeight
 * @group mars
 * @group mars_search
 */
class FaqItemQueueWeightTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Plugin\search_api\processor\FaqItemQueueWeight
   */
  private $processor;

  /**
   * Container mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface
   */
  private $containerMock;

  /**
   * Entity type manager mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManagerMock;

  /**
   * Translation mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translationMock;

  /**
   * FieldsHelper mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\search_api\Utility\FieldsHelperInterface
   */
  private $fieldsHelperMock;

  /**
   * Data source mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\search_api\Datasource\DatasourceInterface
   */
  private $dataSourceMock;

  /**
   * Storage mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityStorageInterface
   */
  private $entityStorageMock;

  /**
   * Entity mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\entityqueue\EntitySubqueueInterface
   */
  private $queueEntityMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap([
        [
          'string_translation',
          ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
          $this->translationMock,
        ],
        [
          'search_api.fields_helper',
          ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
          $this->fieldsHelperMock,
        ],
        [
          'entity_type.manager',
          ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
          $this->entityTypeManagerMock,
        ],
      ]);

    $this->processor = new FaqItemQueueWeight(
      [],
      'faq_item_queue_weight',
      []
    );
    $this->processor->setEntityTypeManager($this->entityTypeManagerMock);
    $this->processor->setFieldsHelper($this->fieldsHelperMock);
  }

  /**
   * Instantiation.
   */
  public function testShouldInstantiateProperly() {
    $processor = $this->processor::create(
      $this->containerMock,
      [],
      'faq_item_queue_weight',
      []
    );
    $this->assertInstanceOf(FaqItemQueueWeight::class, $processor);
    $this->assertObjectHasAttribute('entityTypeManager', $processor);
  }

  /**
   * Test get entity manager.
   */
  public function testGetEntityTypeManager() {
    $entityTypeManager = $this->processor->getEntityTypeManager();
    $this->assertInstanceOf(EntityTypeManagerInterface::class, $entityTypeManager);
  }

  /**
   * Test set entity manager.
   */
  public function testSetEntityTypeManager() {
    $processor = $this->processor->setEntityTypeManager($this->entityTypeManagerMock);
    $this->assertObjectHasAttribute('entityTypeManager', $processor);
  }

  /**
   * Test property definitions.
   */
  public function testGetPropertyDefinitions() {
    $properties = $this->processor->getPropertyDefinitions();
    $this->assertArrayHasKey('faq_item_queue_weight', $properties);
    $this->assertInstanceOf(ProcessorProperty::class, $properties['faq_item_queue_weight']);
  }

  /**
   * Test add field values method.
   */
  public function testAddFieldValues() {
    $this->entityTypeManagerMock
      ->expects($this->once())
      ->method('getStorage')
      ->with('entity_subqueue')
      ->willReturn($this->entityStorageMock);
    $this->entityStorageMock
      ->expects($this->once())
      ->method('load')
      ->with('faq_queue')
      ->willReturn($this->queueEntityMock);
    $this->queueEntityMock();

    $itemMock = $this->getMockBuilder(ItemInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $nodeMock = $this->getMockBuilder(NodeInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $nodeMock
      ->expects($this->once())
      ->method('id')
      ->willReturn(12345);
    $complexDataMock = $this->getMockBuilder(ComplexDataInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $searchApiFieldItem = $this->getMockBuilder(FieldInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $searchApiFieldItem
      ->expects($this->once())
      ->method('addValue')
      ->with('test_weight');

    $itemMock
      ->expects($this->once())
      ->method('getOriginalObject')
      ->willReturn($complexDataMock);
    $complexDataMock
      ->expects($this->once())
      ->method('getValue')
      ->willReturn($nodeMock);

    $itemMock
      ->expects($this->once())
      ->method('getFields')
      ->willReturn([]);
    $this->fieldsHelperMock
      ->expects($this->once())
      ->method('filterForPropertyPath')
      ->willReturn([$searchApiFieldItem]);

    $this->processor->addFieldValues($itemMock);
  }

  /**
   * Queue entity mock helper.
   */
  protected function queueEntityMock() {
    $fieldList = $this->getMockBuilder(FieldItemList::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->queueEntityMock
      ->expects($this->once())
      ->method('hasField')
      ->with('items')
      ->willReturn(TRUE);
    $this->queueEntityMock
      ->expects($this->exactly(2))
      ->method('get')
      ->with('items')
      ->willReturn($fieldList);
    $fieldList
      ->expects($this->once())
      ->method('isEmpty')
      ->willReturn(FALSE);
    $fieldList
      ->expects($this->once())
      ->method('getValue')
      ->willReturn(
        [
          'test_weight' => [
            'target_id' => 12345,
          ],
        ]
      );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->fieldsHelperMock = $this->createMock(FieldsHelperInterface::class);
    $this->dataSourceMock = $this->createMock(DatasourceInterface::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->queueEntityMock = $this->createMock(EntitySubqueueInterface::class);
  }

}
