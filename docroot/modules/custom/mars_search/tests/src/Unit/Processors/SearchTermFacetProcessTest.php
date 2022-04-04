<?php

namespace Drupal\Tests\mars_search\Unit\Processors;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\mars_common\Form\MarsSiteLabelsForm;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_search\Processors\SearchHelper;
use Drupal\mars_search\Processors\SearchQueryParser;
use Drupal\mars_search\Processors\SearchQueryParserInterface;
use Drupal\mars_search\Processors\SearchTermFacetProcess;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\VocabularyInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ParameterBag;
use Drupal\mars_search\Processors\SearchCategoriesInterface;

/**
 * Class SearchTermFacetProcessTest - unit tests.
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
   * Config factory mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactoryMock;

  /**
   * Search categories mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchCategoriesInterface
   */
  private $searchCategoriesMock;

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
   * Language helper service mock.
   *
   * @var \Drupal\mars_common\LanguageHelper|\PHPUnit\Framework\MockObject\MockObject
   */
  private $languageHelperMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    $this->container = new ContainerBuilder();
    $this->searchTermFacetProcess = new SearchTermFacetProcess(
      $this->entityTypeManagerMock,
      $this->requestStackMock,
      $this->searchCategoriesMock,
      $this->languageHelperMock,
      $this->configFactoryMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->requestStackMock = $this->createMock(RequestStack::class);
    $this->requestMock = $this->createMock(Request::class);
    $this->requestMock->query = $this->createMock(ParameterBag::class);
    $this->searchCategoriesMock = $this->createMock(SearchCategoriesInterface::class);
    $this->requestStackMock
      ->expects($this->any())
      ->method('getMasterRequest')
      ->willReturn($this->requestMock);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManager::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
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

    $storage = $this->getMockBuilder(EntityStorageInterface::class)
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
      ->willReturn($storage);

    $vocabulary = $this->createMock(VocabularyInterface::class);
    $storage
      ->expects($this->any())
      ->method('loadMultiple')
      ->willReturnMap(
        [
          [
            [],
            ['mars_flavor' => $term],
          ],
          [
            [
              'mars_brand_initiatives',
              'mars_occasions',
              'mars_flavor',
              'mars_category',
              'mars_format',
              'mars_diet_allergens',
              'mars_trade_item_description',
            ],
            ['mars_flavor' => $vocabulary],
          ],
        ]
      );

    $field = $this->createMock(FieldItemListInterface::class);
    $term
      ->expects($this->any())
      ->method('get')
      ->willReturn($field);

    $vocabulary
      ->expects($this->once())
      ->method('get')
      ->willReturn($field);

    $siteLabelMock = $this
      ->getMockBuilder(MarsSiteLabelsForm::class)
      ->addMethods(['get'])
      ->disableOriginalConstructor()
      ->getMock();

    $this->configFactoryMock
      ->method('get')
      ->with('mars_common.site_labels')
      ->willReturn($siteLabelMock);

    $siteLabelMock
      ->method('get')
      ->with('mars_category')
      ->willReturn('range');

    $process_filter = $this->searchTermFacetProcess->processFilter($facets_query['facets'], static::TAXONOMY_VOCABULARIES, $grid_id);
    $this->assertCount(2, $process_filter);
    $this->arrayHasKey('weight', $process_filter[1]);
    $this->assertEquals('FLAVOR', $process_filter[1][0]['filter_title']);
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
    $this->assertEquals($expected, $has_query_key);
  }

}
