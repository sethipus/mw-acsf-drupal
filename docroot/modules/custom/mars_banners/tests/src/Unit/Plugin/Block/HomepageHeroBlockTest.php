<?php

namespace Drupal\Tests\mars_banners\Unit\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\mars_banners\Plugin\Block\HomepageHeroBlock;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_banners\Plugin\Block\HomepageHeroBlock
 * @group mars
 * @group mars_banners
 */
class HomepageHeroBlockTest extends UnitTestCase {

  private const TEST_CARD = [
    'eyebrow' => 'test',
    'title' => [
      'label' => 'test',
      'url' => 'test',
    ],
    'cta' => [
      'title' => 'test',
      'url' => 'test',
    ],
    'foreground_image' => 'media:1',
  ];

  private const TEST_CONFIGURATION = [
    'id' => 'homepage_hero_block',
    'label' => 'Homepage Hero block',
    'provider' => 'mars_banners',
    'title' => [
      'url' => '',
      'label' => 'Homepage Hero block',
      'next_line_label' => [
        'value' => 'Homepage Hero block title override',
      ],
    ],
    'block_type' => 'video',
    'label_display' => '0',
    'eyebrow' => 'test eyebrow',
    'cta' => [
      'url' => 'https://test.test',
      'title' => 'Explore',
    ],
    'background_video' => 'background video',
    'background_default' => 'background_default',
    'background_image' => [0 => 'background_default'],
    'card' => [self::TEST_CARD],
    'use_dark_overlay' => TRUE,
    'hide_volume' => FALSE,
  ];

  private const TEST_DEFINITION = [
    'provider' => 'mars_banners',
    'admin_label' => 'admin_label',
  ];

  private const TEST_PLUGIN_ID = 'homepage_hero_block';
  private const TEST_MEDIA_PARAMS = [
    'src' => 'test',
    'title' => 'test',
    'alt' => 'test',
  ];

  private const TEST_FORM = [
    'settings' => [
      'card' => self::TEST_CARD,
    ],
  ];

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
   * System under test.
   *
   * @var \Drupal\mars_banners\Plugin\Block\HomepageHeroBlock
   */
  private $homepageBlock;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translationMock;

  /**
   * Mock.
   *
   * @var \Drupal\mars_media\MediaHelper|\PHPUnit\Framework\MockObject\MockObject
   */
  private $mediaHelperMock;

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

    $this->homepageBlock = new HomepageHeroBlock(
      self::TEST_CONFIGURATION,
      self::TEST_PLUGIN_ID,
      self::TEST_DEFINITION,
      $this->configFactoryMock,
      $this->mediaHelperMock,
      $this->languageHelperMock,
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
            'mars_media.media_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->mediaHelperMock,
          ],
          [
            'mars_common.language_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->languageHelperMock,
          ],
          [
            'mars_common.theme_configurator_parser',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->themeConfiguratorParserMock,
          ],
        ]
      );
    $this->homepageBlock::create(
      $this->containerMock,
      self::TEST_CONFIGURATION,
      self::TEST_PLUGIN_ID,
      self::TEST_DEFINITION
    );
  }

  /**
   * Test.
   */
  public function testShouldBuildWithVideo() {
    $block_build = $this->homepageBlock->build();
    $this->assertSame(
      'Homepage Hero block',
      $block_build['#title_label']
    );
    $this->assertIsArray($block_build);
  }

  /**
   * Test.
   */
  public function testShouldBuildWithImage() {
    $conf = $this->homepageBlock->getConfiguration();
    $conf['block_type'] = 'image';
    $conf['background_image'] = 'media:1';
    $this->homepageBlock->setConfiguration($conf);
    $block_build = $this->homepageBlock->build();
    $this->assertSame(
      'Homepage Hero block',
      $block_build['#title_label']
    );
    $this->assertIsArray($block_build);
  }

  /**
   * Test.
   */
  public function testShouldBuildConfigurationForm() {
    $form_array = [];

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

    $block_form = $this->homepageBlock->buildConfigurationForm(
      $form_array,
      $this->formStateMock
    );
    $this->assertSame(
      'textfield',
      $block_form['eyebrow']['#type']
    );
    $this->assertIsArray($block_form);
  }

  /**
   * Test.
   */
  public function testShouldBlockSubmit() {
    $form_data = [];

    $this->formStateMock
      ->expects($this->once())
      ->method('getValues')
      ->willReturn(self::TEST_CONFIGURATION);

    $this->homepageBlock->blockSubmit(
      $form_data,
      $this->formStateMock
    );
  }

  /**
   * Test.
   */
  public function testShouldAddCardAjaxCallback() {
    $card_container = $this->homepageBlock->ajaxAddCardCallback(self::TEST_FORM, $this->formStateMock);
    $this->assertEquals(self::TEST_CARD, $card_container);
  }

  /**
   * Test.
   */
  public function testShouldRemoveCardAjaxCallback() {
    $card_container = $this->homepageBlock->ajaxRemoveCardCallback(self::TEST_FORM, $this->formStateMock);
    $this->assertEquals(self::TEST_CARD, $card_container);
  }

  /**
   * Test.
   */
  public function testShouldAddCardSubmitted() {
    $this->formStateMock
      ->expects($this->once())
      ->method('get')
      ->with('card_storage')
      ->willReturn([]);
    $this->formStateMock
      ->expects($this->once())
      ->method('set')
      ->with('card_storage', [1]);
    $this->formStateMock
      ->expects($this->once())
      ->method('setRebuild')
      ->with(TRUE);

    $this->homepageBlock->addCardSubmitted(self::TEST_FORM, $this->formStateMock);
  }

  /**
   * Test.
   */
  public function testShouldRemoveCardSubmitted() {
    $triggered_test = [
      "#parents" => ['test', 'test', 0, 'remove_card'],
    ];

    $this->formStateMock
      ->expects($this->once())
      ->method('getTriggeringElement')
      ->willReturn($triggered_test);
    $this->formStateMock
      ->expects($this->once())
      ->method('get')
      ->with('card_storage')
      ->willReturn(['test_1', 'test_2', 'test_3']);
    $this->formStateMock
      ->expects($this->once())
      ->method('set')
      ->with('card_storage', [
        1 => 'test_2',
        2 => 'test_3',
      ]);
    $this->formStateMock
      ->expects($this->once())
      ->method('setRebuild')
      ->with(TRUE);

    $this->homepageBlock->removeCardSubmitted(self::TEST_FORM, $this->formStateMock);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->immutableConfigMock = $this->createMock(ImmutableConfig::class);
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->mediaHelperMock
      ->expects($this->any())
      ->method('getIdFromEntityBrowserSelectValue')
      ->willReturn('media:1');
    $this->mediaHelperMock
      ->expects($this->any())
      ->method('getMediaParametersById')
      ->willReturn(self::TEST_MEDIA_PARAMS);
    $this->languageHelperMock = $this->createLanguageHelperMock();
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->themeConfiguratorParserMock
      ->expects($this->any())
      ->method('getUrlForFile')
      ->willReturn($this->createUrlMock());
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
   * Return Url mock.
   *
   * @return \Drupal\Core\Url|\PHPUnit\Framework\MockObject\MockObject
   *   Url mock.
   */
  private function createUrlMock() {
    $mock = $this->createMock(Url::class);
    $mock
      ->expects($this->any())
      ->method('toUriString')
      ->willReturn('test');

    return $mock;
  }

}

/**
 * HomepageHeroBlock uses file_create_url().
 */
namespace Drupal\mars_banners\Plugin\Block;

if (!function_exists('Drupal\mars_banners\file_create_url')) {

  /**
   * Stub for drupal file_create_url function.
   *
   * @param string $uri
   *   The URI to a file for which we need an external URL, or the path to a
   *   shipped file.
   *
   * @return string
   *   Result.
   */
  function file_create_url($uri) {
    return NULL;
  }

}
