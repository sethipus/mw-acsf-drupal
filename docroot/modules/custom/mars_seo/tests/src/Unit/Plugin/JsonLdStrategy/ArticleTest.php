<?php

namespace Drupal\Tests\mars_seo\Unit\Plugin\JsonLdStrategy;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Layout\LayoutDefinition;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_seo\Plugin\JsonLdStrategy\Article;
use Drupal\Tests\UnitTestCase;
use Spatie\SchemaOrg\NewsArticle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_seo\Plugin\JsonLdStrategy\Article
 * @group mars
 * @group mars_seo
 */
class ArticleTest extends UnitTestCase {

  use JsonLdTestsTrait;

  /**
   * System under test.
   *
   * @var \Drupal\mars_seo\Plugin\JsonLdStrategy\Article
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
  const PLUGIN_ID = 'news_article';

  /**
   * The plugin definitions.
   */
  const DEFINITIONS = [
    'provider' => 'test',
    'admin_label' => 'Article',
    'label' => 'Article Page',
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
    $this->supportedBundles = ['article'];

    $this->jsonLdPlugin = new Article(
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
    $this->jsonLdPlugin->setContext('node', $this->createNodeContextMock(['bundle' => '']));
    $this->jsonLdPlugin->setContext('build', $this->createBuildContext());
    $this->assertFalse($this->jsonLdPlugin->isApplicable());
    $this->jsonLdPlugin->setContext('node', $this->createNodeContextMock(['bundle' => 'article']));
    $this->assertTrue($this->jsonLdPlugin->isApplicable());
  }

  /**
   * Test.
   */
  public function testGetStructuredData() {
    // Prepare all necessary contexts.
    $this->jsonLdPlugin->setContext('build', $this->createBuildContext());
    $this->fieldItemListMock
      ->expects($this->any())
      ->method('__get')
      ->with('target_id')
      ->willReturn('1');

    $node_context_params = [
      'getTitle' => 'test title',
      '__get' => [
        '_with' => 'field_article_image',
        'field_article_image' => $this->fieldItemListMock,
      ],
    ];
    $this->jsonLdPlugin->setContext('node', $this->createNodeContextMock($node_context_params));
    $this->mediaHelperMock->expects($this->any())->method('getMediaUrl')->willReturn('test_image.jpeg');

    // Test system with prepared data.
    $schema = $this->jsonLdPlugin->getStructuredData();
    $this->assertTrue($schema instanceof NewsArticle);
    $this->assertEquals($node_context_params['getTitle'], $schema->getProperties()['headline']);
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
    $this->layoutDefinitionMock = $this->getMockBuilder(LayoutDefinition::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->layoutDefinitionMock->expects($this->any())
      ->method('getRegionNames')
      ->willReturn(['region' => 'faq_region']);
    $this->fieldItemListMock = $this->createMock(EntityReferenceFieldItemListInterface::class);
  }

}
