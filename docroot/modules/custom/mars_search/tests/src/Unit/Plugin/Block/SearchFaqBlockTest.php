<?php

namespace Drupal\Tests\mars_search\Unit\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_search\Plugin\Block\SearchFaqBlock;
use Drupal\mars_search\Processors\SearchBuilder;
use Drupal\mars_search\Processors\SearchHelperInterface;
use Drupal\mars_search\SearchProcessFactoryInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\node\Entity\Node;

/**
 * @coversDefaultClass \Drupal\mars_search\Plugin\Block\SearchFaqBlock
 * @group mars
 * @group mars_search
 */
class SearchFaqBlockTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Plugin\Block\SearchFaqBlock
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
   * Search helper.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchHelperInterface
   */
  private $searchHelperMock;

  /**
   * Search builder mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchBuilderInterface
   */
  private $searchBuilderMock;

  /**
   * Config factory.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactory
   */
  private $configFactoryMock;

  /**
   * Immutable config mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ImmutableConfig
   */
  private $immutableConfig;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration;

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
   * Theme configurator parser mock.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
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
    ->willReturn($this->immutableConfig);

    \Drupal::setContainer($this->containerMock);
    $this->configuration = [
      'faq_title' => 'FAQ block title',
    ];
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    $this->searchProcessFactoryMock
      ->expects($this->any())
      ->method('getProcessManager')
      ->willReturnMap([
        [
          'search_helper',
          $this->searchHelperMock,
        ],
        [
          'search_builder',
          $this->searchBuilderMock,
        ],
      ]);

    $this->block = new SearchFaqBlock(
      $this->configuration,
      'list_block',
      $definitions,
      $this->searchProcessFactoryMock,
      $this->configFactoryMock,
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
            'mars_search.search_factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->searchProcessFactoryMock,
          ],
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
            'mars_common.theme_configurator_parser',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->themeConfiguratorParserMock,
          ],
        ]
      );

    $this->block::create(
      $this->containerMock,
      $this->configuration,
      'search_faq_block',
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
    $this->assertArrayHasKey('faq_title', $config_form);
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
    // Mock node context.
    $node = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node
      ->expects($this->once())
      ->method('id');
    $nodeContext = $this->getMockBuilder(Context::class)
      ->disableOriginalConstructor()
      ->getMock();
    $nodeContext->expects($this->once())
      ->method('getContextValue')
      ->willReturn($node);
    $this->block->setContext('node', $nodeContext);

    $this->block->setConfiguration([
      'faq_title' => 'Test FAQ title',
    ]);

    $this->configFactoryMock
      ->expects($this->once())
      ->method('get')
      ->with('mars_search.search_no_results')
      ->willReturn($this->immutableConfig);

    $this->immutableConfig
      ->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        [
          'no_results_heading',
          'test_heading with "@keys"',
        ],
        [
          'no_results_text',
          'test_text',
        ],
      ]);

    $this->searchBuilderMock
      ->expects($this->once())
      ->method('buildSearchResults')
      ->willReturn([
        [
          'limit' => 1,
          'keys' => 'test_search_key',
        ],
        [
          'resultsCount' => 1,
        ],
        [],
      ]);
    $this->searchBuilderMock
      ->expects($this->once())
      ->method('buildFaqFilters')
      ->willReturn([]);

    $this->languageHelperMock
      ->expects($this->exactly(3))
      ->method('translate');

    $build = $this->block->build();
    $this->assertEquals('mars_search_faq_block', $build['#theme']);
    $this->assertEquals('Test FAQ title', $build['#faq_title']);
    $this->assertEquals(1, $build['#search_result_counter']);
    $this->assertArrayHasKey('#no_results_heading', $build);
    $this->assertArrayHasKey('#no_results_text', $build);
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
    $this->searchHelperMock = $this->createMock(SearchHelperInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->immutableConfig = $this->createMock(ImmutableConfig::class);
    $this->searchProcessFactoryMock = $this->createMock(SearchProcessFactoryInterface::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this->searchBuilderMock = $this->createMock(SearchBuilder::class);
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
  }

}
