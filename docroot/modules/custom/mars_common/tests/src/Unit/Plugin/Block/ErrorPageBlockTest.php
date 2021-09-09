<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_common\Plugin\Block\ErrorPageBlock;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\Context\Context;

/**
 * Class ErrorPageBlockTest - unit tests.
 */
class ErrorPageBlockTest extends UnitTestCase {

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Error page block.
   *
   * @var \Drupal\mars_common\Plugin\Block\ErrorPageBlock
   */
  private $errorPageBlock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration;

  /**
   * Menu link tree mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Menu\MenuLinkTreeInterface
   */
  private $menuLinkTreeMock;

  /**
   * ThemeConfiguratorParserMock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\ThemeConfiguratorParser
   */
  private $themeConfiguratorParserMock;

  /**
   * Media Helper service Mock.
   *
   * @var \Drupal\mars_media\MediaHelper|\PHPUnit\Framework\MockObject\MockObject
   */
  private $mediaHelperMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $this->configuration = [
      'label_display' => FALSE,
    ];

    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    $this->menuLinkTreeMock
      ->expects($this->any())
      ->method('load')
      ->willReturn([]);
    $this->menuLinkTreeMock
      ->expects($this->any())
      ->method('transform')
      ->willReturn([]);
    $this->menuLinkTreeMock
      ->expects($this->any())
      ->method('build');

    $this->errorPageBlock = new ErrorPageBlock(
      $this->configuration,
      'error_page_block',
      $definitions,
      $this->menuLinkTreeMock,
      $this->themeConfiguratorParserMock,
      $this->mediaHelperMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->menuLinkTreeMock = $this->createMock(MenuLinkTreeInterface::class);
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
  }

  /**
   * Test method build.
   */
  public function testBuild() {
    // Mock node context.
    $node = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
    // Mock string fields.
    $fieldStringMock = $this->getMockBuilder(FieldItemListInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $fieldStringMock->expects($this->any())
      ->method('__get')
      ->with('value')
      ->willReturn('string');

    // Attach field values to calls.
    $node->expects($this->any())
      ->method('__get')
      ->willReturnMap([
        ['title', $fieldStringMock],
        ['body', $fieldStringMock],
      ]);

    $nodeContext = $this->getMockBuilder(Context::class)
      ->disableOriginalConstructor()
      ->getMock();
    $nodeContext->expects($this->once())
      ->method('getContextValue')
      ->willReturn($node);

    $this->errorPageBlock->setContext('node', $nodeContext);
    $build = $this->errorPageBlock->build();

    $this->assertArrayHasKey('#title', $build);
    $this->assertArrayHasKey('#body', $build);
    $this->assertArrayHasKey('#links', $build);
    $this->assertArrayHasKey('#graphic_divider', $build);
    $this->assertArrayHasKey('#brand_shape', $build);
    $this->assertEquals('error_page_block', $build['#theme']);
  }

  /**
   * Test method defaultConfiguration.
   */
  public function testDefaultConfiguration() {
    $default_configuration = $this->errorPageBlock->defaultConfiguration();
    $this->assertEquals(FALSE, $default_configuration['label_display']);
  }

}
