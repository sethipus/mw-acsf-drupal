<?php

namespace Drupal\Tests\mars_recipes\Unit;

use Drupal\mars_recipes\Plugin\Block\RecipeDetailBody;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Class RecipeDetailBodyTest.
 *
 * @package Drupal\Tests\mars_recipes\Unit
 * @covers \Drupal\mars_recipes\Plugin\Block\RecipeDetailBody
 */
class RecipeDetailBodyTest extends UnitTestCase {

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $viewBuilderMock;

  /**
   * Entity type manager mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManagerMock;

  /**
   * Tested recipe hero block.
   *
   * @var \Drupal\mars_recipes\Plugin\Block\RecipeDetailHero
   */
  private $recipeBodyBlock;

  /**
   * File storage.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject||\Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorageMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getViewBuilder')
      ->withConsecutive([$this->equalTo('node')])
      ->will($this->onConsecutiveCalls($this->viewBuilderMock));

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->withConsecutive(
        [$this->equalTo('file')]
      )
      ->will($this->onConsecutiveCalls($this->fileStorageMock));

    $definitions = [
      'provider' => 'test',
      'admin_label' => 'test',
    ];

    $this->recipeBodyBlock = new RecipeDetailBody(
      [],
      'recipe_detail_body',
      $definitions,
      $this->entityTypeManagerMock
    );
  }

  /**
   * Test Block creation.
   *
   * @test
   */
  public function blockShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(1))
      ->method('get')
      ->withConsecutive(
        [$this->equalTo('entity_type.manager')],
        )
      ->will($this->onConsecutiveCalls($this->entityTypeManagerMock));

    $this->entityTypeManagerMock
      ->expects($this->exactly(1))
      ->method('getViewBuilder')
      ->withConsecutive(
        [$this->equalTo('node')]
      )
      ->will($this->onConsecutiveCalls($this->viewBuilderMock));

    $definitions = [
      'provider' => 'test',
      'admin_label' => 'test',
    ];
    $this->recipeBodyBlock::create($this->containerMock, [], 'recipe_detail_body', $definitions);
  }

  /**
   * Test building block.
   *
   * @test
   */
  public function buildBlockRenderArrayProperly() {
    $this->assertEquals('build', 'build', 'actual value is not equals to expected');

    // Mock node context.
    $nodeMock = $this->createNodeMock();
    $nodeContext = $this->getMockBuilder(Context::class)
      ->disableOriginalConstructor()
      ->getMock();
    $nodeContext->expects($this->exactly(1))
      ->method('getContextValue')
      ->willReturn($nodeMock);
    $this->recipeBodyBlock->setContext('node', $nodeContext);

    // Main testing function.
    $build = $this->recipeBodyBlock->build();

    // TODO Should we add test for related products?
    $this->assertEquals('recipe_detail_body_block', $build['#theme']);
    $this->assertArrayHasKey('#ingredients_list', $build);
    $this->assertArrayHasKey('#nutrition_module', $build);
    $this->assertArrayHasKey('#product_used_items', $build);
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->viewBuilderMock = $this->createMock(EntityViewBuilderInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->fileStorageMock = $this->createMock(EntityStorageInterface::class);
  }

  /**
   * Mock recipe node.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   Mock node object.
   */
  private function createNodeMock() {
    $node = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();

    // Mock string fields.
    $fieldStringMock = $this->getMockBuilder(FieldItemListInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $fieldStringMock->expects($this->any())
      ->method('__get')
      ->with('value')
      ->willReturn(['string']);

    // Attach field values to calls.
    $node->expects($this->any())
      ->method('__get')
      ->willReturnMap([
        ['field_recipe_nutrition_module', $fieldStringMock],
      ]);

    // Mock ingredients and product fields as empty.
    $fieldArrayMock = $this->getMockBuilder(FieldItemListInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $fieldArrayMock->expects($this->any())
      ->method('isEmpty')
      ->willReturn(TRUE);

    $node->expects($this->any())
      ->method('get')
      ->with(
        $this->logicalOr(
          'field_recipe_ingredients',
          'field_product_reference'
        )
      )
      ->willReturn($fieldArrayMock);

    return $node;
  }

}
