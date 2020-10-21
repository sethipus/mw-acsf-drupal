<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\Tests\UnitTestCase;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\Plugin\Block\FlexibleDriverBlock;

/**
 * Class FlexibleDriverBlockTest.
 *
 * @package Drupal\Tests\mars_common\Unit
 * @covers \Drupal\mars_common\Plugin\Block\FooterBlock
 */
class FlexibleDriverBlockTest extends UnitTestCase {

  const STORY_ITEM_1_MEDIA_ID = 10;
  const STORY_ITEM_1_ENTITY_BROWSER_VALUE = 'media:10';
  const STORY_ITEM_2_MEDIA_ID = 15;
  const STORY_ITEM_2_ENTITY_BROWSER_VALUE = 'media:15';
  const STORY_ITEM_3_MEDIA_ID = 20;
  const STORY_ITEM_3_ENTITY_BROWSER_VALUE = 'media:20';

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Tested FlexibleDriverBlock block.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\Plugin\Block\FlexibleDriverBlock
   */
  private $flexibleDriverBlock;

  /**
   * ThemeConfiguratorParserMock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParserMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Form\FormStateInterface
   */
  private $formStateMock;

  /**
   * EntityBrowserForm mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|Drupal\mars_common\Plugin\Block\FlexibleDriverBlock
   */
  protected $entityBrowserFormMock;

  /**
   * GetMediaIdMock mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|Drupal\mars_common\Plugin\Block\FlexibleDriverBlock
   */
  protected $getMediaIdMock;


  /**
   * Media Helper service mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\MediaHelper
   */
  protected $mediaHelperMock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration = [
    'title' => 'Flexible driver',
    'description' => 'Description',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->createMocks();
    // $container = new ContainerBuilder();
    // $container->set(
    // 'mars_common.media_helper',
    // $this->mediaHelperMock2
    // );
    // $container->set(
    // 'mars_common.theme_configurator_parser',
    // $this->themeConfiguratorParserMock
    // );
    // Drupal::setContainer($this->containerMock);
    $definitions = [
      'provider' => 'test',
      'admin_label' => 'test',
    ];

    $this->flexibleDriverBlock = new FlexibleDriverBlock(
      $this->configuration,
      'flexible_driver',
      $definitions,
      $this->mediaHelperMock,
      $this->themeConfiguratorParserMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    // $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    // $this->formStateMock = $this->createMock(FormStateInterface::class);
    // $this->entityBrowserFormMock =
    // $this->createMock(EntityBrowserFormTrait::class);
    // $this->entityBrowserFormMock
    // ->method('getEntityBrowserForm')
    // ->willReturn([]);
    // $this->getMediaIdMock = $this->getMockBuilder('FlexibleDriverBlock')->setMethods(['getMediaId'])->getMock();
    // $this->entityBrowserFormMock = $this->getMockBuilder('FlexibleDriverBlock')->setMethods(['getEntityBrowserForm'])->getMock();
  }

  /**
   * Test building block.
   */
  public function testBuildBlockRenderArrayProperly() {
    $this->mediaHelperMock
      ->expects($this->exactly(2))
      ->method('getIdFromEntityBrowserSelectValue')
      ->willReturnMap([
        [
          self::STORY_ITEM_1_ENTITY_BROWSER_VALUE,
          self::STORY_ITEM_1_MEDIA_ID,
        ],
        [
          self::STORY_ITEM_2_ENTITY_BROWSER_VALUE,
          self::STORY_ITEM_2_MEDIA_ID,
        ],
        [
          self::STORY_ITEM_3_ENTITY_BROWSER_VALUE,
          self::STORY_ITEM_3_MEDIA_ID,
        ],
      ]);

    $build = $this->flexibleDriverBlock->build();

    $this->assertCount(7, $build);
    $this->assertEquals('flexible_driver_block', $build['#theme']);
    $this->assertEquals($this->configuration['title'], $build['#title']);
    $this->assertEquals($this->configuration['description'], $build['#description']);
  }

}
