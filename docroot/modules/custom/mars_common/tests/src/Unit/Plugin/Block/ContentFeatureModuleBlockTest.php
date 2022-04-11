<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Form\FormState;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_common\Plugin\Block\ContentFeatureModuleBlock;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContentFeatureModuleBlockTest - unit tests for component.
 */
class ContentFeatureModuleBlockTest extends UnitTestCase {

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
   * Media entity storage.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser|\PHPUnit\Framework\MockObject\MockObject
   */
  private $themeConfigurationParserMock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\LanguageHelper
   */
  private $languageHelperMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_media\MediaHelper
   */
  private $mediaHelperMock;

  /**
   * Content feature module block.
   *
   * @var \Drupal\mars_common\Plugin\Block\ContentFeatureModuleBlock
   */
  private $contentFeatureModuleBlock;

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
      'label_display' => FALSE,
      'explore_cta' => 'Explore',
      'title' => '',
    ];
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    // We should create it in test to import different configs.
    $this->contentFeatureModuleBlock = new ContentFeatureModuleBlock(
      $this->configuration,
      'mars_common_content_feature_module',
      $definitions,
      $this->configFactoryMock,
      $this->themeConfigurationParserMock,
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
    $this->themeConfigurationParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
  }

  /**
   * Test method buildConfigurationForm.
   */
  public function testBuildConfigurationForm() {
    $form = [];
    $form_state = (new FormState())->setValues([
      'test' => 'test date',
    ]);
    $build_configuration_form = $this->contentFeatureModuleBlock->buildConfigurationForm($form, $form_state);
    $this->assertArrayHasKey('explore_group', $build_configuration_form);
    $this->assertArrayHasKey('description', $build_configuration_form);
    $this->assertArrayHasKey('background', $build_configuration_form);
    $this->assertArrayHasKey('title', $build_configuration_form);
    $this->assertArrayHasKey('eyebrow', $build_configuration_form);
  }

  /**
   * Test method build.
   */
  public function testBuild() {
    $build = $this->contentFeatureModuleBlock->build();
    $this->assertArrayHasKey('#eyebrow', $build);
    $this->assertArrayHasKey('#title', $build);
    $this->assertArrayHasKey('#description', $build);
    $this->assertArrayHasKey('#explore_cta', $build);
    $this->assertArrayHasKey('#explore_cta_link', $build);
    $this->assertArrayHasKey('#border_radius', $build);
    $this->assertArrayHasKey('#graphic_divider', $build);
    $this->assertArrayHasKey('#theme', $build);
    $this->assertEquals('content_feature_module_block', $build['#theme']);
  }

  /**
   * Test method blockSubmit.
   */
  public function testBlockSubmit() {
    $form = [];
    $form_state = (new FormState())->setValues([
      'title' => 'test title',
      'explore_group' => [
        'explore_cta' => 'cta',
        'explore_cta_link' => 'cta_link',
      ],
    ]);
    $this->contentFeatureModuleBlock->blockSubmit($form, $form_state);
    $this->assertEquals($form_state->getValue('title'), $this->contentFeatureModuleBlock->getConfiguration()['title']);
  }

  /**
   * Test method defaultConfiguration.
   */
  public function testDefaultConfiguration() {
    $explore_cta = 'test data';
    $this->contentFeatureModuleBlock->setConfigurationValue('explore_cta', $explore_cta);
    $default_configuration = $this->contentFeatureModuleBlock->defaultConfiguration();
    $this->assertEquals($explore_cta, $default_configuration['explore_cta']);
  }

}
