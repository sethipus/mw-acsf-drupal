<?php

namespace Drupal\Tests\mars_seo\Unit\Plugin\JsonLdStrategy;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_seo\Plugin\JsonLdStrategy\Recipe;
use Drupal\metatag\MetatagManager;
use Drupal\Tests\UnitTestCase;
use Spatie\SchemaOrg\Recipe as RecipeSchema;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_seo\Plugin\JsonLdStrategy\Recipe
 * @group mars
 * @group mars_seo
 */
class RecipeTest extends UnitTestCase {

  use JsonLdTestsTrait;

  /**
   * System under test.
   *
   * @var \Drupal\mars_seo\Plugin\JsonLdStrategy\Recipe
   */
  private $jsonLdPlugin;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Supported node types.
   *
   * @var string[]
   */
  protected $supportedBundles;

  /**
   * Url generator service.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGeneratorMock;

  /**
   * Config factory service.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_media\MediaHelper
   */
  private $mediaHelperMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Layout\LayoutDefinition
   */
  protected $layoutDefinitionMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\EntityReferenceFieldItemListInterface
   */
  private $fieldItemListMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\metatag\MetatagManager
   */
  private $metatagManagerMock;

  /**
   * The plugin ID.
   */
  const PLUGIN_ID = 'recipe';

  /**
   * The plugin definitions.
   */
  const DEFINITIONS = [
    'provider' => 'test',
    'admin_label' => 'Recipe',
    'label' => 'Recipe Page',
    'auto_select' => FALSE,
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $configuration = [];
    $this->supportedBundles = ['recipe'];

    $this->jsonLdPlugin = new Recipe(
      $configuration,
      static::PLUGIN_ID,
      static::DEFINITIONS,
      $this->mediaHelperMock,
      $this->urlGeneratorMock,
      $this->configFactoryMock,
      $this->metatagManagerMock
    );
  }

  /**
   * Test.
   *
   * @test
   *
   * @covers \Drupal\mars_seo\Plugin\JsonLdStrategy\Recipe::isApplicable
   * @covers \Drupal\mars_seo\Plugin\JsonLdStrategy\Recipe::getContextValue
   * @covers \Drupal\mars_seo\Plugin\JsonLdStrategy\Recipe::supportedBundles
   */
  public function testIsApplicable() {
    // Test system with empty build & node contexts.
    $this->jsonLdPlugin->setContext('node', $this->createNodeContextMock(['bundle' => '']));
    $this->jsonLdPlugin->setContext('build', $this->createBuildContext());
    $this->assertFalse($this->jsonLdPlugin->isApplicable());
    // Test system with correct node context.
    $this->jsonLdPlugin->setContext('node', $this->createNodeContextMock(['bundle' => 'recipe']));
    $this->assertTrue($this->jsonLdPlugin->isApplicable());
  }

  /**
   * Test.
   *
   * @test
   *
   * @covers \Drupal\mars_seo\Plugin\JsonLdStrategy\Recipe::getStructuredData
   * @covers \Drupal\mars_seo\Plugin\JsonLdStrategy\Recipe::getContextValue
   */
  public function testGetStructuredData() {
    // Prepare all necessary contexts.
    $this->jsonLdPlugin->setContext('build', $this->createBuildContext());

    $this->fieldItemListMock->expects($this->any())->method('__get')->willReturnMap([
      ['value', '5'],
      ['target_id', 1],
    ]);

    $node_context_params = [
      'getTitle' => 'test title',
      'id' => 1,
      'getEntityTypeId' => 'node',
      'bundle' => 'recipe',
      'multiple_get_with' => [
        [
          '_with' => 'field_recipe_image',
          'field_recipe_image' => $this->fieldItemListMock,
        ],
        [
          '_with' => 'field_recipe_cooking_time',
          'field_recipe_cooking_time' => $this->fieldItemListMock,
        ],
        [
          '_with' => 'field_recipe_ingredients',
          'field_recipe_ingredients' => $this->fieldItemListMock,
        ],
        [
          '_with' => 'field_recipe_number_of_servings',
          'field_recipe_number_of_servings' => $this->fieldItemListMock,
        ],
        [
          '_with' => 'field_recipe_description',
          'field_recipe_description' => $this->fieldItemListMock,
        ],
      ],
    ];
    $this->jsonLdPlugin->setContext('node', $this->createNodeContextMock($node_context_params));
    $this->mediaHelperMock->expects($this->any())->method('getMediaUrl')->willReturn('test_image.jpeg');
    $this->metatagManagerMock->expects($this->once())->method('tagsFromEntityWithDefaults')->willReturn([]);
    $metadata_array = [
      'title' => [
        '#attributes' => [
          'content' => $node_context_params['getTitle'],
        ],
      ],
      'description' => [
        '#attributes' => [
          'content' => 'Test description.',
        ],
      ],
      'keywords' => [
        '#attributes' => [
          'content' => 'test, keyword, values',
        ],
      ],
    ];
    $this->metatagManagerMock->expects($this->once())->method('generateRawElements')->willReturn($metadata_array);

    // Test system with prepared data.
    $schema = $this->jsonLdPlugin->getStructuredData();
    $this->assertTrue($schema instanceof RecipeSchema);
    $this->assertEquals($node_context_params['getTitle'], $schema->getProperties()['name']);
    $this->assertEquals('PT5M', $schema->getProperties()['totalTime']);
    $this->assertEquals('5', $schema->getProperties()['recipeYield']);
    $this->assertEquals('5', $schema->getProperties()['description']);
    $this->assertEquals($metadata_array['keywords']['#attributes']['content'], $schema->getProperties()['keywords']);
    $this->assertEquals('test_image.jpeg', current($schema->getProperties()['image']));
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->urlGeneratorMock = $this->createMock(UrlGeneratorInterface::class);
    $this->fieldItemListMock = $this->createMock(EntityReferenceFieldItemListInterface::class);
    $this->metatagManagerMock = $this->createMock(MetatagManager::class);
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(4))
      ->method('get')
      ->willReturnMap(
        [
          [
            'mars_media.media_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->mediaHelperMock,
          ],
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configFactoryMock,
          ],
          [
            'url_generator',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->urlGeneratorMock,
          ],
          [
            'metatag.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->metatagManagerMock,
          ],
        ]
      );

    $this->jsonLdPlugin::create(
      $this->containerMock,
      [],
      static::PLUGIN_ID,
      static::DEFINITIONS,
    );
  }

}
