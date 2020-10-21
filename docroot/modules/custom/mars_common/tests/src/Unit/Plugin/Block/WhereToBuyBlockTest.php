<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\mars_common\Plugin\Block\WhereToBuyBlock;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_common\Plugin\Block\WhereToBuyBlock
 * @group mars
 * @group mars_common
 */
class WhereToBuyBlockTest extends UnitTestCase {

  private const CONFIGURATION = [
    'id' => 'where_to_buy_block',
    'label' => 'MARS: Where To Buy',
    'provider' => 'mars_common',
    'label_display' => '1',
    'widget_id' => 'test_widget_id',
    'context_mapping' => [],
  ];

  private const DEFINITION = [
    'id' => 'where_to_buy_block',
    'provider' => 'mars_common',
    'admin_label' => 'test',
  ];

  private const PLUGIN_ID = 'where_to_buy_block';

  /**
   * System under test.
   *
   * @var \Drupal\mars_common\Plugin\Block\WhereToBuyBlock
   */
  private $block;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Language\LanguageInterface
   */
  private $languageMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translationMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface
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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ImmutableConfig
   */
  private $immutableConfigMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->block = new WhereToBuyBlock(
      self::CONFIGURATION,
      self::PLUGIN_ID,
      self::DEFINITION,
      $this->configMock,
      $this->languageManagerMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap(
        [
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configMock,
          ],
          [
            'language_manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->languageManagerMock,
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
   * Test.
   */
  public function testShouldBuildConfigurationForm() {
    $form = [];

    $this->containerMock
      ->expects($this->exactly(1))
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

    $form = $this->block->buildConfigurationForm(
      $form,
      $this->formStateMock
    );
    $this->assertIsArray($form['widget_id']);
  }

  /**
   * Test.
   */
  public function testShouldBlockSubmit() {
    $form = [];

    $this->formStateMock
      ->expects($this->once())
      ->method('getValues')
      ->willReturn(self::CONFIGURATION);

    $this->block->blockSubmit(
      $form,
      $this->formStateMock
    );
  }

  /**
   * Test.
   */
  public function testShouldDefaultConfiguration() {
    $default_conf = $this->block->defaultConfiguration();
    $this->assertSame(
      self::CONFIGURATION['widget_id'],
      $default_conf['widget_id']
    );
  }

  /**
   * Test.
   */
  public function testShouldBuild() {
    $this->configMock
      ->expects($this->once())
      ->method('get')
      ->willReturn($this->immutableConfigMock);

    $this->immutableConfigMock
      ->expects($this->once())
      ->method('get')
      ->willReturn('US');

    $this->languageManagerMock
      ->expects($this->once())
      ->method('getCurrentLanguage')
      ->willReturn($this->languageMock);

    $this->languageMock
      ->expects($this->once())
      ->method('getId')
      ->willReturn('en');

    $build = $this->block->build();
    $this->assertIsArray(
      $build['#attached']['html_head']
    );
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->configMock = $this->createMock(ConfigFactoryInterface::class);
    $this->languageManagerMock = $this->createMock(LanguageManagerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->immutableConfigMock = $this->createMock(ImmutableConfig::class);
    $this->languageMock = $this->createMock(LanguageInterface::class);
  }

}
