<?php

namespace Drupal\Tests\mars_search\Unit\Processors;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\mars_search\Processors\SearchHelper;
use Drupal\mars_search\Processors\SearchQueryParser;
use Drupal\mars_search\Processors\SearchQueryParserInterface;
use Drupal\mars_search\Processors\SearchTermFacetProcess;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class SearchTermFacetProcessTest.
 */
class SearchTermFacetProcessTest extends UnitTestCase {

  /**
   * List of vocabularies which are included in indexing.
   *
   * @var array
   */
  const TAXONOMY_VOCABULARIES = [
    'mars_brand_initiatives' => [
      'label' => 'BRAND INITIATIVES',
      'content_types' => ['article', 'recipe', 'landing_page', 'campaign'],
    ],
    'mars_occasions' => [
      'label' => 'OCCASIONS',
      'content_types' => [
        'article', 'recipe', 'product', 'landing_page', 'campaign',
      ],
    ],
    'mars_flavor' => [
      'label' => 'FLAVOR',
      'content_types' => ['product'],
    ],
    'mars_category' => [
      'label' => 'CATEGORY',
      'content_types' => ['product'],
    ],
    'mars_format' => [
      'label' => 'FORMAT',
      'content_types' => ['product'],
    ],
    'mars_diet_allergens' => [
      'label' => 'DIET & ALLERGENS',
      'content_types' => ['product'],
    ],
    'mars_trade_item_description' => [
      'label' => 'TRADE ITEM DESCRIPTION',
      'content_types' => ['product'],
    ],
  ];

  /**
   * Search key which is using in URL.
   */
  const MARS_SEARCH_SEARCH_KEY = 'search';

  /**
   * Facet search query id.
   *
   * @var string
   */
  const SEARCH_FACET_QUERY_ID = 'main_search_facets';

  /**
   * Container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Request stack mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStackMock;

  /**
   * Search term facet process.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchTermFacetProcess
   */
  protected $searchTermFacetProcess;

  /**
   * Entity type manager mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManagerMock;

  /**
   * Request mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\Request
   */
  private $requestMock;

  /**
   * Parameter bag mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\ParameterBag
   */
  private $parameterBagMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    $this->container = new ContainerBuilder();
    $this->searchTermFacetProcess = new SearchTermFacetProcess(
      $this->entityTypeManagerMock,
      $this->requestStackMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->requestStackMock = $this->createMock(RequestStack::class);
    $this->requestMock = $this->createMock(Request::class);
    $this->requestMock->query = $this->createMock(ParameterBag::class);
    $this->requestStackMock
      ->expects($this->any())
      ->method('getMasterRequest')
      ->willReturn($this->requestMock);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManager::class);

  }

  /**
   * Manager id test.
   */
  public function testGetManagerId() {
    $manager_id = $this->searchTermFacetProcess->getManagerId();
    $this->assertEquals('search_facet_process', $manager_id);
  }

  /**
   * Process filter test.
   */
  public function testProcessFilter() {
    $grid_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID;
    $searchQueryParser = $this->createMock(SearchQueryParser::class);
    $searchQueryParser->expects($this->any())
      ->method('parseQuery')
      ->willReturn([
        'conditions' => [
          ['type', 'faq', '<>', TRUE],
        ],
        'keys' => 'mars_flavor',
        'cards_view' => FALSE,
      ]);
    $facetOptions = $searchQueryParser->parseQuery($grid_id);

    $facet_id = static::SEARCH_FACET_QUERY_ID;
    $searchHelperMock = $this->createMock(SearchHelper::class);
    $searchHelperMock->expects($this->any())
      ->method('getSearchResults')
      ->willReturn([
        'facets' => [
          'mars_flavor' => [
            0 => [
              'filter' => 'mars_flavor',
            ],
          ],
          'mars_brand_initiatives' => [
            0 => [
              'filter' => 'mars_brand_initiatives',
            ],
          ],
        ],
        'resultsCount' => '1',
      ]);
    $facets_query = $searchHelperMock->getSearchResults($facetOptions, $facet_id);

    $storage = $this->getMockBuilder(TermStorageInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $term = $this->getMockBuilder(TermInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $term->expects($this->any())
      ->method('id')
      ->willReturn(1);
    $term->expects($this->any())
      ->method('label')
      ->willReturn('Test term');

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->with('taxonomy_term')
      ->willReturn($storage);
    $storage
      ->expects($this->any())
      ->method('loadMultiple')
      ->willReturn([
        'mars_flavor' => $term,
      ]);

    $process_filter = $this->searchTermFacetProcess->processFilter($facets_query['facets'], static::TAXONOMY_VOCABULARIES, $grid_id);
    $expected_result = [
      0 => [],
      1 => [
        0 => [
          'filter_title' => 'FLAVOR',
          'filter_id' => 'mars_flavor',
          'active_filters_count' => 0,
          'checkboxes' => [
            0 => [
              'title' => 'Test term',
              'key' => '1mars_flavor',
            ],
          ],
        ],
      ],
    ];
    $this->assertArrayEquals($expected_result, $process_filter);
  }

  /**
   * Has query key test.
   */
  public function testHasQueryKey() {
    $has_query_key = $this->searchTermFacetProcess->hasQueryKey('test');
    $expected = FALSE;
    $this->assertEquals($expected, $has_query_key);
  }

  /**
   * Get query value test.
   */
  public function testGetQueryValue() {
    $grid_id = static::MARS_SEARCH_SEARCH_KEY;
    $this->requestMock->query
      ->expects($this->any())
      ->method('get')
      ->with('test')
      ->willReturn([
        'search' => [
          'result' => 'test',
        ],
      ]);

    $has_query_key = $this->searchTermFacetProcess->getQueryValue('test', $grid_id);
    $expected = [
      'result' => 'test',
    ];
    $this->assertArrayEquals($expected, $has_query_key);
  }

}
