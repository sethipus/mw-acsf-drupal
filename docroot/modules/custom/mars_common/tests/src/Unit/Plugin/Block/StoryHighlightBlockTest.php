<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepository;
use Drupal\file\Entity\File;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_common\Plugin\Block\StoryHighlightBlock;
use Drupal\mars_media\SVG\SVG;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\media\Entity\Media;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class StoryHighlightBlockTest - unit tests.
 *
 * @covers \Drupal\mars_common\Plugin\Block\StoryHighlightBlock
 */
class StoryHighlightBlockTest extends UnitTestCase {

  const STORY_ITEM_1_MEDIA_ID = 10;
  const STORY_ITEM_1_MEDIA_URI = 'public://story_media/image1.png';
  const STORY_ITEM_1_ENTITY_BROWSER_VALUE = 'media:10';
  const STORY_ITEM_2_MEDIA_ID = 15;
  const STORY_ITEM_2_MEDIA_URI = 'public://story_media/image2.png';
  const STORY_ITEM_2_ENTITY_BROWSER_VALUE = 'media:15';
  const STORY_ITEM_3_MEDIA_ID = 20;
  const STORY_ITEM_3_MEDIA_URI = 'public://story_media/image3.png';
  const STORY_ITEM_3_ENTITY_BROWSER_VALUE = 'media:20';
  const SVG_ASSET_1_MEDIA_ID = 25;
  const SVG_ASSET_1_MEDIA_URI = 'public://svg_asset/image1.png';
  const SVG_ASSET_1_ENTITY_BROWSER_VALUE = 'media:25';
  const SVG_ASSET_2_MEDIA_ID = 30;
  const SVG_ASSET_2_MEDIA_URI = 'public://svg_asset/image2.png';
  const SVG_ASSET_2_ENTITY_BROWSER_VALUE = 'media:30';
  const SVG_ASSET_3_MEDIA_ID = 35;
  const SVG_ASSET_3_MEDIA_URI = 'public://svg_asset/image3.png';
  const SVG_ASSET_3_ENTITY_BROWSER_VALUE = 'media:35';

  /**
   * Entity Type Manager Mock.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $entityTypeManagerMock;

  /**
   * Entity Type Repository Service Mock.
   *
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $entityTypeRepositoryMock;

  /**
   * File Entity Storage Mock.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $fileStorageMock;

  /**
   * Media Entity Storage Mock.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $mediaStorageMock;

  /**
   * Media Helper service Mock.
   *
   * @var \Drupal\mars_media\MediaHelper|\PHPUnit\Framework\MockObject\MockObject
   */
  private $mediaHelperMock;

  /**
   * Media entity storage.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser|\PHPUnit\Framework\MockObject\MockObject
   */
  private $themeConfigurationParserMock;

  /**
   * Story Highlight default block plugin definitions.
   *
   * @var array
   */
  private $defaultDefinitions;

  /**
   * Story Highlight default block configuration.
   *
   * @var array
   */
  private $defaultConfiguration;

  /**
   * Internal File Storage.
   *
   * @var \Drupal\file\Entity\File[]
   */
  private $internalFileStorage = [];

  /**
   * Internal Media Storage.
   *
   * @var \Drupal\media\Entity\Media[]
   */
  private $internalMediaStorage = [];

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\LanguageHelper
   */
  private $languageHelperMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->createMocks();

    $container = new ContainerBuilder();
    $container->set('string_translation', $this->getStringTranslationStub());
    $container->set('entity_type.manager', $this->entityTypeManagerMock);
    $container->set('entity_type.repository', $this->entityTypeRepositoryMock);
    $container->set('mars_media.media_helper', $this->mediaHelperMock);
    $container->set('mars_common.theme_configurator_parser', $this->themeConfigurationParserMock);
    $container->set('mars_common.language_helper', $this->languageHelperMock);
    \Drupal::setContainer($container);

    $this->defaultDefinitions = [
      'provider' => 'test',
      'admin_label' => 'test',
    ];

    $this->defaultConfiguration = [
      'story_block_title' => 'Test Block Title',
      'story_block_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
      'items' => [
        [
          'title' => 'Story Item 1',
          'item_type' => 'image',
          'image' => self::STORY_ITEM_1_ENTITY_BROWSER_VALUE,
        ],
        [
          'title' => 'Story Item 2',
          'item_type' => 'image',
          'image' => self::STORY_ITEM_2_ENTITY_BROWSER_VALUE,
        ],
        [
          'title' => 'Story Item 3',
          'item_type' => 'image',
          'image' => self::STORY_ITEM_3_ENTITY_BROWSER_VALUE,
        ],
      ],
      'svg_assets' => [
        'svg_asset_1' => self::SVG_ASSET_1_ENTITY_BROWSER_VALUE,
        'svg_asset_2' => self::SVG_ASSET_2_ENTITY_BROWSER_VALUE,
        'svg_asset_3' => self::SVG_ASSET_3_ENTITY_BROWSER_VALUE,
      ],
      'view_more' => [
        'label' => 'View Extra',
        'url' => 'https://mars.com/',
      ],
      'with_brand_borders' => TRUE,
    ];
  }

  /**
   * Test happy path for block content generation.
   */
  public function testValidBlockBuild() {
    $storyHighlightBlock = StoryHighlightBlock::create(
      \Drupal::getContainer(),
      $this->defaultConfiguration,
      'story_highlight',
      $this->defaultDefinitions
    );

    $build = $storyHighlightBlock->build();

    $expected = [
      '#theme' => 'story_highlight_block',
      '#title' => 'Test Block Title',
      '#brand_border' => new SVG('Mocked brand_borders_2 content', 'id'),
      '#graphic_divider' => new SVG('Mocked graphic_divider content', 'id'),
      '#story_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
      '#story_items' => [
        [
          'video' => FALSE,
          'image' => TRUE,
          'content' => 'Story Item 1',
          'src' => self::STORY_ITEM_1_MEDIA_URI,
          'alt' => 'Alt',
          'title' => 'Title',
          'hide_volume' => FALSE,
        ],
        [
          'video' => FALSE,
          'image' => TRUE,
          'content' => 'Story Item 2',
          'src' => self::STORY_ITEM_2_MEDIA_URI,
          'alt' => 'Alt',
          'title' => 'Title',
          'hide_volume' => FALSE,
        ],
        [
          'video' => FALSE,
          'image' => TRUE,
          'content' => 'Story Item 3',
          'src' => self::STORY_ITEM_3_MEDIA_URI,
          'alt' => 'Alt',
          'title' => 'Title',
          'hide_volume' => FALSE,
        ],
      ],
      '#svg_asset_src_1' => self::SVG_ASSET_1_MEDIA_URI,
      '#svg_asset_alt_1' => 'Alt',
      '#svg_asset_src_2' => self::SVG_ASSET_2_MEDIA_URI,
      '#svg_asset_alt_2' => 'Alt',
      '#svg_asset_src_3' => self::SVG_ASSET_3_MEDIA_URI,
      '#svg_asset_alt_3' => 'Alt',
      '#view_more_cta_url' => 'https://mars.com/',
      '#view_more_cta_label' => 'View Extra',
      '#overlaps_previous' => FALSE,
      '#text_color_override' => FALSE,
    ];

    $this->assertEquals($expected, $build);
  }

  /**
   * Test block content generation without overridden CTA label.
   */
  public function testValidBlockBuildWithoutCtaLabel() {
    $configuration = $this->defaultConfiguration;
    unset($configuration['view_more']['label']);

    $storyHighlightBlock = StoryHighlightBlock::create(
      \Drupal::getContainer(),
      $configuration,
      'story_highlight',
      $this->defaultDefinitions
    );

    $build = $storyHighlightBlock->build();

    $expected = [
      '#theme' => 'story_highlight_block',
      '#title' => 'Test Block Title',
      '#brand_border' => new SVG('Mocked brand_borders_2 content', 'id'),
      '#graphic_divider' => new SVG('Mocked graphic_divider content', 'id'),
      '#story_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
      '#story_items' => [
        [
          'video' => FALSE,
          'image' => TRUE,
          'content' => 'Story Item 1',
          'src' => self::STORY_ITEM_1_MEDIA_URI,
          'alt' => 'Alt',
          'title' => 'Title',
          'hide_volume' => FALSE,
        ],
        [
          'video' => FALSE,
          'image' => TRUE,
          'content' => 'Story Item 2',
          'src' => self::STORY_ITEM_2_MEDIA_URI,
          'alt' => 'Alt',
          'title' => 'Title',
          'hide_volume' => FALSE,
        ],
        [
          'video' => FALSE,
          'image' => TRUE,
          'content' => 'Story Item 3',
          'src' => self::STORY_ITEM_3_MEDIA_URI,
          'alt' => 'Alt',
          'title' => 'Title',
          'hide_volume' => FALSE,
        ],
      ],
      '#svg_asset_src_1' => self::SVG_ASSET_1_MEDIA_URI,
      '#svg_asset_alt_1' => 'Alt',
      '#svg_asset_src_2' => self::SVG_ASSET_2_MEDIA_URI,
      '#svg_asset_alt_2' => 'Alt',
      '#svg_asset_src_3' => self::SVG_ASSET_3_MEDIA_URI,
      '#svg_asset_alt_3' => 'Alt',
      '#view_more_cta_url' => 'https://mars.com/',
      '#view_more_cta_label' => 'View More',
      '#overlaps_previous' => FALSE,
      '#text_color_override' => FALSE,
    ];

    $this->assertEquals($expected, $build);
  }

  /**
   * Test block content generation without CTA URL set.
   */
  public function testValidBlockBuildWithoutCtaUrl() {
    $configuration = $this->defaultConfiguration;
    unset($configuration['view_more']['url']);

    $storyHighlightBlock = StoryHighlightBlock::create(
      \Drupal::getContainer(),
      $configuration,
      'story_highlight',
      $this->defaultDefinitions
    );

    $build = $storyHighlightBlock->build();

    $expected = [
      '#theme' => 'story_highlight_block',
      '#title' => 'Test Block Title',
      '#brand_border' => new SVG('Mocked brand_borders_2 content', 'id'),
      '#graphic_divider' => new SVG('Mocked graphic_divider content', 'id'),
      '#story_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
      '#story_items' => [
        [
          'video' => FALSE,
          'image' => TRUE,
          'content' => 'Story Item 1',
          'src' => self::STORY_ITEM_1_MEDIA_URI,
          'alt' => 'Alt',
          'title' => 'Title',
          'hide_volume' => FALSE,
        ],
        [
          'video' => FALSE,
          'image' => TRUE,
          'content' => 'Story Item 2',
          'src' => self::STORY_ITEM_2_MEDIA_URI,
          'alt' => 'Alt',
          'title' => 'Title',
          'hide_volume' => FALSE,
        ],
        [
          'video' => FALSE,
          'image' => TRUE,
          'content' => 'Story Item 3',
          'src' => self::STORY_ITEM_3_MEDIA_URI,
          'alt' => 'Alt',
          'title' => 'Title',
          'hide_volume' => FALSE,
        ],
      ],
      '#svg_asset_src_1' => self::SVG_ASSET_1_MEDIA_URI,
      '#svg_asset_alt_1' => 'Alt',
      '#svg_asset_src_2' => self::SVG_ASSET_2_MEDIA_URI,
      '#svg_asset_alt_2' => 'Alt',
      '#svg_asset_src_3' => self::SVG_ASSET_3_MEDIA_URI,
      '#svg_asset_alt_3' => 'Alt',
      '#overlaps_previous' => FALSE,
      '#text_color_override' => FALSE,
    ];

    $this->assertEquals($expected, $build);
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->entityTypeRepositoryMock = $this->createMock(EntityTypeRepository::class);
    $this
      ->entityTypeRepositoryMock
      ->method('getEntityTypeFromClass')
      ->willReturnMap([
        [File::class, 'file'],
        [Media::class, 'media'],
      ]);

    $this->fileStorageMock = $this->createMock(EntityStorageInterface::class);
    $this
      ->fileStorageMock
      ->method('load')
      ->willReturnCallback(function ($id) {
        return $this->internalFileStorage[$id];
      });

    $this->mediaStorageMock = $this->createMock(EntityStorageInterface::class);
    $this
      ->mediaStorageMock
      ->method('load')
      ->willReturnCallback(function ($id) {
        return $this->internalMediaStorage[$id];
      });

    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this
      ->languageHelperMock->method('translate')
      ->will(
        $this->returnCallback(
          function ($arg) {
            return $arg;
          })
      );

    $this->themeConfigurationParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->themeConfigurationParserMock
      ->method('getGraphicDivider')
      ->willReturn(new SVG('Mocked graphic_divider content', 'id'));
    $this->themeConfigurationParserMock
      ->method('getBrandBorder2')
      ->willReturn(new SVG('Mocked brand_borders_2 content', 'id'));

    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this
      ->entityTypeManagerMock
      ->method('getStorage')
      ->willReturnMap([
        ['file', $this->fileStorageMock],
        ['media', $this->mediaStorageMock],
      ]);

    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this
      ->mediaHelperMock
      ->method('getMediaParametersById')
      ->willReturnMap([
        [
          self::STORY_ITEM_1_MEDIA_ID,
          FALSE,
          [
            'image' => TRUE,
            'src' => self::STORY_ITEM_1_MEDIA_URI,
            'alt' => 'Alt',
            'title' => 'Title',
          ],
        ],
        [
          self::STORY_ITEM_2_MEDIA_ID,
          FALSE,
          [
            'image' => TRUE,
            'src' => self::STORY_ITEM_2_MEDIA_URI,
            'alt' => 'Alt',
            'title' => 'Title',
          ],
        ],
        [
          self::STORY_ITEM_3_MEDIA_ID,
          FALSE,
          [
            'image' => TRUE,
            'src' => self::STORY_ITEM_3_MEDIA_URI,
            'alt' => 'Alt',
            'title' => 'Title',
          ],
        ],
        [
          self::SVG_ASSET_1_MEDIA_ID,
          FALSE,
          [
            'image' => TRUE,
            'src' => self::SVG_ASSET_1_MEDIA_URI,
            'alt' => 'Alt',
            'title' => 'Title',
          ],
        ],
        [
          self::SVG_ASSET_2_MEDIA_ID,
          FALSE,
          [
            'image' => TRUE,
            'src' => self::SVG_ASSET_2_MEDIA_URI,
            'alt' => 'Alt',
            'title' => 'Title',
          ],
        ],
        [
          self::SVG_ASSET_3_MEDIA_ID,
          FALSE,
          [
            'image' => TRUE,
            'src' => self::SVG_ASSET_3_MEDIA_URI,
            'alt' => 'Alt',
            'title' => 'Title',
          ],
        ],
      ]);
    $this
      ->mediaHelperMock
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
        [
          self::SVG_ASSET_1_ENTITY_BROWSER_VALUE,
          self::SVG_ASSET_1_MEDIA_ID,
        ],
        [
          self::SVG_ASSET_2_ENTITY_BROWSER_VALUE,
          self::SVG_ASSET_2_MEDIA_ID,
        ],
        [
          self::SVG_ASSET_3_ENTITY_BROWSER_VALUE,
          self::SVG_ASSET_3_MEDIA_ID,
        ],
      ]);
  }

}
