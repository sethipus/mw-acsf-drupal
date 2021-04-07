<?php

namespace Drupal\Tests\mars_seo\Unit\Plugin\JsonLdStrategy;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Layout\LayoutDefinition;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_seo\Plugin\JsonLdStrategy\Faq;
use Drupal\Tests\UnitTestCase;
use Spatie\SchemaOrg\FAQPage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_seo\Plugin\JsonLdStrategy\Faq
 * @group mars
 * @group mars_seo
 */
class FaqTest extends UnitTestCase {

  use JsonLdTestsTrait;

  /**
   * System under test.
   *
   * @var \Drupal\mars_seo\Plugin\JsonLdStrategy\Faq
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
  const PLUGIN_ID = 'faq';

  /**
   * The plugin definitions.
   */
  const DEFINITIONS = [
    'provider' => 'test',
    'admin_label' => 'FAQ',
    'label' => 'FAQ Page',
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

    $this->jsonLdPlugin = new Faq(
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
          'faq_region' => [
            'faq_block' => [
              '#plugin_id' => 'search_faq_block',
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
    $this->jsonLdPlugin->setContext('build', $this->createBuildContext(['_layout_builder' => []]));
    $this->assertEmpty($this->jsonLdPlugin->getStructuredData());
    // Test system with filled in 'build' context without FAQ items.
    $build = [
      '_layout_builder' => [
        [
          'faq_region' => [
            'faq_block' => [
              '#plugin_id' => 'search_faq_block',
              'content' => [],
            ],
          ],
          '#layout' => $this->layoutDefinitionMock,
        ],
      ],
    ];
    $this->jsonLdPlugin->setContext('build', $this->createBuildContext($build));
    $this->assertEmpty($this->jsonLdPlugin->getStructuredData());
    // Test the system with completely filled in 'build' context
    // and clean plugin object.
    $build['_layout_builder'][0]['faq_region']['faq_block']['content']['#qa_items'] = [
      [
        'content' => [
          'question' => 'test',
          'answer' => 'test',
        ],
      ],
    ];
    // Cleanup testing object state.
    $this->jsonLdPlugin = new Faq(
      [],
      static::PLUGIN_ID,
      static::DEFINITIONS,
      $this->mediaHelperMock,
      $this->urlGeneratorMock,
      $this->configFactoryMock
    );
    $this->jsonLdPlugin->setContext('build', $this->createBuildContext($build));
    $schema = $this->jsonLdPlugin->getStructuredData();
    $this->assertTrue($schema instanceof FAQPage);
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
  }

}
