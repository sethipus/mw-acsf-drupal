<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_common\Plugin\Block\ParentPageHeaderBlock;
use Drupal\mars_media\SVG\SVG;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\ThemeConfiguratorParser;

/**
 * @coversDefaultClass \Drupal\mars_common\Plugin\Block\ParentPageHeaderBlock
 * @group mars
 * @group mars_common
 */
class ParentPageHeaderBlockTest extends UnitTestCase {

  private const CONFIGURATION = [
    'id' => 'parent_page_header',
    'provider' => 'mars_common',
    'eyebrow' => 'Eyebrow',
    'title' => 'Title',
    'background_options' => ParentPageHeaderBlock::KEY_OPTION_DEFAULT,
    'description' => 'Description',
  ];

  private const DEFINITION = [
    'id' => 'parent_page_header',
    'provider' => 'mars_common',
    'admin_label' => 'test',
  ];

  private const PLUGIN_ID = 'parent_page_header';
  private const MEDIA_SOURCE = 'test_source';
  private const MEDIA_FORMAT = 'test_format';
  private const IMAGE_MEDIA_ID = 'media:1';
  private const VIDEO_MEDIA_ID = 'media:2';

  private const IMAGE_PARAMS = [
    'src' => self::MEDIA_SOURCE,
  ];

  private const VIDEO_PARAMS = [
    'src' => self::MEDIA_SOURCE,
    'format' => self::MEDIA_FORMAT,
    'video' => TRUE,
  ];

  /**
   * System under test.
   *
   * @var \Drupal\mars_common\Plugin\Block\ParentPageHeaderBlock
   */
  private $block;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_media\MediaHelper
   */
  private $mediaHelperMock;

  /**
   * Config factory mock.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactoryMock;

  /**
   * Immutable config mock.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $immutableConfigMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\LanguageHelper
   */
  private $languageHelperMock;

  /**
   * Mock.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser|\PHPUnit\Framework\MockObject\MockObject
   */
  private $themeConfiguratorParserMock;

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
    $this->block = new ParentPageHeaderBlock(
      self::CONFIGURATION,
      self::PLUGIN_ID,
      self::DEFINITION,
      $this->configFactoryMock,
      $this->languageHelperMock,
      $this->mediaHelperMock,
      $this->themeConfiguratorParserMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(4))
      ->method('get')
      ->willReturnMap(
        [
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
          [
            'mars_common.theme_configurator_parser',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->themeConfiguratorParserMock,
          ],
        ]
      );
    $this->block::create(
      $this->containerMock,
      self::CONFIGURATION,
      self::PLUGIN_ID,
      self::DEFINITION
    );
  }

  /**
   * Test configuration form.
   */
  public function testBuildConfigurationFormProperly() {
    $config_form = $this->block->buildConfigurationForm([], $this->formStateMock);
    $this->assertCount(16, $config_form);
    $this->assertArrayHasKey('title', $config_form);
    $this->assertArrayHasKey('eyebrow', $config_form);
    $this->assertArrayHasKey('background_options', $config_form);
    $this->assertArrayHasKey('description', $config_form);
  }

  /**
   * Test submitting block.
   */
  public function testShouldBlockSubmit() {
    $form_data = [];

    $this->formStateMock
      ->expects($this->exactly(12))
      ->method('getValue')
      ->willReturn('');

    $this->block->blockSubmit(
      $form_data,
      $this->formStateMock
    );
  }

  /**
   * Test building block when image was provided.
   */
  public function testShouldBuildProperlyWithImage() {
    $conf = $this->block->getConfiguration();
    $conf['background_options'] = ParentPageHeaderBlock::KEY_OPTION_IMAGE;
    $conf['background_image'] = self::IMAGE_MEDIA_ID;
    $this->block->setConfiguration($conf);
    $build = $this->block->build();

    $this->assertCount(10, $build);
    $this->assertArrayHasKey('#eyebrow', $build);
    $this->assertArrayHasKey('#description', $build);
    $this->assertArrayHasKey('#brand_shape', $build);
    $this->assertArrayHasKey('#label', $build);
    // Expecting image specific keys in the build array.
    $this->assertEquals('image', $build['#media_type']);

    foreach (['desktop', 'tablet', 'mobile'] as $resolution) {
      $this->assertArrayHasKey($resolution, $build['#background']);
      $this->assertEquals(self::MEDIA_SOURCE, $build['#background'][$resolution]['src']);
    }

    $this->assertEquals('parent_page_header_block', $build['#theme']);
  }

  /**
   * Test building block when image was provided.
   */
  public function testShouldBuildProperlyWithVideo() {
    $conf = $this->block->getConfiguration();
    $conf['background_options'] = ParentPageHeaderBlock::KEY_OPTION_VIDEO;
    $conf['background_video'] = self::VIDEO_MEDIA_ID;
    $conf['hide_volume'] = FALSE;
    $this->block->setConfiguration($conf);
    $build = $this->block->build();

    $this->assertCount(12, $build);
    $this->assertArrayHasKey('#eyebrow', $build);
    $this->assertArrayHasKey('#description', $build);
    $this->assertArrayHasKey('#brand_shape', $build);
    $this->assertArrayHasKey('#label', $build);
    // Expecting video specific keys in the build array.
    $this->assertEquals('video', $build['#media_type']);
    $this->assertEquals(self::MEDIA_SOURCE, $build['#background']['video']['src']);
    $this->assertEquals(self::MEDIA_FORMAT, $build['#media_format']);

    $this->assertEquals('parent_page_header_block', $build['#theme']);
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->immutableConfigMock = $this->createMock(ImmutableConfig::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this->languageHelperMock->method('translate')
      ->will(
        $this->returnCallback(
          function ($arg) {
            return $arg;
          })
      );

    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->themeConfiguratorParserMock
      ->method('getBrandShapeWithoutFill')
      ->willReturn(new SVG('<svg xmlns="http://www.w3.org/2000/svg" />', 'id'));

    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->mediaHelperMock
      ->expects($this->any())
      ->method('getMediaParametersById')
      ->willReturnMap([
        [self::IMAGE_MEDIA_ID, FALSE, self::IMAGE_PARAMS],
        [self::VIDEO_MEDIA_ID, FALSE, self::VIDEO_PARAMS],
      ]);
    $this->mediaHelperMock
      ->method('getIdFromEntityBrowserSelectValue')
      ->will(
        $this->returnCallback(
          function ($arg) {
            return $arg;
          })
      );
  }

}
