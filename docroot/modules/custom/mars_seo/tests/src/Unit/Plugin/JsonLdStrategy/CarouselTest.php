<?php

namespace Drupal\Tests\mars_seo\Unit\Plugin\JsonLdStrategy;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Layout\LayoutDefinition;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Url;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_seo\Plugin\JsonLdStrategy\Carousel;
use Drupal\Tests\UnitTestCase;
use Spatie\SchemaOrg\FAQPage;
use Spatie\SchemaOrg\ItemList;
use Spatie\SchemaOrg\ListItem;
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
                  ]
                ]
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
    $this->assertTrue($this->jsonLdPlugin->getStructuredData() instanceof ItemList);
    // Test the system with completely filled in 'build' context
    // and clean plugin object.
    $build['_layout_builder'][0]['recommendation_region']['recommendation_block']['#plugin_id'] = 'pdp_hero_block';
    $build['_layout_builder'][0]['recommendation_region']['recommendation_block']['content'] = [];
    // @TODO: Test pdp_block plugin.
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
    $this->jsonLdPlugin->setContext('node', $this->createNodeContextMock());
//    $schema = $this->jsonLdPlugin->getStructuredData();
//    $this->assertTrue($schema instanceof ItemList);
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->urlGeneratorMock = $this->createMock(UrlGeneratorInterface::class);
    $this->layoutDefinitionMock = $this->getMockBuilder(LayoutDefinition::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->layoutDefinitionMock->expects($this->any())
      ->method('getRegionNames')
      ->willReturn(['region' => 'recommendation_region']);
  }

}
