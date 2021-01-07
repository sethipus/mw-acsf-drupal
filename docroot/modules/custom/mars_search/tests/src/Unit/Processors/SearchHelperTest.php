<?php

namespace Drupal\Tests\mars_search\Unit\Processors;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\search_api\Item\Field;
use Drupal\search_api\Item\Item;
use Drupal\search_api\Query\QueryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Url;
use Drupal\mars_search\Processors\SearchHelper;
use Drupal\mars_search\Processors\SearchTermFacetProcess;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\ResultSet;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * SearchHelperTest class.
 */
class SearchHelperTest extends UnitTestCase {

  /**
   * Search helper mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchHelperInterface
   */
  private $searchHelper;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchTermFacetProcess
   */
  private $searchTermFacetProcessMock;

  /**
   * Entity type manager mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Logger\LoggerChannelInterface
   */
  private $loggerChannelMock;

  /**
   * Request stack mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStackMock;

  /**
   * Container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Url mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Url
   */
  private $urlMock;

  /**
   * A test index for use in these tests.
   *
   * @var \Drupal\search_api\IndexInterface|null
   */
  protected $index;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityStorageInterface
   */
  private $entityStorageMock;

  /**
   * The entity query object.
   *
   * @var \Drupal\search_api\Query\QueryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $query;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    $this->container = new ContainerBuilder();
    $this->searchHelper = new SearchHelper(
      $this->entityTypeManagerMock,
      $this->loggerFactoryMock,
      $this->requestStackMock,
      $this->searchTermFacetProcessMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->searchHelperMock = $this->createMock(SearchHelper::class);
    $this->loggerFactoryMock = $this->createMock(LoggerChannelFactoryInterface::class);
    $this->searchTermFacetProcessMock = $this->createMock(SearchTermFacetProcess::class);
    $this->loggerChannelMock = $this->createMock(LoggerChannelInterface::class);
    $this->requestStackMock = $this->createMock(RequestStack::class);
    $this->urlMock = $this->createMock(Url::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->query = $this->createMock(QueryInterface::class);
    $this->loggerFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->loggerChannelMock);
    $masterRequest = Request::create('/foo');
    $this->requestStackMock
      ->expects($this->any())
      ->method('getMasterRequest')
      ->willReturn($masterRequest);
  }

  /**
   * Get search results test.
   */
  public function testGetSearchResults() {

    $index = $this->createMock(Index::class);

    $this->entityStorageMock->method('load')->willReturnMap([
      ['acquia_search_index', $index],
    ]);
    $this->entityTypeManagerMock->method('getStorage')->willReturnMap([
      ['search_api_index', $this->entityStorageMock],
    ]);

    $this->container->set('entity_type.manager', $this->entityTypeManagerMock);
    \Drupal::setContainer($this->container);
    $this->query = $this->createMock(QueryInterface::class);

    $index->expects($this->any())
      ->method('query')
      ->with([])
      ->will($this->returnValue($this->query));

    $this->query->expects($this->once())
      ->method('range')
      ->with(0, 4)
      ->will($this->returnValue($this->query));

    $results = $this->createMock(ResultSet::class);

    $item = $this->createTestItem($index);

    $results->expects($this->any())
      ->method('getResultItems')
      ->willReturn([$item]);

    $results->expects($this->any())
      ->method('getExtraData')
      ->willReturn([
        '1' => [
          '1' => [
            'filter' => 'test',
          ],
        ],
      ]);

    $this->query->expects($this->once())
      ->method('execute')
      ->willReturn($results);

    $search_results = $this->searchHelper->getSearchResults([], 'searcher_default');

    $this->assertArrayEquals([
      'facets' => [
        '1' => [
          '1' => [
            'filter' => 'test',
          ],
        ],
      ],
      'highlighted_fields' => [],
      'results' => [],
      'resultsCount' => NULL,
    ], $search_results);
  }

  /**
   * Get current url test.
   */
  public function testGetCurrentUrl() {
    $router = $this->createMock('Drupal\Tests\Core\Routing\TestRouterInterface');
    $router->expects($this->any())
      ->method('matchRequest')
      ->with(Request::create('/foo'))
      ->willReturn([
        '_raw_variables' => new ParameterBag([]),
        '_route' => 'test',
      ]);

    $this->container->set('router.no_access_checks', $router);
    \Drupal::setContainer($this->container);

    $current_url = $this->searchHelper->getCurrentUrl();
    $this->assertInstanceOf(Url::class, $current_url);
  }

  /**
   * Get facet keys test.
   */
  public function testGetFacetKeys() {
    $facet_keys = $this->searchHelper->getFacetKeys();
    $facet_keys_expected_result = [
      'type',
      'faq_filter_topic',
      'mars_flavor',
      'mars_format',
      'mars_diet_allergens',
      'mars_occasions',
      'mars_brand_initiatives',
    ];
    $this->assertArrayEquals($facet_keys_expected_result, $facet_keys);
  }

  /**
   * Get manager id test.
   */
  public function testGetManagerId() {
    $manager_id = $this->searchHelper->getManagerId();
    $this->assertEquals('search_helper', $manager_id);
  }

  /**
   * Creates an item for testing purposes.
   *
   * @return \Drupal\search_api\Item\ItemInterface
   *   A test item.
   */
  protected function createTestItem($index) {
    $item = new Item($index, 'id');

    $item->setBoost(2);
    $item->setExcerpt('Foo bar baz');
    $item->setExtraData('foo', (object) ['bar' => 1]);
    $item->setExtraData('test', 1);
    $item->setLanguage('en');
    $item->setScore(4);
    $item->setFields([
      'test' => $this->createTestField($index),
      'foo' => $this->createTestField($index, 'foo', 'entity:entity_test_mulrev_changed'),
    ]);
    $item->setFieldsExtracted(TRUE);

    return $item;
  }

  /**
   * Creates a field for testing purposes.
   *
   * @param object $index
   *   Index object.
   * @param string $id
   *   (optional) The field ID (and property path).
   * @param string|null $datasource_id
   *   (optional) The field's datasource ID.
   *
   * @return \Drupal\search_api\Item\FieldInterface
   *   A test field.
   */
  protected function createTestField($index, $id = 'test', $datasource_id = NULL) {

    $field = new Field($index, $id);
    $field->setDatasourceId($datasource_id);
    $field->setPropertyPath($id);
    $field->setLabel('Foo');
    $field->setDescription('Bar');
    $field->setType('float');
    $field->setBoost(2);
    $field->setIndexedLocked();
    $field->setConfiguration([
      'foo' => 'bar',
      'test' => TRUE,
    ]);
    $field->setValues([1, 3, 5]);

    return $field;
  }

}
