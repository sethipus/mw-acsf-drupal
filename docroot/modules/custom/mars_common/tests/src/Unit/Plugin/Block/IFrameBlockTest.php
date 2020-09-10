<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal;
use Drupal\mars_common\Plugin\Block\IFrameBlock;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class IFrameBlockTest.
 *
 * @package Drupal\Tests\mars_common\Unit
 * @covers \Drupal\mars_common\Plugin\Block\ContactFormBlock
 */
class IFrameBlockTest extends UnitTestCase {

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Tested IFrameBlock block.
   *
   * @var \Drupal\mars_common\Plugin\Block\IFrameBlock
   */
  private $iFrameBlock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration = [
    'url' => 'demo-url',
    'accessibility_title' => 'Accessibility Title',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    Drupal::setContainer($this->containerMock);

    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    $this->iFrameBlock = new IFrameBlock(
      $this->configuration,
      'iframe_block',
      $definitions
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
  }

  /**
   * Test building block.
   *
   * @test
   */
  public function buildBlockRenderArrayProperly() {
    $build = $this->iFrameBlock->build();

    $this->assertCount(3, $build);
    $this->assertArrayHasKey('#url', $build);
    $this->assertEquals($this->configuration['url'], $build['#url']);
    $this->assertArrayHasKey('#accessibility_title', $build);
    $this->assertEquals($this->configuration['accessibility_title'], $build['#accessibility_title']);
    $this->assertArrayHasKey('#theme', $build);
    $this->assertEquals('iframe_block', $build['#theme']);
  }

}
