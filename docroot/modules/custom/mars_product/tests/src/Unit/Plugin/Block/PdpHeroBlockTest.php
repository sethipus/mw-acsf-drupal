<?php

namespace Drupal\Tests\mars_product\Unit\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_media\SVG\SVG;
use Drupal\mars_product\NutritionDataHelper;
use Drupal\mars_product\Plugin\Block\PdpHeroBlock;
use Drupal\mars_product\ProductHelper;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Path\CurrentPathStack;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\mars_product\Plugin\Block\PdpHeroBlock
 * @group mars
 * @group mars_product
 */
class PdpHeroBlockTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_product\Plugin\Block\PdpHeroBlock
   */
  private $block;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Form\FormStateInterface
   */
  private $formStateMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_media\MediaHelper
   */
  private $mediaHelperMock;

  /**
   * Mock.
   *
   * @var \Drupal\mars_product\ProductHelper|\PHPUnit\Framework\MockObject\MockObject
   */
  private $productHelperMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManagerMock;

  /**
   * Node storage.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject||\Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorageMock;

  /**
   * Route Match.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject||\Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatchMock;

  /**
   * Current Path.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject||\Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPathMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $entityRepositoryMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $entityFormBuilderMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\LanguageHelper
   */
  private $languageHelperMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $configFactoryMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|\PHPUnit\Framework\MockObject\MockObject
   */
  private $immutableConfigMock;

  /**
   * Mock.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser|\PHPUnit\Framework\MockObject\MockObject
   */
  private $themeConfiguratorParserMock;

  /**
   * Mock.
   *
   * @var \Drupal\mars_product\NutritionDataHelper|\PHPUnit\Framework\MockObject\MockObject
   */
  private $nutritionHelperMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $translationMock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();

    $this->configFactoryMock
    ->method('getEditable')
    ->with('mars_common.character_limit_page')
    ->willReturn($this->immutableConfigMock);

    \Drupal::setContainer($this->containerMock);
    $this->configuration = [
      'provider' => 'mars_product',
      'eyebrow' => 'test',
      'available_sizes' => 'Available sizes',
      'wtb' => [
        'commerce_vendor' => 'price_spider',
        'data_widget_id' => 'Widget id',
        'data_token' => 'Token',
        'data_subid' => 'SubId',
        'product_id' => 'Product ID',
        'cta_title' => 'CTA title',
        'button_type' => 'my_own',
        'data_locale' => 'Data locale',
      ],
      'nutrition' => [
        'label' => 'Nutrition section label',
        'serving_label' => 'Serving label',
        'daily_label' => 'Daily label',
        'vitamins_label' => 'Vitamins label',
      ],
      'allergen_label' => 'Allergen label',
      'more_information' => [
        'more_information_label' => 'More information label',
        'show_more_information_label' => 'Show more information label',
      ],
      'use_background_color' => 1,
      'background_color' => 1,
    ];
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    $this->block = new PdpHeroBlock(
      $this->configuration,
      'pdp_hero_block',
      $definitions,
      $this->entityTypeManagerMock,
      $this->entityRepositoryMock,
      $this->entityFormBuilderMock,
      $this->themeConfiguratorParserMock,
      $this->languageHelperMock,
      $this->productHelperMock,
      $this->mediaHelperMock,
      $this->immutableConfigMock,
      FALSE,
      $this->configFactoryMock,
      $this->nutritionHelperMock,
      $this->routeMatchMock,
      $this->currentPathMock
    );
  }

  /**
   * Test configuration form.
   */
  public function testBuildConfigurationFormProperly() {
    $config_form = $this->block->buildConfigurationForm([], $this->formStateMock);
    $this->assertCount(18, $config_form);
    $this->assertArrayHasKey('eyebrow', $config_form);
    $this->assertArrayHasKey('available_sizes', $config_form);
    $this->assertArrayHasKey('wtb', $config_form);
    $this->assertArrayHasKey('nutrition', $config_form);
    $this->assertArrayHasKey('labels', $config_form);
    $this->assertArrayHasKey('more_information', $config_form);
    $this->assertArrayHasKey('use_background_color', $config_form);
    $this->assertArrayHasKey('background_color', $config_form);
  }

  /**
   * Test submitting block.
   */
  public function testShouldBlockSubmit() {
    $form_data = [];

    $this->formStateMock
      ->expects($this->once())
      ->method('getValues')
      ->willReturn([]);

    $this->block->blockSubmit(
      $form_data,
      $this->formStateMock
    );
  }

  /**
   * Test building block (product bundle type).
   */
  public function testValidBlockBuildProduct() {
    $product_node = $this->createProductMock('product');
    $product_variant = $this->createProductMock('product_variant');

    $nodeContext = $this->createMock(Context::class);
    $nodeContext
      ->method('getContextValue')
      ->willReturn($product_node);

    $this->routeMatchMock
      ->expects($this->any())
      ->method('getParameter')
      ->willReturn($product_node);
 
    $this->currentPathMock
      ->expects($this->any())
      ->method('getPath')
      ->willReturn('test/node.123/url');

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->immutableConfigMock);

    $this->themeConfiguratorParserMock
      ->method('getBrandBorder')
      ->willReturn(new SVG('<svg xmlns="http://www.w3.org/2000/svg" />', 'id'));
    $this->themeConfiguratorParserMock
      ->method('getSettingValue')
      ->willReturn(1);

    $this->immutableConfigMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('0');

    $this->productHelperMock
      ->expects($this->any())
      ->method('mainVariant')
      ->willReturn($product_variant);

    $this->languageHelperMock
      ->method('getTranslation')
      ->will(
        $this->returnCallback(
          function ($arg) {
            return $arg;
          })
      );

    $this->mediaHelperMock
      ->method('getMediaParametersById')
      ->willReturn(
        [
          'src' => 'test_image_source',
          'alt' => 'test_image_alt',
        ]
      );

    $this->nutritionHelperMock
      ->method('getMapping')
      ->willReturn([
        PdpHeroBlock::NUTRITION_SUBGROUP_1 => [],
      ]);

    $this->block->setContext('node', $nodeContext);

    $build = $this->block->build();

    $this->assertCount(5, $build);
    $this->assertArrayHasKey('#pdp_common_data', $build);
    $this->assertArrayHasKey('#pdp_size_data', $build);
    $this->assertArrayHasKey('#pdp_data', $build);
    $this->assertArrayHasKey('nutrition_data', $build['#pdp_data'][1]);
    $this->assertEquals('product', $build['#pdp_bundle_type']);
    $this->assertEquals('pdp_hero_block', $build['#theme']);
  }

  /**
   * Test building block (product_multipack bundle type).
   */
  public function testValidBlockBuildProductMultipack() {
    $product_node = $this->createProductMock('product_multipack');

    // Mock $node->bundle().
    $product_node->expects($this->any())
      ->method('bundle')
      ->willReturn('product');

    $nodeContext = $this->createMock(Context::class);

    $nodeContext
      ->method('getContextValue')
      ->willReturn($product_node);
    $this->block->setContext('node', $nodeContext);

    $this->routeMatchMock
      ->expects($this->any())
      ->method('getParameter')
      ->willReturn($product_node);
    
    $this->currentPathMock
      ->expects($this->any())
      ->method('getPath')
      ->willReturn('test/node.123/url');

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->immutableConfigMock);

    $this->immutableConfigMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('0');

    $this->languageHelperMock
      ->method('getTranslation')
      ->will(
        $this->returnCallback(
          function ($arg) {
            return $arg;
          })
      );

    $this->mediaHelperMock
      ->method('getMediaParametersById')
      ->willReturn(
        [
          'src' => 'test_image_source',
          'alt' => 'test_image_alt',
        ]
      );

    $this->entityFormBuilderMock
      ->method('getForm')
      ->willReturn([
        '#fieldgroups' => [],
      ]);

    $field = $this->createFieldMock();
    $product_node
      ->expects($this->any())
      ->method('get')
      ->willReturn($field);

    $build = $this->block->build();

    $this->assertCount(5, $build);
    $this->assertArrayHasKey('#pdp_common_data', $build);
    $this->assertArrayHasKey('#pdp_size_data', $build);
    $this->assertArrayHasKey('#pdp_data', $build);
    $this->assertArrayHasKey('products', $build['#pdp_data'][1]);
    $this->assertEquals('product_multipack', $build['#pdp_bundle_type']);
    $this->assertEquals('pdp_hero_block', $build['#theme']);
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->entityRepositoryMock = $this->createMock(EntityRepositoryInterface::class);
    $this->entityFormBuilderMock = $this->createMock(EntityFormBuilderInterface::class);
    $this->immutableConfigMock = $this->createMock(ImmutableConfig::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->productHelperMock = $this->createMock(ProductHelper::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->nutritionHelperMock = $this->createMock(NutritionDataHelper::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->routeMatchMock = $this->createMock(RouteMatchInterface::class);
    $this->currentPathMock = $this->createMock(CurrentPathStack::class);

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'string_translation',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->translationMock,
          ],
        ]
      );

    $this->nutritionHelperMock
      ->expects($this->any())
      ->method('getNutritionConfig')
      ->willReturn($this->immutableConfigMock);

    $this->nodeStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->nodeStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn($this->createMock(NodeInterface::class));

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->nodeStorageMock);
    
    $this->routeMatchMock
      ->expects($this->any())
      ->method('getParameter')
      ->willReturn($this->createProductMock('product'));
      
    $this->currentPathMock
      ->expects($this->any())
      ->method('getPath')
      ->willReturn($this->createMock(Request::class));
  }

  /**
   * Mock product node.
   *
   * @param string $node_bundle
   *   Mock node bundle type.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   Mock node object.
   */
  private function createProductMock(string $node_bundle) {
    $product = $this->createMock(Node::class);
    $product_variant_1 = new \stdClass();
    $product_variant_1->entity = $this->createProductVariantMock();
    $product_variant_2 = new \stdClass();
    $product_variant_2->entity = $this->createProductVariantMock();
    $product_variants = [$product_variant_1, $product_variant_2];

    $product
      ->method('id')
      ->willReturn(12345);

    $product
      ->method('bundle')
      ->willReturn($node_bundle);

    $product
      ->method('__get')
      ->will(
        $this->returnCallback((function ($field_name) use ($product_variants) {
          if ($field_name === 'field_product_variants') {
            return $product_variants;
          }
          return $this->createFieldMock();
        }))
      );

    return $product;
  }

  /**
   * Mock product variant node.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   Mock node object.
   */
  private function createProductVariantMock() {
    $product_variant = $this->createMock(Node::class);

    $product_variant
      ->method('id')
      ->willReturn(123456);

    $product_variant
      ->method('bundle')
      ->willReturn('product_variant');

    $product_variant
      ->method('get')
      ->willReturn($this->createFieldMock());

    return $product_variant;
  }

  /**
   * Mock field.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   Mock field object.
   */
  private function createFieldMock() {
    $field = $this->createMock(FieldItemListInterface::class);
    $field_definition = $this->createMock(FieldDefinitionInterface::class);

    $field_definition
      ->method('getLabel')
      ->willReturn('string');

    $field
      ->method('__get')
      ->willReturn('string');

    $field
      ->method('getFieldDefinition')
      ->willReturn($field_definition);

    $field
      ->method('isEmpty')
      ->willReturn('FALSE');

    return $field;
  }

}
