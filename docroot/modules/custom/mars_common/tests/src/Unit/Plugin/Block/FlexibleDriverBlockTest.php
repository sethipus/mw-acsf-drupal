<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\mars_common\LanguageHelper;
use Drupal\Tests\UnitTestCase;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_common\Plugin\Block\FlexibleDriverBlock;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class FlexibleDriverBlockTest - unit tests.
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
   * Media Helper service mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_media\MediaHelper
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
    'select_background_color' => '',
  ];

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\LanguageHelper
   */
  private $languageHelperMock;

  /**
   * Config factory mock.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactoryMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->createMocks();
    $definitions = [
      'provider' => 'test',
      'admin_label' => 'test',
    ];

    $configMock = $this->getMockBuilder(stdClass::class)
      ->setMethods(['get'])
      ->getMock();

    $this->configFactoryMock
      ->method('get')
      ->with('mars_common.character_limit_page')
      ->willReturn($configMock);

    $this->flexibleDriverBlock = new FlexibleDriverBlock(
      $this->configuration,
      'flexible_driver',
      $definitions,
      $this->mediaHelperMock,
      $this->languageHelperMock,
      $this->themeConfiguratorParserMock,
      $this->configFactoryMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->languageHelperMock = $this->createLanguageHelperMock();
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
  }

  /**
   * Returns Language helper mock.
   *
   * @return \Drupal\mars_common\LanguageHelper|\PHPUnit\Framework\MockObject\MockObject
   *   Theme Configuration Parser service mock.
   */
  private function createLanguageHelperMock() {
    $mock = $this->createMock(LanguageHelper::class);
    $mock->method('translate')
      ->will(
        $this->returnCallback(function ($arg) {
          return $arg;
        })
      );

    return $mock;
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

    $this->assertCount(8, $build);
    $this->assertEquals('flexible_driver_block', $build['#theme']);
    $this->assertEquals($this->configuration['title'], $build['#title']);
    $this->assertEquals($this->configuration['description'], $build['#description']);
  }

}
