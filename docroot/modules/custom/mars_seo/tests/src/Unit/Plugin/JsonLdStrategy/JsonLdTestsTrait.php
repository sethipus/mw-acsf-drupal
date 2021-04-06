<?php

namespace Drupal\Tests\mars_seo\Unit\Plugin\JsonLdStrategy;

use Drupal\Core\Plugin\Context\Context;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Trait JsonLdTestsTrait.
 *
 * Provides helper methods and common tests for LD JSON plugins.
 *
 * @package Drupal\Tests\mars_seo\Unit\Plugin\JsonLdStrategy
 */
trait JsonLdTestsTrait {

  /**
   * Test.
   */
  public function testLabel() {
    $this->assertEquals(static::DEFINITIONS['label'], $this->jsonLdPlugin->label());
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(3))
      ->method('get')
      ->willReturnMap(
        [
          [
            'mars_common.media_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->mediaHelperMock,
          ],
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configFactoryMock,
          ],
          [
            'url_generator',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->urlGeneratorMock,
          ],
        ]
      );

    $this->jsonLdPlugin::create(
      $this->containerMock,
      [],
      static::PLUGIN_ID,
      static::DEFINITIONS,
    );
  }

  /**
   * Test.
   */
  public function testSupportedBundles() {
    $this->assertArrayEquals($this->supportedBundles, $this->jsonLdPlugin->supportedBundles());
  }

  /**
   * Helper function for creating mock.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Plugin\Context\Context
   */
  protected function createContextMock() {
    return $this->getMockBuilder(Context::class)
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * Helper function for creating mock.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Plugin\Context\Context
   */
  protected function createNodeMock() {
    return $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * Helper function for creating mock.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Plugin\Context\Context
   */
  protected function createNodeContextMock() {
    $node = $this->createNodeMock();
    $node_context = $this->createContextMock();
    $node_context->expects($this->once())
      ->method('getContextValue')
      ->willReturn($node);
     return $node_context;
  }

  /**
   * Helper function for creating mock.
   *
   * @param array $build_array
   *   Build array properties list.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Plugin\Context\Context
   */
  protected function createBuildContext($build_array = []) {
    $build_context = $this->createContextMock();
    $build_context->expects($this->once())
      ->method('getContextValue')
      ->willReturn($build_array);
    return $build_context;
  }

}
