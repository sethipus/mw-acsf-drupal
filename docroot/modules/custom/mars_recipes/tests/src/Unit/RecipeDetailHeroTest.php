<?php

namespace Drupal\Tests\mars_recipes\Unit;

use Drupal\mars_recipes\Plugin\Block\RecipeDetailHero;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class RecipeDetailHeroTest.
 *
 * @package Drupal\Tests\mars_recipes\Unit
 * @covers \Drupal\mars_recipes\Plugin\Block\RecipeDetailHero
 */
class RecipeDetailHeroTest extends UnitTestCase {

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
   * Config factory mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject||\Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactoryMock;

  /**
   * Tested recipe hero block.
   *
   * @var \Drupal\mars_recipes\Plugin\Block\RecipeDetailHero
   */
  private $recipeHeroBlock;

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

    $definitions = [
      'provider' => 'test',
      'admin_label' => 'test',
    ];

    $this->recipeHeroBlock = new RecipeDetailHero(
      [],
      'recipe_detail_hero',
      $definitions,
      $this->entityTypeManagerMock,
      $this->configFactoryMock
    );
  }

  /**
   * Test Block creation.
   *
   * @test
   */
  public function blockShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(2))
      ->method('get')
      ->withConsecutive(
        [$this->equalTo('entity_type.manager')],
        [$this->equalTo('config.factory')],
        )
      ->will($this->onConsecutiveCalls($this->entityTypeManagerMock, $this->configFactoryMock));

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
    $this->recipeHeroBlock::create($this->containerMock, [], 'recipe_detail_hero', $definitions);
  }

  /**
   * Test building block.
   *
   * @test
   */
  public function buildBlockRenderArrayProperly() {
    // TODO: Mock getContextValue() function.
    // $build = $this->recipeHeroBlock->build();
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->viewBuilderMock = $this->createMock(EntityViewBuilderInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
  }

  /* @code{
   * private function createNodeMock() {
   * $node = $this->getMockBuilder(Node::class)
   * ->disableOriginalConstructor()
   * ->getMock();
   * $node->expects($this->any())
   * ->method('label')
   * ->willReturn('Recipe label');
   *
   * $fieldStringMock = $this->getMockBuilder(FieldItemListInterface::class)
   * ->disableOriginalConstructor()
   * ->getMock();
   * $fieldStringMock->expects($this->any())
   * ->method('__get')
   * ->with('value')
   * ->willReturn('string');
   *
   * $node->expects($this->any())
   * ->method('__get')
   * ->with('field_recipe_description')
   * ->willReturn($fieldStringMock);
   *
   * $node->expects($this->exactly(6))
   * ->method('__get')
   * ->withConsecutive(
   * [$this->equalTo('field_recipe_description')],
   * [$this->equalTo('field_recipe_cooking_time')],
   * [$this->equalTo('field_recipe_ingredients_number')],
   * [$this->equalTo('field_recipe_number_of_servings')],
   * [$this->equalTo('field_recipe_image')],
   * [$this->equalTo('field_recipe_video')]
   * )
   * ->will($this->onConsecutiveCalls(
   * $fieldStringMock,
   * $fieldStringMock,
   * $fieldStringMock,
   * $fieldStringMock,
   *
   * ));
   * }*/

}
