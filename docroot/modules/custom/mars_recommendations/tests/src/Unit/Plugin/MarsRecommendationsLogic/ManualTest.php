<?php

namespace Drupal\Tests\mars_recommendations\Unit\Plugin\MarsRecommendationsLogic;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\mars_recommendations\Plugin\MarsRecommendationsLogic\Manual;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ManualTest.
 *
 * @package Drupal\Tests\mars_recommendations\Unit
 * @covers \Drupal\mars_recommendations\Plugin\MarsRecommendationsLogic\Manual
 */
class ManualTest extends UnitTestCase {

  private const TEST_CONFIG = [
    'nodes' => [1],
  ];

  private const TEST_ITEMS_COUNT = 1;

  private const TEST_FORM = [
    'settings' => [
      'population' => [
        'configuration' => [
          'subform' => [
            'nodes' => [],
          ],
        ],
      ],
    ],
  ];

  /**
   * System under test.
   *
   * @var \Drupal\mars_recommendations\Plugin\MarsRecommendationsLogic\Manual
   */
  private $manual;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $entityTypeManagerMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Form\FormStateInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $formStateMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private $eventDispatcherMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $nodeStorageMock;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->manual = new Manual(
      self::TEST_CONFIG,
      'manual',
      [
        'id' => 'manual',
        'provider' => 'mars_recommendations',
        'admin_label' => 'test',
      ],
      $this->entityTypeManagerMock,
      $this->eventDispatcherMock
    );

    $this->manual->setStringTranslation($this->getStringTranslationStub());
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
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'event_dispatcher',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->eventDispatcherMock,
          ],
        ]
      );
    $this->manual::create(
      $this->containerMock,
      self::TEST_CONFIG,
      'manual',
      [
        'provider'    => 'test',
        'admin_label' => 'test',
      ]
    );
  }

  /**
   * Test.
   */
  public function testGetResultsLimit() {
    $limit = $this->manual->getResultsLimit();
    $this->assertEquals(Manual::DEFAULT_RESULTS_LIMIT, $limit);
  }

  /**
   * Test.
   */
  public function testGetRecommendations() {
    $recommendations = $this->manual->getRecommendations();
    $this->assertIsArray($recommendations);
    $this->assertEquals($this->createMock(NodeInterface::class), $recommendations[0]);
  }

  /**
   * Test.
   */
  public function testBuildConfigurationFormProperly() {
    $form = [];
    $entityMock = $this->createMock(EntityInterface::class);
    $this->formStateMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap([
          ['items_count', self::TEST_ITEMS_COUNT],
          ['layout_id', 1],
          ['entity', $entityMock],
      ]);

    $this->eventDispatcherMock
      ->expects($this->once())
      ->method('dispatch');

    $conf = $this->manual->buildConfigurationForm($form, $this->formStateMock);
    $this->assertArrayHasKey('nodes', $conf);
  }

  /**
   * Test.
   */
  public function testSubmitConfigurationFormProperly() {
    $form = [];
    $this->formStateMock
      ->expects($this->never())
      ->method($this->anything());

    $this->manual->submitConfigurationForm($form, $this->formStateMock);
  }

  /**
   * Test.
   */
  public function testValidateConfigurationFormProperly() {
    $form = [];
    $this->formStateMock
      ->expects($this->once())
      ->method('getValues')
      ->willReturn([
        'nodes' => [
          'add_more' => [],
          'test' => [
            'node' => '1',
          ],
        ],
      ]);

    $this->manual->validateConfigurationForm($form, $this->formStateMock);
  }

  /**
   * Test.
   */
  public function testValidateAddItemProperly() {
    $form = [];
    $this->formStateMock
      ->expects($this->once())
      ->method('get')
      ->with('items_count')
      ->willReturn(self::TEST_ITEMS_COUNT);

    $this->formStateMock
      ->expects($this->once())
      ->method('set')
      ->willReturn(1);

    $this->formStateMock
      ->expects($this->once())
      ->method('setRebuild')
      ->willReturn(1);

    $this->manual->validateAddItem($form, $this->formStateMock);
  }

  /**
   * Test.
   */
  public function testValidateRemoveItemProperly() {
    $form = [];
    $this->formStateMock
      ->expects($this->once())
      ->method('get')
      ->with('items_count')
      ->willReturn(self::TEST_ITEMS_COUNT);

    $this->formStateMock
      ->expects($this->once())
      ->method('getTriggeringElement')
      ->willReturn([
        '#parents' => [1, 1, 1],
      ]);

    $this->formStateMock
      ->expects($this->once())
      ->method('getUserInput')
      ->willReturn(self::TEST_FORM);

    $this->formStateMock
      ->expects($this->once())
      ->method('getValues')
      ->willReturn(self::TEST_FORM);

    $this->formStateMock
      ->expects($this->once())
      ->method('setValues');

    $this->formStateMock
      ->expects($this->once())
      ->method('set')
      ->with('items_count', self::TEST_ITEMS_COUNT - 1);

    $this->formStateMock
      ->expects($this->once())
      ->method('setRebuild')
      ->willReturn(1);

    $this->manual->validateRemoveItem($form, $this->formStateMock);
  }

  /**
   * Test.
   */
  public function testAjaxCallbackProperly() {
    $form = self::TEST_FORM;
    $nodes = $this->manual->ajaxCallback($form, $this->formStateMock);
    $this->assertEquals([], $nodes);
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks() {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $nodeStorageMock = $this->createMock(EntityStorageInterface::class);
    $nodeStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn($this->createMock(NodeInterface::class));
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->entityTypeManagerMock
      ->method('getStorage')
      ->willReturn($nodeStorageMock);
    $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
  }

}
