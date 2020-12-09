<?php

namespace Drupal\Tests\mars_recipes\Unit;

use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_recipes\Plugin\Block\RecipeFeatureBlock;

/**
 * Class RecipeDetailBodyTest.
 *
 * @package Drupal\Tests\mars_recipes\Unit
 * @covers \Drupal\mars_recipes\Plugin\Block\RecipeFeatureBlockTest
 */
class RecipeFeatureBlockTest extends UnitTestCase {

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration;

  /**
   * Tested RecipeFeatureBlock.
   *
   * @var \Drupal\mars_recipes\Plugin\Block\RecipeFeatureBlock
   */
  private $recipeFeatureBlock;

  /**
   * Entity type manager mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManagerMock;

  /**
   * ThemeConfiguratorParserMock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParserMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\LanguageHelper
   */
  private $languageHelperMock;

  /**
   * Media Helper service Mock.
   *
   * @var \Drupal\mars_common\MediaHelper|\PHPUnit\Framework\MockObject\MockObject
   */
  private $mediaHelperMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->configuration = [];
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    // We should create it in test to import different configs.
    $this->recipeFeatureBlock = new RecipeFeatureBlock(
      $this->configuration,
      'recipe_feature_block',
      $definitions,
      $this->entityTypeManagerMock,
      $this->themeConfiguratorParserMock,
      $this->languageHelperMock,
      $this->mediaHelperMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
  }

  /**
   * Test Block creation.
   *
   * @test
   */
  public function blockShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(4))
      ->method('get')
      ->withConsecutive(
        [$this->equalTo('entity_type.manager')],
        [$this->equalTo('mars_common.theme_configurator_parser')],
        [$this->equalTo('mars_common.language_helper')],
        [$this->equalTo('mars_common.media_helper')]
      )
      ->will($this->onConsecutiveCalls($this->entityTypeManagerMock, $this->themeConfiguratorParserMock, $this->languageHelperMock, $this->mediaHelperMock));

    $definitions = [
      'provider' => 'test',
      'admin_label' => 'test',
    ];
    $this->recipeFeatureBlock::create($this->containerMock, [], 'recipe_feature_block', $definitions);
  }

}
