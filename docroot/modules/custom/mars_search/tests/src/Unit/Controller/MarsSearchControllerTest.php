<?php

namespace Drupal\Tests\mars_search\Unit\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\mars_search\Controller\MarsSearchController;
use Drupal\mars_search\SearchHelperInterface;
use Drupal\mars_search\SearchQueryParserInterface;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

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
    'cards_view' => FALSE,
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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\SearchHelperInterface
   */
  private $searchHelperMock;

  /**
   * Search query parser.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\SearchQueryParserInterface
   */
  private $searchQueryParserMock;

  /**
   * Renderer mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Render\RendererInterface
   */
  private $rendererMock;

  /**
   * Menu link tree mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Menu\MenuLinkTreeInterface
   */
  private $menuLinkTreeMock;

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
   * Menu link tree element mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Menu\MenuLinkTreeElement
   */
  private $menuLinkTreeElementMock;

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

    $this->controller = new MarsSearchController(
      $this->rendererMock,
      $this->searchHelperMock,
      $this->searchQueryParserMock,
      $this->menuLinkTreeMock,
      $this->entityTypeManagerMock,
      $this->requestStackMock,
      $this->configFactoryMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(7))
      ->method('get')
      ->willReturnMap(
        [
          [
            'renderer',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->rendererMock,
          ],
          [
            'mars_search.search_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->searchHelperMock,
          ],
          [
            'mars_search.search_query_parser',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->searchQueryParserMock,
          ],
          [
            'menu.link_tree',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->menuLinkTreeMock,
          ],
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'request_stack',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->requestStackMock,
          ],
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configFactoryMock,
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
  public function testShouldAutocompleteNoResults() {
    $this->searchQueryParserMock
      ->expects($this->once())
      ->method('parseQuery')
      ->willReturn(self::TEST_QUERY_OPTIONS);

    $this->searchHelperMock
      ->expects($this->once())
      ->method('getSearchResults');

    $this->configFactoryMock
      ->expects($this->once())
      ->method('get')
      ->willReturn($this->immutableConfigMock);
    $this->immutableConfigMock
      ->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        [
          'no_results_heading',
          'No results config heading',
        ],
        [
          'no_results_text',
          'No results config text',
        ],
      ]);

    $this->menuBuildAsserts();

    $this->rendererMock
      ->expects($this->once())
      ->method('render');

    $autocompleteResult = $this->controller->autocomplete();
    $this->assertInstanceOf(JsonResponse::class, $autocompleteResult);
  }

  /**
   * Test autocomplete.
   */
  public function testShouldAutocompleteWithResults() {
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
      ->willReturn($this->immutableConfigMock);

    $this->menuBuildAsserts();

    $autocompleteResult = $this->controller->autocomplete();
    $this->assertInstanceOf(JsonResponse::class, $autocompleteResult);
  }

  /**
   * Test search callback method.
   */
  public function testSearchCallback() {
    $this->searchQueryParserMock
      ->expects($this->once())
      ->method('parseQuery')
      ->willReturn(self::TEST_QUERY_OPTIONS);
    $nodeMock = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->searchHelperMock
      ->expects($this->once())
      ->method('getSearchResults')
      ->willReturn([
        'results' => [$nodeMock],
      ]);
    $this->entityViewBuilderMock
      ->expects($this->once())
      ->method('view')
      ->with($nodeMock, 'card');
    $this->rendererMock
      ->expects($this->once())
      ->method('render');
    $callbackResponse = $this->controller->searchCallback();
    $this->assertInstanceOf(JsonResponse::class, $callbackResponse);
  }

  /**
   * Test see all callback method.
   */
  public function testSeeAllCallback() {
    $topNodeMock = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
    $resultsNodeMock = clone $topNodeMock;
    $resultsNodeMock->id = 2;

    $entityStorageMock = $this->getMockBuilder(EntityStorageInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entityStorageMock
      ->expects($this->once())
      ->method('loadMultiple')
      ->willReturn([$topNodeMock]);

    $this->entityTypeManagerMock
      ->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($entityStorageMock);
    $this->requestMock->query->expects($this->once())
      ->method('all')
      ->willReturn([
        'searchOptions' => self::TEST_QUERY_OPTIONS,
        'topResults' => [1 => $topNodeMock],
        'id' => 'test_id',
      ]);
    $this->searchQueryParserMock
      ->expects($this->once())
      ->method('parseQuery')
      ->willReturn(self::TEST_QUERY_OPTIONS);
    $this->searchHelperMock
      ->expects($this->once())
      ->method('getSearchResults')
      ->willReturn([
        'results' => [$resultsNodeMock],
      ]);
    $this->entityViewBuilderMock
      ->expects($this->exactly(2))
      ->method('view')
      ->withConsecutive(
        [$topNodeMock, 'card'],
        [$resultsNodeMock, 'card']
      );
    $this->rendererMock
      ->expects($this->once())
      ->method('renderRoot');

    $callbackResponse = $this->controller->seeAllCallback();
    $this->assertInstanceOf(Response::class, $callbackResponse);
  }

  /**
   * Menu build asserts.
   */
  private function menuBuildAsserts() {
    // Menu build.
    $menu_item_url = $this->getMockBuilder(Url::class)
      ->disableOriginalConstructor()
      ->getMock();
    $menu_item_url
      ->expects($this->once())
      ->method('setAbsolute')
      ->willReturn($menu_item_url);
    $menu_item_url
      ->expects($this->once())
      ->method('toString')
      ->willReturn('string_url');
    $this->menuLinkTreeMock
      ->expects($this->once())
      ->method('load')
      ->willReturn([]);
    $this->menuLinkTreeMock
      ->expects($this->once())
      ->method('transform')
      ->willReturn([]);
    $this->menuLinkTreeMock
      ->expects($this->once())
      ->method('build')
      ->willReturn(
        [
          '#items' => [
            [
              'title' => 'Test link title',
              'url' => $menu_item_url,
            ],
          ],
        ]
      );
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
    $this->searchHelperMock->request = $this->requestMock;
    $this->searchQueryParserMock = $this->createMock(SearchQueryParserInterface::class);
    $this->menuLinkTreeMock = $this->createMock(MenuLinkTreeInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->entityViewBuilderMock = $this->createMock(EntityViewBuilderInterface::class);
    $this->requestStackMock = $this->createMock(RequestStack::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->immutableConfigMock = $this->createMock(ImmutableConfig::class);
    $this->menuLinkTreeElementMock = $this->createMock(MenuLinkTreeElement::class);
  }

}
