<?php

namespace Drupal\Tests\mars_search\Unit\Controller;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\mars_search\Controller\MarsSearchController;
use Drupal\mars_search\SearchHelperInterface;
use Drupal\mars_search\SearchQueryParserInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_search\Controller\MarsSearchController
 * @group mars
 * @group mars_search
 */
class MarsSearchControllerTest extends UnitTestCase {

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
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $this->controller = new MarsSearchController(
      $this->rendererMock,
      $this->searchHelperMock,
      $this->searchQueryParserMock,
      $this->menuLinkTreeMock,
      $this->entityTypeManagerMock
    );

  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->entityTypeManagerMock
      ->expects($this->exactly(1))
      ->method('getViewBuilder')
      ->willReturn($this->entityViewBuilderMock);
    $this->containerMock
      ->expects($this->exactly(5))
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
        ]
      );

    $this->controller::create(
      $this->containerMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->rendererMock = $this->createMock(RendererInterface::class);
    $this->searchHelperMock = $this->createMock(SearchHelperInterface::class);
    $this->searchQueryParserMock = $this->createMock(SearchQueryParserInterface::class);
    $this->menuLinkTreeMock = $this->createMock(MenuLinkTreeInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->entityViewBuilderMock = $this->createMock(EntityViewBuilderInterface::class);
  }

}
