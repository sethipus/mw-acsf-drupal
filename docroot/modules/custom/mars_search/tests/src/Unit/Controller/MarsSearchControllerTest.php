<?php

namespace Drupal\Tests\mars_search\Unit\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_search\Controller\MarsSearchController;
use Drupal\mars_search\Processors\SearchBuilder;
use Drupal\mars_search\Processors\SearchHelperInterface;
use Drupal\mars_search\Processors\SearchPrettyFacetProcessInterface;
use Drupal\mars_search\Processors\SearchQueryParserInterface;
use Drupal\mars_search\SearchProcessFactoryInterface;
use Drupal\node\Entity\Node;
use Drupal\pathauto\AliasCleanerInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\node\NodeInterface;

/**
 * @coversDefaultClass \Drupal\mars_search\Controller\MarsSearchController
 * @group mars
 * @group mars_search
 */
class MarsSearchControllerTest extends UnitTestCase {

  const TEST_QUERY_OPTIONS = [
    'conditions' => [
      ['type', 'faq', '<>', TRUE],
    ],
    'keys' => 'some key',
  ];

  /**
   * System under test.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Controller\MarsSearchController
   */
  private $controller;

  /**
   * Container Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Search helper.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchHelperInterface
   */
  private $searchHelperMock;

  /**
   * Search query parser.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchQueryParserInterface
   */
  private $searchQueryParserMock;

  /**
   * Process factory mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\SearchProcessFactoryInterface
   */
  private $searchProcessFactoryMock;

  /**
   * Search builder mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchBuilderInterface
   */
  private $searchBuilderMock;

  /**
   * Translation mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translationMock;

  /**
   * Renderer mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Render\RendererInterface
   */
  private $rendererMock;

  /**
   * Entity type manager mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManagerMock;

  /**
   * Entity view builder mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityViewBuilderInterface
   */
  private $entityViewBuilderMock;

  /**
   * Request stack mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStackMock;

  /**
   * Request mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\Request
   */
  private $requestMock;

  /**
   * Config factory mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactoryMock;

  /**
   * Immutable config mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ImmutableConfig
   */
  private $immutableConfigMock;

  /**
   * Theme configurator parser mock.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser|\PHPUnit\Framework\MockObject\MockObject
   */
  private $themeConfiguratorParserMock;

  /**
   * The path validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $pathValidator;

  /**
   * The path validator service.
   *
   * @var \Drupal\mars_search\Processors\SearchPrettyFacetProcessInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $searchPrettyFacetProcessor;

  /**
   * Language helper mock.
   *

   * @var \Drupal\pathauto\AliasCleanerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $aliasCleanerMock;

  /**
   * Language helper mock.
   *
   * @var \Drupal\mars_common\LanguageHelper|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $languageHelperMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getViewBuilder')
      ->with('node')
      ->willReturn($this->entityViewBuilderMock);
    $nodeMock = $this->createMock(NodeInterface::class);
    $nodeMock
      ->expects($this->any())
      ->method('getTranslation')
      ->willReturn($nodeMock);
    $this->nodeStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn($nodeMock);

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->nodeStorageMock);
    $this->languageHelperMock
      ->expects($this->any())
      ->method('getCurrentLanguageId')
      ->willReturn('en');
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
          [
            'search_builder',
            $this->searchBuilderMock,
          ],
          [
            'search_pretty_facet_process',
            $this->searchPrettyFacetProcessor,
          ],
        ]
      );

    $this->controller = new MarsSearchController(
      $this->rendererMock,
      $this->searchProcessFactoryMock,
      $this->requestStackMock,
      $this->entityTypeManagerMock,
      $this->themeConfiguratorParserMock,
      $this->pathValidator,
      $this->aliasCleanerMock,
      $this->languageHelperMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(8))
      ->method('get')
      ->willReturnMap(
        [
          [
            'renderer',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->rendererMock,
          ],
          [
            'mars_search.search_factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->searchProcessFactoryMock,
          ],
          [
            'request_stack',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->requestStackMock,
          ],
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'mars_common.theme_configurator_parser',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->themeConfiguratorParserMock,
          ],
          [
            'path.validator',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->pathValidator,
          ],
          [
            'pathauto.alias_cleaner',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->aliasCleanerMock,
          ],
          [
            'mars_common.language_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->languageHelperMock,
          ],
        ]
      );

    $this->controller::create(
      $this->containerMock
    );
  }

  /**
   * Test autocomplete.
   */
  public function testShouldAutocomplete() {
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
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configFactoryMock,
          ],
        ]
      );

    $this->searchQueryParserMock
      ->expects($this->once())
      ->method('parseQuery')
      ->willReturn(self::TEST_QUERY_OPTIONS);
    $fieldMock = $this->getMockBuilder(FieldItemList::class)
      ->disableOriginalConstructor()
      ->getMock();
    $nodeMock = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
    $nodeMock
      ->expects($this->once())
      ->method('bundle')
      ->willReturn('faq');
    $nodeMock
      ->expects($this->once())
      ->method('get')
      ->with('field_qa_item_question')
      ->willReturn($fieldMock);
    $this->searchHelperMock
      ->expects($this->once())
      ->method('getSearchResults')
      ->willReturn([
        'results' => [$nodeMock],
      ]);
    $this->configFactoryMock
      ->expects($this->once())
      ->method('get')
      ->with('mars_search.search_no_results')
      ->willReturn($this->immutableConfigMock);
    $this->immutableConfigMock
      ->expects($this->exactly(2))
      ->method('get');

    $this->searchBuilderMock
      ->expects($this->once())
      ->method('getSearchNoResult');

    $autocompleteResult = $this->controller->autocomplete();
    $this->assertInstanceOf(JsonResponse::class, $autocompleteResult);
  }

  /**
   * Test search callback method for results.
   */
  public function testSearchCallbackResults() {
    $this->requestMock->query
      ->expects($this->once())
      ->method('all')
      ->willReturn([
        'action_type' => MarsSearchController::MARS_SEARCH_AJAX_RESULTS,
        'grid_type' => 'test_type',
        'page_id' => 0,
        'limit' => 0,
      ]);
    $nodeMock = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->searchBuilderMock
      ->expects($this->once())
      ->method('buildSearchResults')
      ->willReturn([
        [
          'limit' => 1,
          'keys' => 'test_search_key',
          'offset' => 0,
        ],
        [
          'itemsCount' => 1,
          'resultsCount' => 1,
        ],
        [
          '#items' => [$nodeMock],
        ],
      ]);
    $this->rendererMock
      ->expects($this->once())
      ->method('render')
      ->with($nodeMock);

    $callbackResponse = $this->controller->searchCallback($this->requestMock);
    $this->assertInstanceOf(JsonResponse::class, $callbackResponse);
  }

  /**
   * Test search callback method for facets.
   */
  public function testSearchCallbackFacets() {
    $this->requestMock->query
      ->expects($this->once())
      ->method('all')
      ->willReturn([
        'action_type' => MarsSearchController::MARS_SEARCH_AJAX_FACET,
        'grid_type' => 'search_page',
        'page_id' => 0,
      ]);
    $this->searchBuilderMock
      ->expects($this->once())
      ->method('buildSearchFacets');
    $this->searchBuilderMock
      ->expects($this->once())
      ->method('buildSearchHeader')
      ->willReturn([
        '#search_filters' => [],
      ]);
    $this->rendererMock
      ->expects($this->exactly(2))
      ->method('render');

    $callbackResponse = $this->controller->searchCallback($this->requestMock);
    $this->assertInstanceOf(JsonResponse::class, $callbackResponse);
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->requestMock = $this->createMock(Request::class);
    $this->requestMock->query = $this->createMock(ParameterBagInterface::class);
    $this->rendererMock = $this->createMock(RendererInterface::class);
    $this->searchHelperMock = $this->createMock(SearchHelperInterface::class);
    $this->searchQueryParserMock = $this->createMock(SearchQueryParserInterface::class);
    $this->entityViewBuilderMock = $this->createMock(EntityViewBuilderInterface::class);
    $this->requestStackMock = $this->createMock(RequestStack::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->immutableConfigMock = $this->createMock(ImmutableConfig::class);
    $this->searchProcessFactoryMock = $this->createMock(SearchProcessFactoryInterface::class);
    $this->searchBuilderMock = $this->createMock(SearchBuilder::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->pathValidator = $this->createMock(PathValidatorInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->nodeStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->searchPrettyFacetProcessor = $this->createMock(SearchPrettyFacetProcessInterface::class);
    $this->aliasCleanerMock = $this->createMock(AliasCleanerInterface::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
  }

}
