<?php

namespace Drupal\Tests\mars_articles\Unit\Plugin\Block;

use Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Utility\Token;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mars_articles\Plugin\Block\ArticleHeader;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\node\NodeInterface;

/**
 * @coversDefaultClass \Drupal\mars_articles\Plugin\Block\ArticleHeader
 * @group mars
 * @group mars_articles
 */
class ArticleHeaderTest extends UnitTestCase {

  private const TEST_CONFIGURATION = [
    'id' => 'article_header',
    'label' => 'Article header',
    'provider' => 'mars_articles',
    'eyebrow' => 'Test eyebrow text',
    'article' => 1,
  ];

  private const TEST_DEFENITION = [
    'provider' => 'mars_articles',
    'admin_label' => 'Article header',
  ];

  private const TEST_NODE = [
    'label' => 'article test label',
    'provider' => 'mars_articles',
    'eyebrow' => 'Test eyebrow text',
    'article' => 1,
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
   * @var \Drupal\mars_common\MediaHelper|\PHPUnit\Framework\MockObject\MockObject
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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    Drupal::setContainer($this->containerMock);

    $this->entityTypeManagerMock
      ->expects($this->exactly(1))
      ->method('getViewBuilder')
      ->willReturn($this->viewBuilderMock);

    $this->entityTypeManagerMock
      ->expects($this->exactly(1))
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
      $this->mediaHelperMock
    );
  }

  /**
   * Test block build.
   */
  public function testShouldBuild() {

    $this->entityStorageMock
      ->expects($this->once())
      ->method('load')
      ->willReturn($this->nodeMock);
    $this->nodeMock
      ->expects($this->once())
      ->method('label')
      ->willReturn(self::TEST_NODE['label']);
    $block_build = $this->articleHeaderBlock->build();

    $this->assertEquals(self::TEST_NODE['label'], $block_build['#label']);
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
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->viewBuilderMock = $this->createMock(EntityViewBuilderInterface::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->nodeMock = $this->createMock(NodeInterface::class);
  }

}
