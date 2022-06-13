<?php

namespace Drupal\Tests\mars_recipes\Unit\Plugin\Block;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Entity\File;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_media\SVG\SVG;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_recipes\Plugin\Block\RecipeFeatureBlock;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;

/**
 * Class RecipeFeatureBlockTest - unit tests.
 *
 * @package Drupal\Tests\mars_recipes\Unit
 * @covers \Drupal\mars_recipes\Plugin\Block\RecipeFeatureBlock
 */
class RecipeFeatureBlockTest extends UnitTestCase {
  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration;

  /**
   * Tested RecipeFeatureBlock.
   *
   * @var \Drupal\mars_recipes\Plugin\Block\RecipeFeatureBlock
   */
  private $recipeFeatureBlock;

  /**
   * Entity type manager mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManagerMock;

  /**
   * ThemeConfiguratorParserMock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParserMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\LanguageHelper
   */
  private $languageHelperMock;

  /**
   * Media Helper service Mock.
   *
   * @var \Drupal\mars_media\MediaHelper|\PHPUnit\Framework\MockObject\MockObject
   */
  private $mediaHelperMock;

  /**
   * Mocked url that will be returned on toUrl call on entity.
   *
   * @var \Drupal\Core\Url|\PHPUnit\Framework\MockObject\MockObject
   */
  private $entityCollectionUrl;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\FieldItemListInterface
   */
  private $fieldItemListMock;

  /**
   * Config factory mock.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactoryMock;

  /**
   * Immutable config mock.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $immutableConfigMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->configuration = [
      'cta' => [
        'title' => 'Recipe title',
      ],
      'block_title' => 'Recipe title',
      'eyebrow' => FALSE,
      'recipe_media_image' => 11,
      'recipe_options' => 'image',
      'override_text_color' => [
        'override_color' => FALSE,
      ],
    ];
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    $this->languageHelperMock
      ->expects($this->any())
      ->method('getTranslation')
      ->willReturn($this->createNodeMock());

    $this->entityCollectionUrl
      ->expects($this->any())
      ->method('toString')
      ->willReturn('https://test.com');

    $this->fieldItemListMock
      ->expects($this->any())
      ->method('isEmpty')
      ->willReturn(FALSE);

    $configMock = $this->getMockBuilder(stdClass::class)
      ->setMethods(['get'])
      ->getMock();

    $this->configFactoryMock
      ->method('get')
      ->with('mars_common.character_limit_page')
      ->willReturn($configMock);

    // We should create it in test to import different configs.
    $this->recipeFeatureBlock = new RecipeFeatureBlock(
    $this->configuration,
    'recipe_feature_block',
    $definitions,
    $this->entityTypeManagerMock,
    $this->themeConfiguratorParserMock,
    $this->languageHelperMock,
    $this->mediaHelperMock,
    $this->configFactoryMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->entityCollectionUrl = $this->createMock(Url::class);
    $this->fieldItemListMock = $this->createMock(FieldItemListInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->immutableConfigMock = $this->createMock(ImmutableConfig::class);
  }

  /**
   * Test Block creation.
   *
   * @test
   */
  public function blockShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(5))
      ->method('get')
      ->withConsecutive(
          [$this->equalTo('entity_type.manager')],
          [$this->equalTo('mars_common.theme_configurator_parser')],
          [$this->equalTo('mars_common.language_helper')],
          [$this->equalTo('mars_media.media_helper')],
          [$this->equalTo('config.factory')]
        )
      ->will($this->onConsecutiveCalls($this->entityTypeManagerMock, $this->themeConfiguratorParserMock, $this->languageHelperMock, $this->mediaHelperMock, $this->configFactoryMock));

    $definitions = [
      'provider' => 'test',
      'admin_label' => 'test',
    ];
    $this->recipeFeatureBlock::create($this->containerMock, [], 'recipe_feature_block', $definitions);
  }

  /**
   * Test building block.
   *
   * @test
   */
  public function buildBlockRenderArrayProperly() {
    $this->assertEquals('build', 'build', 'actual value is not equals to expected');

    // Set Config Parser Mock.
    $this->themeConfiguratorParserMock
      ->expects($this->exactly(1))
      ->method('getBrandBorder')
      ->willReturn(new SVG('<svg xmlns="http://www.w3.org/2000/svg" />', 'id'));

    // Mock node context.
    $nodeMock = $this->createNodeMock();
    $nodeContext = $this->getMockBuilder(Context::class)
      ->disableOriginalConstructor()
      ->getMock();
    $nodeContext->expects($this->exactly(1))
      ->method('getContextValue')
      ->willReturn($nodeMock);
    $this->recipeFeatureBlock->setContext('node', $nodeContext);

    // Main testing function.
    $build = $this->recipeFeatureBlock->build();

    $this->assertEquals('recipe_feature_block', $build['#theme']);
    $this->assertArrayHasKey('#title', $build);
    $this->assertEquals('Recipe title', $build['#title']);
    $this->assertArrayHasKey('#block_title', $build);
    $this->assertArrayHasKey('#cooking_time', $build);
    $this->assertArrayHasKey('#recipe_media', $build);
    $this->assertArrayHasKey('#eyebrow', $build);
    $this->assertArrayHasKey('#cta', $build);
    $this->assertArrayHasKey('#text_color_override', $build);
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
      ->willReturn('Recipe title');

    // Mock $node->bundle().
    $node->expects($this->any())
      ->method('bundle')
      ->willReturn('recipe');

    $node->expects($this->any())
      ->method('toUrl')
      ->willReturn($this->entityCollectionUrl);

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
      ->willReturnMap(
              [
              ['entity', $this->createMediaMock()],
              ['target_id', '1'],
              ]
          );

    // Attach field values to calls.
    $node->expects($this->any())
      ->method('__get')
      ->willReturnMap(
              [
              ['field_recipe_description', $fieldStringMock],
              ['field_recipe_cooking_time', $fieldStringMock],
              ['field_recipe_ingredients_number', $fieldStringMock],
              ['field_recipe_number_of_servings', $fieldStringMock],
              ['field_recipe_image', $fieldEntityMock],
              ]
          );

    // Disable render of the video field.
    $node->expects($this->any())
      ->method('hasField')
      ->willReturnMap(
              [
              ['field_recipe_video', FALSE],
              ['field_recipe_image', FALSE],
              ]
          );

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

}
