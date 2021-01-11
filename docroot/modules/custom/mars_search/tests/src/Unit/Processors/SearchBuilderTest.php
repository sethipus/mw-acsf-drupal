<?php

namespace Drupal\Tests\mars_search\Unit\Processors;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_search\SearchProcessFactoryInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\mars_search\Processors\SearchQueryParserInterface;
use Drupal\mars_search\Processors\SearchHelperInterface;
use Drupal\mars_search\Processors\SearchBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SearchBuilderTest.
 */
class SearchBuilderTest extends UnitTestCase {

  const TEST_QUERY_OPTIONS = [
    'conditions' => [
      ['type', 'faq', '<>', TRUE],
    ],
    'keys' => 'some key',
    'cards_view' => FALSE,
  ];

  /**
   * Config factory.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactory
   */
  private $configFactoryMock;

  /**
   * Entity type manager mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManagerMock;

  /**
   * Menu link tree mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTreeMock;

  /**
   * Theme configurator mock.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  private $themeConfiguratorMock;

  /**
   * Search process factory mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\SearchProcessFactoryInterface
   */
  private $searchProcessFactoryMock;

  /**
   * Container mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface
   */
  private $containerMock;

  /**
   * Search builder.
   *
   * @var \Drupal\mars_search\Processors\SearchBuilderInterface
   */
  private $searchBuilder;

  /**
   * Immutable config mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ImmutableConfig
   */
  private $immutableConfig;

  /**
   * Search query parser.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchQueryParserInterface
   */
  private $searchQueryParserMock;

  /**
   * Search helper mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchHelperInterface
   */
  private $searchHelperMock;

  /**
   * The Request stack mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStackMock;

  /**
   * The Request mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\Request
   */
  private $requestMock;

  /**
   * The Request mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\ParameterBag
   */
  private $parameterBagMock;

  /**
   * The mocked node view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $nodeViewBuilder;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $this->requestStackMock
      ->expects($this->any())
      ->method('getMasterRequest')
      ->willReturn($this->requestMock);

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->with('mars_search.search_no_results')
      ->willReturn($this->immutableConfig);

    $this->searchProcessFactoryMock
      ->expects($this->any())
      ->method('getProcessManager')
      ->willReturnMap(
        [
          [
            'search_query_parser',
            $this->searchQueryParserMock,
          ],
          [
            'search_helper',
            $this->searchHelperMock,
          ],
        ]
      );

    $this->immutableConfig
      ->expects($this->any())
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

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getViewBuilder')
      ->with('node')
      ->willReturn($this->nodeViewBuilder);

    $this->searchBuilder = new SearchBuilder(
      $this->entityTypeManagerMock,
      $this->menuLinkTreeMock,
      $this->themeConfiguratorMock,
      $this->configFactoryMock,
      $this->searchProcessFactoryMock,
      $this->requestStackMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->searchProcessFactoryMock = $this->createMock(SearchProcessFactoryInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->menuLinkTreeMock = $this->createMock(MenuLinkTreeInterface::class);
    $this->themeConfiguratorMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->immutableConfig = $this->createMock(ImmutableConfig::class);
    $this->searchQueryParserMock = $this->createMock(SearchQueryParserInterface::class);
    $this->searchHelperMock = $this->createMock(SearchHelperInterface::class);
    $this->nodeViewBuilder = $this->createMock(EntityViewBuilderInterface::class);
    $this->requestStackMock = $this->createMock(RequestStack::class);
    $this->requestMock = $this->createMock(Request::class);
    $this->parameterBagMock = $this->createMock(ParameterBag::class);
  }

  /**
   * Build no result search.
   */
  public function testGetSearchNoResult() {
    $build = $this->searchBuilder->getSearchNoResult('test', 'grid');
    $this->assertArrayHasKey('#brand_border', $build);
    $this->assertArrayHasKey('#no_results_heading', $build);
    $this->assertArrayHasKey('#no_results_text', $build);
    $this->assertEquals('mars_search_no_results', $build['#theme']);
  }

  /**
   * Build search results.
   */
  public function testBuildSearchResultsWhenMobile() {
    $this->requestMock->query = $this->parameterBagMock;

    $this->parameterBagMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('true');

    $this->searchQueryParserMock
      ->expects($this->any())
      ->method('parseQuery')
      ->willReturn(self::TEST_QUERY_OPTIONS);

    $this->searchQueryParserMock
      ->expects($this->any())
      ->method('parseFilterPreset')
      ->willReturn(self::TEST_QUERY_OPTIONS);

    $fieldMock = $this->getMockBuilder(FieldItemList::class)
      ->disableOriginalConstructor()
      ->getMock();
    $nodeMock = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
    $nodeMock
      ->expects($this->any())
      ->method('bundle')
      ->willReturn('faq');
    $nodeMock
      ->expects($this->any())
      ->method('get')
      ->with('field_qa_item_question')
      ->willReturn($fieldMock);

    $this->searchHelperMock
      ->expects($this->any())
      ->method('getSearchResults')
      ->willReturn([
        'results' => [$nodeMock],
        'resultsCount' => '1',
      ]);

    $this->nodeViewBuilder
      ->expects($this->any())
      ->method('view')
      ->with($nodeMock, 'card')
      ->willReturn([
        '#markup' => '',
      ]);

    $build = $this->searchBuilder->buildSearchResults('grid', [], 'test_grid_id');

    $this->assertEquals([
      [
        'keys' => 'some key',
        'conditions' => [
          0 => [
            0 => 'type',
            1 => 'faq',
            2 => '<>',
            3 => TRUE,
          ],
        ],
        'cards_view' => FALSE,
        'limit' => 4,
      ],
      [
        'resultsCount' => 1,
        'results' => [$nodeMock],
      ],
      [
        '#items' => [
          0 => [
            '#markup' => '',
          ],
        ],
      ],
    ], $build);
  }

}
