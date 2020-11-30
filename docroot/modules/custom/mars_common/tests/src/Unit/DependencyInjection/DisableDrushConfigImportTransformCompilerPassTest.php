<?php

namespace Drupal\Tests\mars_common\Unit\DependencyInjection;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\mars_common\DependencyInjection\DisableDrushConfigImportTransformCompilerPass;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Unit tests for the DisableDrushConfigImportTransformCompilerPass class.
 */
class DisableDrushConfigImportTransformCompilerPassTest extends UnitTestCase {

  /**
   * Mocked container builder.
   *
   * @var \Drupal\Core\DependencyInjection\ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerBuilder;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->containerBuilder = $this->createMock(ContainerBuilder::class);
  }

  /**
   * Tests that the import transformer is disabled if service exits.
   *
   * @test
   */
  public function shouldDisableImportTransformIfServiceExists() {
    $compilerPass = new DisableDrushConfigImportTransformCompilerPass();
    $serviceDefinition = $this->containerHasService('config.import.commands');

    $serviceDefinition
      ->expects($this->once())
      ->method('removeMethodCall')
      ->with('setImportTransformer');

    $compilerPass->process($this->containerBuilder);
  }

  /**
   * Tests that nothing is done to the definition if it doesn't exists.
   *
   * @test
   */
  public function shouldDoNothingIfDefinitionDoesntExists() {
    $compilerPass = new DisableDrushConfigImportTransformCompilerPass();

    $this->containerBuilder
      ->expects($this->never())
      ->method('getDefinition')
      ->with('config.import.commands');

    $compilerPass->process($this->containerBuilder);
  }

  /**
   * Creates a mocked service definition with the given name.
   *
   * @param string $serviceName
   *   The name of the service.
   *
   * @return \Symfony\Component\DependencyInjection\Definition|\PHPUnit\Framework\MockObject\MockObject
   *   The mocked service definition.
   */
  protected function containerHasService(string $serviceName): Definition {
    $serviceDefinition = $this->createMock(Definition::class);
    $this->containerBuilder
      ->method('hasDefinition')
      ->with($serviceName)
      ->willReturn(TRUE);

    $this->containerBuilder
      ->method('getDefinition')
      ->with($serviceName)
      ->willReturn($serviceDefinition);

    return $serviceDefinition;
  }

}
