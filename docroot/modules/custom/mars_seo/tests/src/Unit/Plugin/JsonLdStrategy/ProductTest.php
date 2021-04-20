<?php

namespace Drupal\Tests\mars_seo\Unit\Plugin\JsonLdStrategy;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_seo\Plugin\JsonLdStrategy\Product;
use Drupal\Tests\UnitTestCase;
use Spatie\SchemaOrg\Product as ProductSchema;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_seo\Plugin\JsonLdStrategy\Product
 * @group mars
 * @group mars_seo
 */
class ProductTest extends UnitTestCase {

  use JsonLdTestsTrait;

  /**
   * System under test.
   *
   * @var \Drupal\mars_seo\Plugin\JsonLdStrategy\Product
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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\MediaHelper
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
   * The plugin ID.
   */
  const PLUGIN_ID = 'product';

  /**
   * The plugin definitions.
   */
  const DEFINITIONS = [
    'provider' => 'test',
    'admin_label' => 'Product',
    'label' => 'Product Page',
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
    $this->supportedBundles = ['product', 'product_multipack'];

    $this->jsonLdPlugin = new Product(
      $configuration,
      static::PLUGIN_ID,
      static::DEFINITIONS,
      $this->mediaHelperMock,
      $this->urlGeneratorMock,
      $this->configFactoryMock
    );
  }

  /**
   * Test.
   *
   * @test
   *
   * @covers \Drupal\mars_seo\Plugin\JsonLdStrategy\Product::isApplicable
   * @covers \Drupal\mars_seo\Plugin\JsonLdStrategy\Product::supportedBundles
   * @covers \Drupal\mars_seo\Plugin\JsonLdStrategy\Product::getContextValue
   */
  public function testIsApplicable() {
    // Test system with empty build & node contexts.
    $this->jsonLdPlugin->setContext('node', $this->createNodeContextMock(['bundle' => '']));
    $this->jsonLdPlugin->setContext('build', $this->createBuildContext());
    $this->assertFalse($this->jsonLdPlugin->isApplicable());
    // Test system with correct node context.
    $this->jsonLdPlugin->setContext('node', $this->createNodeContextMock(['bundle' => 'product']));
    $this->assertTrue($this->jsonLdPlugin->isApplicable());
  }

  /**
   * Test.
   *
   * @test
   *
   * @covers \Drupal\mars_seo\Plugin\JsonLdStrategy\Product::getStructuredData
   * @covers \Drupal\mars_seo\Plugin\JsonLdStrategy\Product::getContextValue
   */
  public function testGetStructuredData() {
    // Prepare all necessary contexts.
    $this->jsonLdPlugin->setContext('build', $this->createBuildContext());

    $variants_mock_node_array = [
      '__get' => [
        '_with' => 'field_product_sku',
        'field_product_sku' => $this->fieldItemListMock,
      ],
    ];

    $this->fieldItemListMock->expects($this->any())->method('__get')->willReturnMap([
      ['value', 'test'],
      ['entity', $this->createNodeMock($variants_mock_node_array)],
    ]);

    $this->fieldItemListMock
      ->expects($this->any())
      ->method('first')
      ->willReturn($this->fieldItemListMock);

    $node_context_params = [
      'getTitle' => 'test title',
      'id' => 1,
      'multiple_get_with' => [
        [
          '_with' => 'field_product_description',
          'field_product_description' => $this->fieldItemListMock,
        ],
        [
          '_with' => 'field_product_variants',
          'field_product_variants' => $this->fieldItemListMock,
        ],
      ],
    ];
    $this->jsonLdPlugin->setContext('node', $this->createNodeContextMock($node_context_params));
    $this->mediaHelperMock->expects($this->any())->method('getMediaUrl')->willReturn('test_image.jpeg');
    $this->mediaHelperMock->expects($this->any())->method('getEntityMainMediaId')->willReturn(1);
    $config_mock = $this->createMock(Config::class);
    $config_mock->expects($this->any())->method('get')->willReturn('Mars');
    $config_mock->expects($this->any())->method('isNew')->willReturn(FALSE);
    $this->configFactoryMock->expects($this->once())->method('get')->willReturn($config_mock);
    $this->urlGeneratorMock->expects($this->once())->method('generateFromRoute')->willReturn('/node/' . $node_context_params['id']);

    // Test system with prepared data.
    $schema = $this->jsonLdPlugin->getStructuredData();
    $this->assertTrue($schema instanceof ProductSchema);
    $this->assertEquals($node_context_params['getTitle'], $schema->getProperties()['name']);
    $this->assertEquals('/node/' . $node_context_params['id'], $schema->getProperties()['identifier']);
    $this->assertEquals('Mars', $schema->getProperties()['brand']);
    $this->assertEquals('test', $schema->getProperties()['description']);
    $this->assertEquals('test', $schema->getProperties()['sku']);
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
  }

}
