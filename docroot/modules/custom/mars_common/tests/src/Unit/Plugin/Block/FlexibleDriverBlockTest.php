<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_common\MediaHelper;

/**
 * Class FlexibleDriverBlockTest.
 *
 * @package Drupal\Tests\mars_common\Unit
 * @covers \Drupal\mars_common\Plugin\Block\FooterBlock
 */
class FlexibleDriverBlockTest extends UnitTestCase {
  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Tested FlexibleDriverBlock block.
   *
   * @var \Drupal\mars_common\Plugin\Block\ContactHelpBannerBlock
   */
  private $flexibleDriverBlock;

  /**
   * ThemeConfiguratorParserMock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParserMock;

  /**
   * Media Helper service mock.
   *
   * @var \Drupal\mars_common\MediaHelper
   */
  protected $mediaHelper;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration = [
    'title' => 'Flexible driver',
    'description' => 'Description',
    'cta_label' => 'CTA Label',
    'cta_link' => 'CTA Link',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->createMocks();
    $container = new ContainerBuilder();
    $container->set('mars_common.media_helper', $this->mediaHelper);
    $container->set('mars_common.theme_configurator_parser', $this->themeConfiguratorParserMock);
    Drupal::setContainer($this->containerMock);

    $definitions = [
      'provider' => 'test',
      'admin_label' => 'test',
    ];

    $this->flexibleDriverBlock = new FlexibleDriverBlock(
      $this->configuration,
      'flexible_driver',
      $definitions,
      $this->mediaHelper,
      $this->themeConfiguratorParserMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->mediaHelper = $this->createMock(MediaHelper::class);
  }

  /**
   * Test building block.
   *
   * @test
   */
  public function buildBlockRenderArrayProperly() {

  }

}
