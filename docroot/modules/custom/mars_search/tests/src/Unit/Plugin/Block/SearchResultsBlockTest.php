<?php

namespace Drupal\Tests\mars_search\Unit\Plugin\Block;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_search\Plugin\Block\SearchResultsBlock;
use Drupal\mars_search\Processors\SearchBuilder;
use Drupal\mars_search\Processors\SearchHelperInterface;
use Drupal\mars_search\SearchProcessFactoryInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\node\Entity\Node;

/**
 * @coversDefaultClass \Drupal\mars_search\Plugin\Block\SearchResultsBlock
 * @group mars
 * @group mars_search
 */
class SearchResultsBlockTest extends UnitTestCase {

  const BLOCK_CONFIGURATION = [
    'search_header_heading' => 'Test header title',
    'search_header_placeholder' => 'Test header placeholder',
  ];

  /**
   * System under test.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Plugin\Block\SearchResultsBlock
   */
  private $block;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface
   */
  private $containerMock;

  /**
   * Search builder mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchBuilderInterface
   */
  private $searchBuilderMock;

  /**
   * Search helper mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchHelperInterface
   */
  private $searchHelperMock;

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
   * Theme configurator mock.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  private $themeConfiguratorMock;

  /**
   * Translation mock.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translationMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
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
        [
          'search_helper',
          $this->searchBuilderMock,
        ],
      ]);

    $this->block = new SearchResultsBlock(
      $this->configuration,
      'search_results_block',
      $definitions,
      $this->searchProcessFactoryMock,
      $this->themeConfiguratorMock,
      $this->languageHelperMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(3))
      ->method('get')
      ->willReturnMap(
        [
          [
            'mars_search.search_factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->searchProcessFactoryMock,
          ],
          [
            'mars_common.theme_configurator_parser',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->themeConfiguratorMock,
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
      'search_results_block',
      [
        'provider'    => 'test',
        'admin_label' => 'test',
      ],
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

    $this->searchBuilderMock
      ->expects($this->once())
      ->method('buildSearchResults')
      ->willReturn([
        [
          'limit' => 1,
          'keys' => 'test_results_key',
        ],
        [
          'resultsCount' => 1,
        ],
        [
          '#items' => [],
        ],
      ]);
    $this->searchBuilderMock
      ->expects($this->once())
      ->method('buildSearchFacets')
      ->willReturn([]);

    $this->themeConfiguratorMock
      ->expects($this->once())
      ->method('getGraphicDivider');

    $this->languageHelperMock
      ->expects($this->exactly(2))
      ->method('translate')
      ->willReturn('Results for: ');

    $build = $this->block->build();
    $this->assertEquals('mars_search_search_results_block', $build['#theme']);
    $this->assertEquals('Results for: test_results_key', $build['#results_key_header']);
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
    $this->searchProcessFactoryMock = $this->createMock(SearchProcessFactoryInterface::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this->searchBuilderMock = $this->createMock(SearchBuilder::class);
    $this->searchHelperMock = $this->createMock(SearchHelperInterface::class);
    $this->themeConfiguratorMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
  }

}
