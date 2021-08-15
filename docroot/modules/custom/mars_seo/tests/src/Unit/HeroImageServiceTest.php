<?php

namespace Drupal\Tests\mars_seo\Unit;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\layout_builder\Field\LayoutSectionItemList;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_seo\HeroImageService;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_seo\HeroImageService
 * @group mars
 * @group mars_seo
 */
class HeroImageServiceTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_seo\HeroImageService
   */
  private $service;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Config factory service.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Cache\Context\CacheContextsManager
   */
  protected $cacheContextsManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_media\MediaHelper
   */
  private $mediaHelperMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $this->service = new HeroImageService(
      $this->mediaHelperMock,
      $this->configFactoryMock
    );
  }

  /**
   * Test.
   *
   * @covers \Drupal\mars_seo\HeroImageService::getCacheableMetadata
   * @covers \Drupal\mars_seo\HeroImageService::getOpenGraphConfig
   */
  public function testGetCacheableMetadata() {
    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap([
        [
          'cache_contexts_manager',
          ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
          $this->cacheContextsManagerMock,
        ],
      ]);

    $this->cacheContextsManagerMock
      ->expects($this->any())
      ->method('assertValidTokens')
      ->willReturn(TRUE);

    // Test system with empty build context.
    $node_mock = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node_mock->expects($this->any())->method('getCacheContexts')->willReturn(['nid' => 1, 'bundle' => 'article']);
    $node_mock->expects($this->any())->method('getCacheTags')->willReturn(['node:id' => '1']);
    $node_mock->expects($this->any())->method('getCacheMaxAge')->willReturn(0);
    $config_mock = $this->createMock(ImmutableConfig::class);
    $config_mock->expects($this->any())->method('getCacheContexts')->willReturn(['config' => 'og_image']);
    $config_mock->expects($this->any())->method('getCacheTags')->willReturn(['og:image' => 'testpng']);
    $config_mock->expects($this->any())->method('getCacheMaxAge')->willReturn(0);
    $this->configFactoryMock->expects($this->once())->method('get')->willReturn($config_mock);
    $this->mediaHelperMock->expects($this->any())->method('getIdFromEntityBrowserSelectValue')->willReturn('1');
    $cacheable_metadata = $this->service->getCacheableMetadata($node_mock);
    $this->assertTrue($cacheable_metadata instanceof CacheableMetadata);
    $this->assertNotEmpty($cacheable_metadata->getCacheContexts());
    $this->assertIsArray($cacheable_metadata->getCacheContexts());
    $this->assertNotEmpty($cacheable_metadata->getCacheTags());
    $this->assertIsArray($cacheable_metadata->getCacheTags());
    $this->assertEquals(0, $cacheable_metadata->getCacheMaxAge());
  }

  /**
   * Test.
   *
   * @covers \Drupal\mars_seo\HeroImageService::getOpenGraphConfig
   * @covers \Drupal\mars_seo\HeroImageService::getHeroImageUrl
   * @covers \Drupal\mars_seo\HeroImageService::blockIsHeroBlock
   * @covers \Drupal\mars_seo\HeroImageService::extractFromLayoutBuilder
   * @covers \Drupal\mars_seo\HeroImageService::getDefaultImage
   * @covers \Drupal\mars_seo\HeroImageService::getHeroImageFromBlock
   * @covers \Drupal\mars_seo\HeroImageService::getHeroImageId
   */
  public function testGetHeroImageUrl() {
    $node_mock = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
    // Test already found image scenario.
    $this->mediaHelperMock->expects($this->any())->method('getMediaUrl')->with('1')->willReturn('test.png');
    $this->mediaHelperMock->expects($this->any())->method('getEntityMainMediaId')->willReturn('1');
    $this->assertEquals('test.png', $this->service->getHeroImageUrl($node_mock));
    // Test not found image + default image scenario.
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->mediaHelperMock->expects($this->any())->method('getIdFromEntityBrowserSelectValue')->willReturn(1);
    $this->mediaHelperMock->expects($this->any())->method('getMediaUrl')->with('1')->willReturn('test.png');
    $this->mediaHelperMock->expects($this->any())->method('getEntityMainMediaId')->willReturn(NULL);
    $config_mock = $this->createMock(ImmutableConfig::class);
    $config_mock->expects($this->once())->method('get')->willReturn('test.png');
    $this->configFactoryMock->expects($this->once())->method('get')->willReturn($config_mock);
    $this->service = new HeroImageService(
      $this->mediaHelperMock,
      $this->configFactoryMock
    );
    $this->assertEquals('test.png', $this->service->getHeroImageUrl($node_mock));
    // Test with getting the image from the layout builder.
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->mediaHelperMock->expects($this->any())->method('getIdFromEntityBrowserSelectValue')->willReturn(1);
    $this->mediaHelperMock->expects($this->any())->method('getMediaUrl')->with('1')->willReturn('test.png');
    $this->mediaHelperMock->expects($this->any())->method('getEntityMainMediaId')->willReturn(NULL);
    $config_mock = $this->createMock(ImmutableConfig::class);
    $config_mock->expects($this->any())->method('get')->willReturn('test.png');
    $this->configFactoryMock->expects($this->any())->method('get')->willReturn($config_mock);
    $node_mock->expects($this->any())->method('hasField')->willReturn(TRUE);
    $layout_section_item_list_mock = $this->createMock(LayoutSectionItemList::class);
    $layout_builder_section_mock = $this->createMock(Section::class);
    $section_component_mock = $this->createMock(SectionComponent::class);
    $section_component_mock->expects($this->any())->method('getPluginId')->willReturn('homepage_hero_block');
    $section_component_mock->expects($this->any())->method('get')->with('configuration')->willReturn(['block_type' => 'image', 'background_image' => 'hero_image_field']);
    $layout_builder_section_mock->expects($this->any())->method('getComponents')->willReturn(
      [
        $section_component_mock,
        $section_component_mock,
        $section_component_mock,
      ]
    );
    $layout_section_item_list_mock->expects($this->any())->method('getSections')->willReturn([
      $layout_builder_section_mock,
      $layout_builder_section_mock,
      $layout_builder_section_mock,
    ]);
    $node_mock->expects($this->any())->method('get')->with('layout_builder__layout')->willReturn($layout_section_item_list_mock);
    $this->service = new HeroImageService(
      $this->mediaHelperMock,
      $this->configFactoryMock
    );
    $this->assertEquals('test.png', $this->service->getHeroImageUrl($node_mock));
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->cacheContextsManagerMock = $this->createMock(CacheContextsManager::class);
  }

}
