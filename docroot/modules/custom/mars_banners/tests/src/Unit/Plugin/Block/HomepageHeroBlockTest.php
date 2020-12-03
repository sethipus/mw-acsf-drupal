<?php

namespace Drupal\Tests\mars_banners\Unit\Plugin\Block;

use Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\mars_banners\Plugin\Block\HomepageHeroBlock;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_banners\Plugin\Block\HomepageHeroBlock
 * @group mars
 * @group mars_banners
 */
class HomepageHeroBlockTest extends UnitTestCase {

  private const TEST_CONFIGURATION = [
    'id' => 'homepage_hero_block',
    'label' => 'Homepage Hero block',
    'provider' => 'mars_banners',
    'title' => [
      'url' => '',
      'label' => 'Homepage Hero block',
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
    'card' => [],
  ];

  private const TEST_DEFENITION = [
    'provider' => 'mars_banners',
    'admin_label' => 'admin_label',
  ];

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
   * @var \Drupal\mars_common\MediaHelper|\PHPUnit\Framework\MockObject\MockObject
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
    Drupal::setContainer($this->containerMock);

    $this->homepageBlock = new HomepageHeroBlock(
      self::TEST_CONFIGURATION,
      'homepage_hero_block',
      self::TEST_DEFENITION,
      $this->mediaHelperMock,
      $this->languageHelperMock,
      $this->themeConfiguratorParserMock
    );
  }

  /**
   * Test.
   */
  public function testShouldBuild() {
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
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->languageHelperMock = $this->createLanguageHelperMock();
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
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

}
