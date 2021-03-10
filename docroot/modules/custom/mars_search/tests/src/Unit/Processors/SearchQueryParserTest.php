<?php

namespace Drupal\Tests\mars_search\Unit\Processors;

use Drupal\mars_search\Processors\SearchQueryParser;
use Drupal\mars_search\Processors\SearchQueryParserInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SearchQueryParserTest.
 */
class SearchQueryParserTest extends UnitTestCase {

  /**
   * Search helper mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchQueryParser
   */
  private $searchQueryParser;

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
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    $this->container = new ContainerBuilder();
    $this->searchQueryParser = new SearchQueryParser(
      $this->requestStackMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->requestStackMock = $this->createMock(RequestStack::class);
    $masterRequest = Request::create('/foo');
    $this->requestStackMock
      ->expects($this->any())
      ->method('getMasterRequest')
      ->willReturn($masterRequest);
  }

  /**
   * Get manager id test.
   */
  public function testGetManagerId() {
    $manager_id = $this->searchQueryParser->getManagerId();
    $this->assertEquals('search_query_parser', $manager_id);
  }

  /**
   * Parse query test.
   */
  public function testParseQuery() {
    $filter = $this->searchQueryParser->parseQuery();
    $expected = [
      'conditions' => [
        0 => [
          0 => 'type',
          1 => 'faq',
          2 => '<>',
          3 => TRUE,
        ],
      ],
      'keys' => '',
      'limit' => 8,
      'offset' => 0,
      'options_logic' => 'AND',
      'sort' => [
        'bundle_weight' => 'ASC',
        'title' => 'ASC',
      ],
    ];
    $this->assertArrayEquals($expected, $filter);
  }

  /**
   * Parser filter preset test.
   */
  public function testParseFilterPreset() {
    $grid_id = SearchQueryParserInterface::MARS_SEARCH_DEFAULT_SEARCH_ID;
    $searchOptions = $this->searchQueryParser->parseQuery($grid_id);
    $config = [
      'title' => 'Test grid title',
      'grid_id' => 'test_grid_id',
      'exposed_filters_wrapper' => [
        'toggle_filters' => [],
      ],
      'general_filters' => [
        'vocabulary1' => [
          'select' => 'term1',
        ],
        'vocabulary2' => [
          'select' => 'term2',
        ],
        'options_logic' => 'OR',
      ],
    ];
    $resultSearchOptions = $this->searchQueryParser->parseFilterPreset($searchOptions, $config);

    $expected = [
      'conditions' => [
        0 => [
          0 => 'type',
          1 => 'faq',
          2 => '<>',
          3 => TRUE,
        ],
        1 => [
          0 => 'vocabulary1',
          1 => 'term1',
          2 => 'IN',
        ],
        2 => [
          0 => 'vocabulary2',
          1 => 'term2',
          2 => 'IN',
        ],
      ],
      'keys' => '',
      'limit' => 8,
      'offset' => 0,
      'options_logic' => 'OR',
      'sort' => [
        'bundle_weight' => 'ASC',
        'title' => 'ASC',
      ],
    ];

    $this->assertArrayEquals($expected, $resultSearchOptions);
  }

}
