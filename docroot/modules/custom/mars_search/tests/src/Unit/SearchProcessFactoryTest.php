<?php

namespace Drupal\Tests\mars_search\Unit;

use Drupal\mars_search\Processors\SearchProcessManagerInterface;
use Drupal\mars_search\SearchProcessFactory;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\mars_search\SearchProcessFactory
 * @group mars
 * @group mars_search
 */
class SearchProcessFactoryTest extends UnitTestCase {

  /**
   * Messenger mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_search\Processors\SearchProcessManagerInterface
   */
  private $testProcessServiceMock;

  /**
   * Process factory under test.
   *
   * @var \Drupal\mars_search\SearchProcessFactory
   */
  private $factory;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();

    $this->testProcessServiceMock
      ->expects($this->once())
      ->method('getManagerId')
      ->willReturn('test_process_id');

    $this->factory = new SearchProcessFactory();
    $this->factory->addProcessManager($this->testProcessServiceMock);
  }

  /**
   * Test get process manager.
   */
  public function testGetProcessManager() {
    $manager = $this->factory->getProcessManager('test_process_id');
    $this->assertEquals($this->testProcessServiceMock, $manager);
  }

  /**
   * Test get managers.
   */
  public function testGetManagers() {
    $expected = [
      'test_process_id' => $this->testProcessServiceMock,
    ];
    $managers = $this->factory->getProcessManagers();
    $this->assertArrayHasKey('test_process_id', $managers);
    $this->assertEquals($expected, $managers);
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->testProcessServiceMock = $this->createMock(SearchProcessManagerInterface::class);
  }

}
