<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\Plugin\Block\FreeformStoryBlock;
use Drupal\mars_common\ThemeConfiguratorParser;

/**
 * Class FreeformStoryBlockTest - unit tests.
 *
 * @package Drupal\Tests\mars_common\Unit
 * @covers \Drupal\mars_common\Plugin\Block\FreeformStoryBlock
 */
class FreeformStoryBlockTest extends UnitTestCase {

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
   * Tested FreeformStoryBlock.
   *
   * @var \Drupal\mars_common\Plugin\Block\FreeformStoryBlock
   */
  private $freeformStoryBlock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration;

  /**
   * Entity type manager mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManagerMock;

  /**
   * ThemeConfiguratorParserMock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject||\Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParserMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\LanguageHelper
   */
  private $languageHelperMock;

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

    $this->configFactoryMock
    ->method('getEditable')
    ->with('mars_common.character_limit_page')
    ->willReturn($this->immutableConfigMock);

    \Drupal::setContainer($this->containerMock);
    $this->configuration = [
      'provider' => 'mars_common',
      'block_aligned' => 'left',
      'header_1' => 'Header 1',
      'header_2' => 'Header 2',
      'body' => [
        'value'  => '<p>Description</p>\r\n',
        'format'  => 'rich_text',
      ],
      'background_shape' => 1,
      'image' => '',
      'image_alt' => 'Test',
      'custom_background_color' => 'ffffa8',
      'use_custom_color' => '1',
      'custom_background_color' => '1',
    ];
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    // We should create it in test to import different configs.
    $this->freeformStoryBlock = new FreeformStoryBlock(
      $this->configuration,
      'freeform_story_block',
      $definitions,
      $this->configFactoryMock,
      $this->entityTypeManagerMock,
      $this->themeConfiguratorParserMock,
      $this->languageHelperMock,
      $this->mediaHelperMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->immutableConfigMock = $this->createMock(ImmutableConfig::class);
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
  }

  /**
   * Test configuration form.
   */
  public function testBuildConfigurationFormProperly() {
    $config_form = $this->freeformStoryBlock->buildConfigurationForm([], $this->formStateMock);
    $this->assertCount(15, $config_form);
    $this->assertArrayHasKey('block_aligned', $config_form);
    $this->assertArrayHasKey('header_1', $config_form);
    $this->assertArrayHasKey('header_2', $config_form);
    $this->assertArrayHasKey('body', $config_form);
    $this->assertArrayHasKey('background_shape', $config_form);
    $this->assertArrayHasKey('override_text_color', $config_form);
    $this->assertArrayHasKey('image', $config_form);
  }

  /**
   * Test building block.
   */
  public function testBuildBlockRenderArrayProperly() {
    $build = $this->freeformStoryBlock->build();

    $this->assertCount(11, $build);
    $this->assertArrayNotHasKey('#image', $build);
    $this->assertArrayHasKey('#text_color_override', $build);
    $this->assertEquals('freeform_story_block', $build['#theme']);
  }

}
