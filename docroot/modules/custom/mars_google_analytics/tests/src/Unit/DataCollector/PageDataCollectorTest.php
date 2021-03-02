<?php

namespace Drupal\Tests\mars_google_analytics\Unit\DataCollector;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\mars_google_analytics\DataCollector\PageDataCollector;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_google_analytics\DataCollector\PageDataCollector
 * @group mars
 * @group mars_google_analytics
 */
class PageDataCollectorTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_google_analytics\DataCollector\PageDataCollector
   */
  private $collector;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatchMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\node\NodeInterface
   */
  private $nodeMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->collector = new PageDataCollector(
      $this->routeMatchMock
    );
  }

  /**
   * Test.
   */
  public function testShouldGetDataLayerId() {
    $id = $this->collector->getDataLayerId();
    $this->assertIsString($id);
  }

  /**
   * Test.
   */
  public function testShouldCollectAndGetPageTypeAndGetGaData() {
    $this->routeMatchMock
      ->expects($this->once())
      ->method('getParameter')
      ->willReturn($this->nodeMock);

    $this->nodeMock
      ->expects($this->once())
      ->method('getType')
      ->willReturn('test_type');

    $this->collector->collect();
    $type = $this->collector->getGaData();
    $this->assertSame('test_type', $type);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->routeMatchMock = $this->createMock(RouteMatchInterface::class);
    $this->nodeMock = $this->createMock(NodeInterface::class);
  }

}
