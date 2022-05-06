<?php

namespace Drupal\Tests\mars_search\Unit\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_search\Plugin\Block\SearchHeaderBlock;
use Drupal\mars_search\Processors\SearchBuilder;
use Drupal\mars_search\SearchProcessFactoryInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_search\Plugin\Block\SearchHeaderBlock
 * @group mars
 * @group mars_search
 */
class SearchHeaderBlockTest extends UnitTestCase {

  const BLOCK_CONFIGURATION = [
    'search_header_heading' => 'Test header title',
    'search_header_placeholder' => 'Test header placeholder',
  ];

  /**
   * System under test.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Plugin\Block\SearchHeaderBlock
   */
  private $block;

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
   * Search builder mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchBuilderInterface
   */
  private $searchBuilderMock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration;

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
   * Search process factory mock.
   *
   * @var \Drupal\mars_search\SearchProcessFactoryInterface
   */
  private $searchProcessFactoryMock;

  /**
   * Language helper mock.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelperMock;

  /**
   * Theme configurator mock.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  private $themeConfiguratorMock;

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
    $this->configuration = self::BLOCK_CONFIGURATION;
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    $this->searchProcessFactoryMock
      ->expects($this->any())
      ->method('getProcessManager')
      ->willReturnMap([
        [
          'search_builder',
          $this->searchBuilderMock,
        ],
      ]);

    $this->block = new SearchHeaderBlock(
      $this->configuration,
      'search_header_block',
      $definitions,
      $this->configFactoryMock,
      $this->themeConfiguratorMock,
      $this->searchProcessFactoryMock,
      $this->languageHelperMock
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
            'mars_common.theme_configurator_parser',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->themeConfiguratorMock,
          ],
          [
            'mars_search.search_factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->searchProcessFactoryMock,
          ],
          [
            'mars_common.language_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->languageHelperMock,
          ],
        ]
      );

    $this->block::create(
      $this->containerMock,
      $this->configuration,
      'search_header_block',
      [
        'provider'    => 'test',
        'admin_label' => 'test',
      ],
    );
  }

  /**
   * Test configuration form.
   */
  public function testShouldBuildConfigurationForm() {
    $config_form = $this->block->buildConfigurationForm([], $this->formStateMock);
    $this->assertArrayHasKey('search_header_heading', $config_form);
    $this->assertArrayHasKey('search_header_placeholder', $config_form);
  }

  /**
   * Test configuration form submit.
   */
  public function testShouldBlockSubmit() {
    $form_data = [];

    $this->formStateMock
      ->expects($this->once())
      ->method('getValues')
      ->willReturn([]);

    $this->block->blockSubmit(
      $form_data,
      $this->formStateMock
    );
  }

  /**
   * Test block build.
   */
  public function testBuild() {
    $this->searchBuilderMock
      ->expects($this->once())
      ->method('buildSearchHeader')
      ->with($this->block->getConfiguration());

    $this->themeConfiguratorMock
      ->expects($this->once())
      ->method('getBrandBorder');

    $build = $this->block->build();
    $this->assertEquals('mars_search_header', $build['#theme']);
    $this->assertEquals('Test header title', $build['#search_header_heading']);
  }

  /**
   * Test max age.
   */
  public function testMaxAge() {
    $this->assertEquals(0, $this->block->getCacheMaxAge());
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->immutableConfigMock = $this->createMock(ImmutableConfig::class);
    $this->searchProcessFactoryMock = $this->createMock(SearchProcessFactoryInterface::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this->searchBuilderMock = $this->createMock(SearchBuilder::class);
    $this->themeConfiguratorMock = $this->createMock(ThemeConfiguratorParser::class);
  }

}
