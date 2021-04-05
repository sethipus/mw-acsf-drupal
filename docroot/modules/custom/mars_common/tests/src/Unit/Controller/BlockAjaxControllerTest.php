<?php

namespace Drupal\Tests\mars_common\Unit\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Render\RendererInterface;
use Drupal\mars_common\Controller\BlockAjaxController;
use Drupal\mars_common\Plugin\Block\PollBlock;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\mars_common\Controller\BlockAjaxController
 * @group mars
 * @group mars_common
 */
class BlockAjaxControllerTest extends UnitTestCase {


  /**
   * System under test.
   *
   * @var \Drupal\mars_common\Controller\BlockAjaxController
   */
  private $controller;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Block\BlockManager|\PHPUnit\Framework\MockObject\MockObject
   */
  private $blockManagerMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Render\RendererInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $rendererMock;

  /**
   * Mock.
   *
   * @var \Symfony\Component\HttpFoundation\Request|\PHPUnit\Framework\MockObject\MockObject
   */
  private $requestMock;

  /**
   * Mock.
   *
   * @var \Drupal\mars_common\Plugin\Block\PollBlock|\PHPUnit\Framework\MockObject\MockObject
   */
  private $pollBlockMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->controller = new BlockAjaxController(
      $this->blockManagerMock,
      $this->rendererMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
          [
            'plugin.manager.block',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->blockManagerMock,
          ],
          [
            'renderer',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->rendererMock,
          ],
      ]);
    $this->controller::create($this->containerMock);
  }

  /**
   * Test.
   */
  public function testAjaxBlock() {
    $this->requestMock
      ->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap([
        [
          'plugin_id',
          '',
          'PuginId',
        ],
        [
          'block_config',
          [],
          ['ajaxId' => 'id'],
        ],
      ]);

    $this->blockManagerMock
      ->expects($this->once())
      ->method('createInstance')
      ->willReturn($this->pollBlockMock);

    $this->pollBlockMock
      ->expects($this->once())
      ->method('setConfiguration');

    $this->pollBlockMock
      ->expects($this->once())
      ->method('build')
      ->willReturn(['#attached' => []]);

    $this->rendererMock
      ->expects($this->once())
      ->method('renderRoot')
      ->willReturn('');

    $response = $this->controller->ajaxBlock($this->requestMock);
    $this->assertInstanceOf(AjaxResponse::class, $response);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->blockManagerMock = $this->createMock(BlockManager::class);
    $this->rendererMock = $this->createMock(RendererInterface::class);
    $this->requestMock = $this->createMock(Request::class);
    $this->pollBlockMock = $this->createMock(PollBlock::class);
    $this->containerMock = $this->createMock(ContainerInterface::class);
  }

}
