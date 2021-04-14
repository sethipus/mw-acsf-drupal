<?php

namespace Drupal\Tests\mars_seo\Unit\Plugin\JsonLdStrategy;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Layout\LayoutDefinition;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Url;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_seo\Plugin\JsonLdStrategy\Carousel;
use Drupal\Tests\UnitTestCase;
use Spatie\SchemaOrg\ItemList;
use Spatie\SchemaOrg\ListItem;
use Spatie\SchemaOrg\Product;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_seo\Plugin\JsonLdStrategy\Carousel
 * @group mars
 * @group mars_seo
 */
class CarouselTest extends UnitTestCase {

  use JsonLdTestsTrait;

  /**
   * System under test.
   *
   * @var \Drupal\mars_seo\Plugin\JsonLdStrategy\Carousel
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
  const PLUGIN_ID = 'carousel';

  /**
   * The plugin definitions.
   */
  const DEFINITIONS = [
    'provider' => 'test',
    'admin_label' => 'Carousel',
    'label' => 'Carousel',
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
    $this->supportedBundles = [];

    $this->jsonLdPlugin = new Carousel(
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
   */
  public function testIsApplicable() {
    // Test system with empty build context.
    $this->jsonLdPlugin->setContext('node', $this->createNodeContextMock());
    $this->jsonLdPlugin->setContext('build', $this->createBuildContext());
    $this->assertFalse($this->jsonLdPlugin->isApplicable());
    // Test system with filled in 'build' context.
    $build = [
      '_layout_builder' => [
        [
          'recommendation_region' => [
            'recommendation_block' => [
              '#plugin_id' => 'recommendations_module',
              'content' => [],
            ],
          ],
          '#layout' => $this->layoutDefinitionMock,
        ],
      ],
    ];
    $this->jsonLdPlugin->setContext('build', $this->createBuildContext($build));
    $this->assertTrue($this->jsonLdPlugin->isApplicable());
  }

  /**
   * Test.
   */
  public function testGetStructuredData() {
    // Test system with empty build context.
    $this->jsonLdPlugin->setContext('node', $this->createNodeContextMock());
    $this->jsonLdPlugin->setContext('build', $this->createBuildContext());
    $this->assertEmpty($this->jsonLdPlugin->getStructuredData());
    // Test system with filled in 'build' context without Carousel items.
    $this->jsonLdPlugin->setContext('build', $this->createBuildContext(['_layout_builder' => []]));
    $this->assertEmpty($this->jsonLdPlugin->getStructuredData());
    // Test system with filled in recommendation module 'build' context.
    $url_mock = $this->createMock(Url::class);
    $url_mock->expects($this->any())->method('toString')->willReturn('/node/1');
    $build = [
      '_layout_builder' => [
        [
          'recommendation_region' => [
            'recommendation_block' => [
              '#plugin_id' => 'recommendations_module',
              'content' => [
                '#recommended_items' => [
                  [
                    '#node' => $this->createNodeMock([
                      'toUrl' => $url_mock,
                    ]),
                  ],
                  [
                    '#node' => $this->createNodeMock([
                      'toUrl' => $url_mock,
                    ]),
                  ],
                  [
                    '#node' => $this->createNodeMock([
                      'toUrl' => $url_mock,
                    ]),
                  ],
                  [
                    '#node' => $this->createNodeMock([
                      'toUrl' => $url_mock,
                    ]),
                  ],
                  [
                    '#node' => $this->createNodeMock([
                      'toUrl' => $url_mock,
                    ]),
                  ],
                ],
              ],
            ],
          ],
          '#layout' => $this->layoutDefinitionMock,
        ],
      ],
    ];
    // Cleanup testing object state.
    $this->jsonLdPlugin = new Carousel(
      [],
      static::PLUGIN_ID,
      static::DEFINITIONS,
      $this->mediaHelperMock,
      $this->urlGeneratorMock,
      $this->configFactoryMock
    );
    $this->jsonLdPlugin->setContext('build', $this->createBuildContext($build));
    $recommended_schema = $this->jsonLdPlugin->getStructuredData();
    $first_item = current($recommended_schema->getProperties()['itemListElement']);
    $this->assertTrue($recommended_schema instanceof ItemList);
    $this->assertCount(5, $recommended_schema->getProperties()['itemListElement']);
    $this->assertTrue($first_item instanceof ListItem);
    $this->assertCount(2, $first_item->getProperties());
    // Test the system with completely filled in 'build' context
    // and clean plugin object.
    $build['_layout_builder'][0]['recommendation_region']['recommendation_block']['#plugin_id'] = 'pdp_hero_block';
    $build['_layout_builder'][0]['recommendation_region']['recommendation_block']['content'] = [];
    // Cleanup testing object state.
    $this->jsonLdPlugin = new Carousel(
      [],
      static::PLUGIN_ID,
      static::DEFINITIONS,
      $this->mediaHelperMock,
      $this->urlGeneratorMock,
      $this->configFactoryMock
    );
    // Prepare all necessary mocks.
    $config_mock = $this->createMock(Config::class);
    $config_mock->expects($this->any())->method('get')->willReturn('Mars');
    $config_mock->expects($this->any())->method('isNew')->willReturn(FALSE);
    $this->configFactoryMock->expects($this->once())->method('get')->willReturn($config_mock);
    $this->mediaHelperMock->expects($this->any())->method('getMediaUrl')->willReturn('test_image.jpeg');
    $this->mediaHelperMock->expects($this->any())->method('getEntityMainMediaId')->willReturn(1);
    $variants_mock_node_array = [
      '__get' => [
        '_with' => 'field_product_sku',
        'field_product_sku' => $this->fieldItemListMock,
      ],
      'toUrl' => $url_mock,
    ];

    $this->fieldItemListMock->expects($this->any())->method('__get')->willReturnMap([
      ['value', 'test'],
      ['entity', $this->createNodeMock($variants_mock_node_array)],
    ]);

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
          'field_product_variants' => new \ArrayIterator([
            $this->fieldItemListMock,
            $this->fieldItemListMock,
            $this->fieldItemListMock,
            $this->fieldItemListMock,
            $this->fieldItemListMock,
          ]),
        ],
      ],
      'toUrl' => $url_mock,
    ];
    $this->jsonLdPlugin->setContext('node', $this->createNodeContextMock($node_context_params));
    $this->jsonLdPlugin->setContext('build', $this->createBuildContext($build));
    // Call method with prepared contexts and mocks.
    $pdp_schema = $this->jsonLdPlugin->getStructuredData();
    $this->assertEquals($node_context_params['getTitle'], $pdp_schema->getProperties()['name']);
    // Test first item.
    $pdp_first_item = current($pdp_schema->getProperties()['itemListElement']);
    $this->assertTrue($pdp_schema instanceof ItemList);
    $this->assertCount(5, $pdp_schema->getProperties()['itemListElement']);
    $this->assertTrue($pdp_first_item instanceof ListItem);
    $this->assertCount(2, $pdp_first_item->getProperties());
    // Test first item product.
    $pdp_first_item_product = $pdp_first_item->getProperties()['item'];
    $this->assertTrue($pdp_first_item_product instanceof Product);
    $this->assertEquals('Mars', $pdp_first_item_product->getProperties()['brand']);
    $this->assertEquals('test', $pdp_first_item_product->getProperties()['description']);
    $this->assertEquals('test', $pdp_first_item_product->getProperties()['sku']);
    $this->assertEquals('test_image.jpeg', current($pdp_first_item_product->getProperties()['image']));
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
    $this->layoutDefinitionMock = $this->getMockBuilder(LayoutDefinition::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->layoutDefinitionMock->expects($this->any())
      ->method('getRegionNames')
      ->willReturn(['region' => 'recommendation_region']);
  }

}
