<?php

namespace Drupal\Tests\mars_recipes\Unit;

use Drupal\mars_recipes\Plugin\Block\RecipeDetailHero;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Entity\EntityStorageInterface;

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
   * Test theme settings.
   *
   * @var array
   */
  private $themeSettings;

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

    $this->recipeHeroBlock = new RecipeDetailHero(
      ['social_links_toggle' => FALSE],
      'recipe_detail_hero',
      $definitions,
      $this->entityTypeManagerMock,
      $this->configFactoryMock
    );

    $this->themeSettings = [
      'logo' => [
        'path' => '',
      ],
      'brand_borders' => ['1'],
      'social' => [
        [
          'name' => 'name1',
          'link' => 'link.com',
          'icon' => [0],
        ],
        [
          'name' => 'name2',
          'link' => 'link.net',
        ],
      ],
    ];
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
    $this->assertEquals('build', 'build', 'actual value is not equals to expected');

    // Set theme configuration.
    $this->setConfigFactoryMock();
    // Set file mock.
    $this->setFileMock();

    // Mock node context.
    $nodeMock = $this->createNodeMock();
    $nodeContext = $this->getMockBuilder(Context::class)
      ->disableOriginalConstructor()
      ->getMock();
    $nodeContext->expects($this->exactly(1))
      ->method('getContextValue')
      ->willReturn($nodeMock);
    $this->recipeHeroBlock->setContext('node', $nodeContext);

    // Main testing function.
    $build = $this->recipeHeroBlock->build();

    $this->assertEquals('recipe_detail_hero_block', $build['#theme']);
    $this->assertArrayHasKey('#label', $build);
    $this->assertEquals('Recipe label', $build['#label']);
    $this->assertArrayHasKey('#description', $build);
    $this->assertArrayHasKey('#cooking_time', $build);
    $this->assertArrayHasKey('#ingredients_number', $build);
    $this->assertArrayHasKey('#number_of_servings', $build);
    $this->assertArrayHasKey('#image', $build);
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

    // Mock $node->label().
    $node->expects($this->any())
      ->method('label')
      ->willReturn('Recipe label');

    // Mock string fields. Covers:
    // * $node->field_recipe_description.
    // * $node->field_recipe_cooking_time.
    // * $node->field_recipe_ingredients_number.
    // * $node->field_recipe_number_of_servings.
    $fieldStringMock = $this->getMockBuilder(FieldItemListInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $fieldStringMock->expects($this->any())
      ->method('__get')
      ->with('value')
      ->willReturn('string');

    // Mock $node->field_recipe_image.
    $fieldEntityMock = $this->getMockBuilder(FieldItemListInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $fieldEntityMock->expects($this->any())
      ->method('__get')
      ->with('entity')
      ->willReturn($this->createMediaMock());

    // Attach field values to calls.
    $node->expects($this->any())
      ->method('__get')
      ->willReturnMap([
        ['field_recipe_description', $fieldStringMock],
        ['field_recipe_cooking_time', $fieldStringMock],
        ['field_recipe_ingredients_number', $fieldStringMock],
        ['field_recipe_number_of_servings', $fieldStringMock],
        ['field_recipe_image', $fieldEntityMock],
      ]);

    // Disable render of the video field.
    $node->expects($this->any())
      ->method('hasField')
      ->with('field_recipe_video')
      ->willReturn(FALSE);

    // Mock getting suffix.
    $fieldArrayMock = $this->getMockBuilder(FieldItemListInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $fieldArrayMock->expects($this->any())
      ->method('getSettings')
      ->willReturn(['suffix' => 'suffix']);
    $node->expects($this->any())
      ->method('get')
      ->with(
        $this->logicalOr(
          'field_recipe_cooking_time',
          'field_recipe_ingredients_number',
          'field_recipe_number_of_servings'
        )
      )
      ->willReturn($fieldArrayMock);

    return $node;
  }

  /**
   * Mock media.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   Mock media object.
   */
  private function createMediaMock() {
    $fileMock = $this->getMockBuilder(File::class)
      ->disableOriginalConstructor()
      ->getMock();

    $fileMock->expects($this->any())
      ->method('createFileUrl')
      ->willReturn('/sites/default/files/drupal.png');

    $mediaMock = $this->getMockBuilder(Media::class)
      ->disableOriginalConstructor()
      ->getMock();

    $mediaMock->expects($this->any())
      ->method('label')
      ->willReturn('Media label');

    $fieldEntityMock = $this->getMockBuilder(FieldItemListInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $fieldEntityMock->expects($this->any())
      ->method('__get')
      ->with('entity')
      ->willReturn($fileMock);

    $mediaMock->expects($this->any())
      ->method('__get')
      ->with('image')
      ->willReturn($fieldEntityMock);

    return $mediaMock;
  }

  /**
   * Mock configuration factory.
   */
  protected function setConfigFactoryMock(): void {
    $configGetMock = $this->getMockBuilder(stdClass::class)
      ->setMethods(['get'])
      ->getMock();

    $this->configFactoryMock
      ->expects($this->exactly(1))
      ->method('get')
      ->with($this->equalTo('emulsifymars.settings'))
      ->willReturn($configGetMock);

    $configGetMock
      ->expects($this->exactly(1))
      ->method('get')
      ->willReturn($this->themeSettings);
  }

  /**
   * Mock file storage.
   */
  protected function setFileMock(): void {
    $fileMock = $this->getMockBuilder(stdClass::class)
      ->setMethods(['createFileUrl'])
      ->getMock();

    $fileMock
      ->expects($this->any())
      ->method('createFileUrl')
      ->willReturn('http://mars.com');

    $this->fileStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn($fileMock);
  }

}
