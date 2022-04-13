<?php

namespace Drupal\Tests\mars_articles\Unit\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Utility\Token;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mars_articles\Plugin\Block\ArticleHeader;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\publication_date\Plugin\Field\FieldType\PublicationDateItem;

/**
 * @coversDefaultClass \Drupal\mars_articles\Plugin\Block\ArticleHeader
 * @group mars
 * @group mars_articles
 */
class ArticleHeaderTest extends UnitTestCase {

  /**
   * Configuration data array.
   */
  private const TEST_CONFIGURATION = [
    'id' => 'article_header',
    'label' => 'Article header',
    'provider' => 'mars_articles',
    'eyebrow' => 'Test eyebrow text',
    'article' => 1,
  ];

  /**
   * Definition data array.
   */
  private const TEST_DEFENITION = [
    'provider' => 'mars_articles',
    'admin_label' => 'Article header',
  ];

  /**
   * Social media test configuration.
   */
  private const TEST_SOCIAL_CONFIG = [
    'social' => [
      'enable' => 1,
      'api_url' => 'http://domain.com',
      'text' => 'Test social',
      'img' => 'test/image/path/image.png',
      'attributes' => '',
    ],
  ];

  /**
   * System under test.
   *
   * @var \Drupal\mars_articles\Plugin\Block\ArticleHeader
   */
  private $articleHeaderBlock;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\LanguageHelper
   */
  private $languageHelperMock;

  /**
   * Mock.
   *
   * @var \Drupal\mars_media\MediaHelper|\PHPUnit\Framework\MockObject\MockObject
   */
  private $mediaHelperMock;

  /**
   * Mock.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser|\PHPUnit\Framework\MockObject\MockObject
   */
  private $themeConfiguratorParserMock;

  /**
   * Entity type manager mock.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManagerMock;

  /**
   * Date formatter mock.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private $dateFormatterMock;

  /**
   * Token mock.
   *
   * @var \Drupal\Core\Utility\Token
   */
  private $tokenMock;

  /**
   * Config factory mock.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactoryMock;

  /**
   * View builder mock.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  private $viewBuilderMock;

  /**
   * Entity storage mock.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $entityStorageMock;

  /**
   * Node mock.
   *
   * @var \Drupal\node\NodeInterface
   */
  private $nodeMock;

  /**
   * Immutable config mock.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $immutableConfigMock;

  /**
   * Translation mock.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translationMock;

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

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getViewBuilder')
      ->willReturn($this->viewBuilderMock);

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->articleHeaderBlock = new ArticleHeader(
      self::TEST_CONFIGURATION,
      'article_header',
      self::TEST_DEFENITION,
      $this->entityTypeManagerMock,
      $this->dateFormatterMock,
      $this->tokenMock,
      $this->themeConfiguratorParserMock,
      $this->configFactoryMock,
      $this->languageHelperMock,
      $this->mediaHelperMock
    );
  }

  /**
   * Test dependency injections.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(7))
      ->method('get')
      ->willReturnMap(
        [
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'date.formatter',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->dateFormatterMock,
          ],
          [
            'token',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->tokenMock,
          ],
          [
            'mars_common.theme_configurator_parser',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->themeConfiguratorParserMock,
          ],
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configFactoryMock,
          ],
          [
            'mars_common.language_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->languageHelperMock,
          ],
          [
            'mars_media.media_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->mediaHelperMock,
          ],
        ]
      );

    $this->articleHeaderBlock::create(
      $this->containerMock,
      self::TEST_CONFIGURATION,
      'article_header',
      self::TEST_DEFENITION,
    );
  }

  /**
   * Test block build.
   */
  public function testShouldBuild() {
    // Mock node context.
    $nodeMock = $this->createNodeMock();
    $nodeContext = $this->getMockBuilder(Context::class)
      ->disableOriginalConstructor()
      ->getMock();
    $nodeContext->expects($this->exactly(2))
      ->method('getContextValue')
      ->willReturn($nodeMock);
    $this->articleHeaderBlock->setContext('node', $nodeContext);

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->immutableConfigMock);

    $this->immutableConfigMock
      ->expects($this->any())
      ->method('get')
      ->willReturn(self::TEST_SOCIAL_CONFIG);

    $this->immutableConfigMock
      ->method('getCacheContexts')
      ->willReturn([]);

    $this->immutableConfigMock
      ->method('getCacheTags')
      ->willReturn([]);

    $this->immutableConfigMock
      ->method('getCacheMaxAge')
      ->willReturn(0);

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
    $this->dateFormatterMock
      ->expects($this->once())
      ->method('format')
      ->with(1234567890, 'article_header');

    $this->mediaHelperMock
      ->expects($this->once())
      ->method('getResponsiveImagesFromEntity')
      ->willReturn([
        'desktop' => [
          'src' => 'test_image_source',
          'alt' => 'test_image_alt',
        ],
        'tablet' => [
          'src' => 'test_image_source',
          'alt' => 'test_image_alt',
        ],
        'mobile' => [
          'src' => 'test_image_source',
          'alt' => 'test_image_alt',
        ],
      ]);

    $block_build = $this->articleHeaderBlock->build();

    $this->assertEquals('Test article', $block_build['#label']);
    $this->assertEquals('article_header_block_image', $block_build['#theme']);
    $this->assertArrayHasKey('#images', $block_build);
    foreach (['desktop', 'tablet', 'mobile'] as $resolution) {
      $this->assertArrayHasKey($resolution, $block_build['#images']);
    }
    $this->assertArrayHasKey('social', $block_build['#social_links']);
    $this->assertArrayHasKey('icon', $block_build['#social_links']['social']);
    $this->assertArrayHasKey('#share_text', $block_build);
  }

  /**
   * Test configuration form.
   */
  public function testShouldBuildConfigurationForm() {
    $form_array = [];

    $this->entityStorageMock
      ->expects($this->exactly(1))
      ->method('load')
      ->willReturn($this->nodeMock);

    $block_form = $this->articleHeaderBlock->buildConfigurationForm(
      $form_array,
      $this->formStateMock
    );
    $this->assertSame(
      self::TEST_CONFIGURATION['eyebrow'],
      $block_form['eyebrow']['#default_value']
    );
    $this->assertSame(
      $this->nodeMock,
      $block_form['article']['#default_value']
    );
    $this->assertIsArray($block_form);
  }

  /**
   * Test block submit.
   */
  public function testShouldBlockSubmit() {
    $form_data = [];

    $this->formStateMock
      ->expects($this->once())
      ->method('cleanValues')
      ->willReturn($this->formStateMock);

    $this->formStateMock
      ->expects($this->once())
      ->method('getValues')
      ->willReturn(self::TEST_CONFIGURATION);

    $this->articleHeaderBlock->blockSubmit(
      $form_data,
      $this->formStateMock
    );
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->dateFormatterMock = $this->createMock(DateFormatterInterface::class);
    $this->tokenMock = $this->createMock(Token::class);
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->viewBuilderMock = $this->createMock(EntityViewBuilderInterface::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->nodeMock = $this->createMock(NodeInterface::class);
    $this->immutableConfigMock = $this->createMock(ImmutableConfig::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
  }

  /**
   * Mock recipe node.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   Mock node object.
   */
  private function createNodeMock() {
    $published = $this->getMockBuilder(PublicationDateItem::class)
      ->disableOriginalConstructor()
      ->getMock();
    $published
      ->expects($this->once())
      ->method('__get')
      ->with('value')
      ->willReturn(1234567890);
    $node = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();

    $node
      ->expects($this->once())
      ->method('bundle')
      ->willReturn('article');
    $node
      ->expects($this->once())
      ->method('label')
      ->willReturn('Test article');
    $node
      ->expects($this->once())
      ->method('isPublished')
      ->willReturn(TRUE);
    $node
      ->expects($this->once())
      ->method('__get')
      ->with('published_at')
      ->willReturn($published);

    return $node;
  }

}
