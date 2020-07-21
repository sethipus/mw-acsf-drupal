<?php

namespace Drupal\Tests\platform_core\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\platform_core\DependencyInjection\DisableDrushConfigImportTransformCompilerPass;
use Drupal\platform_core\PlatformCoreServiceProvider;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

/**
 * Unit tests for PlatformCoreServiceProviderTest class.
 */
class PlatformCoreServiceProviderTest extends UnitTestCase {

  /**
   * Tests if the compiler pass is added to the container.
   *
   * @test
   */
  public function shouldAddDisableImportTransformerPass() {
    $service_provider = new PlatformCoreServiceProvider();
    $container_builder = $this->createMock(ContainerBuilder::class);

    $container_builder
      ->expects($this->once())
      ->method('addCompilerPass')
      ->with(
        $this->isInstanceOf(DisableDrushConfigImportTransformCompilerPass::class),
        PassConfig::TYPE_BEFORE_OPTIMIZATION,
        250
      );

    $service_provider->register($container_builder);
  }

}
