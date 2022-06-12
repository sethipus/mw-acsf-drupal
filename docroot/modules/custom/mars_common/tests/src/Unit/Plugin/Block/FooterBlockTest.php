<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\MenuBuilder;
use Drupal\mars_common\Plugin\Block\FooterBlock;
use Drupal\mars_media\SVG\SVG;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mars_common\ThemeConfiguratorService;
use Drupal\Core\File\FileUrlGenerator;

/**
 * Class FooterBlockTest is responsible for footer component logic.
 *
 * @package Drupal\Tests\mars_common\Unit
 * @covers \Drupal\mars_common\Plugin\Block\FooterBlock
 */
class FooterBlockTest extends UnitTestCase {

  /**
   * Mock.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorService|\PHPUnit\Framework\MockObject\MockObject
   */
  private $themeConfiguratorServiceMock;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Form\FormStateInterface
   */
  private $formStateMock;


  /**
   * File storage.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject||\Drupal\Core\Entity\EntityStorageInterface
   */
  protected $menuStorageMock;

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
   * Term storage.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject
   */
  protected $termStorageMock;

  /**
   * Tested footer block.
   *
   * @var \Drupal\mars_common\Plugin\Block\FooterBlock
   */
  private $footerBlock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\LanguageHelper
   */
  private $languageHelperMock;

  /**
   * Menu builder service mock.
   *
   * @var \Drupal\mars_common\MenuBuilder|\PHPUnit\Framework\MockObject\MockObject
   */
  private $menuBuilderMock;

  /**
   * Config factory mock.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $configFactoryMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|\PHPUnit\Framework\MockObject\MockObject
   */
  private $immutableConfigMock;

  /**
   * File url generator service.
   *
   * @var Drupal\Core\File\FileUrlGenerator|\PHPUnit\Framework\MockObject\MockObject
   */
  private $fileUrlGenerator;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();

    $this->configFactoryMock
      ->method('getEditable')
      ->with('mars_common.character_limit_page')
      ->willReturn($this->immutableConfigMock);

    \Drupal::setContainer($this->containerMock);
    $this->configuration = [
      'top_footer_menu' => 'top footer menu',
      'legal_links' => 'legal menu links',
      'marketing' => [
        'value' => 'Marketing and copyright text',
        'format' => 'plain_text',
      ],
      'corporate_tout' => [
        'url' => 'http://mars.com',
        'title' => 'Corporate tout text',
      ],
      'social_links_toggle' => FALSE,
      'region_selector_toggle' => TRUE,
      'cta_button_label' => 'See All',
    ];
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->withConsecutive(
        [$this->equalTo('menu')],
        [$this->equalTo('taxonomy_term')]
      )
      ->will($this->onConsecutiveCalls($this->menuStorageMock, $this->termStorageMock));

    // We should create it in test to import different configs.
    $this->footerBlock = new FooterBlock(
      $this->configuration,
      'footer_block',
      $definitions,
      $this->themeConfiguratorServiceMock,
      $this->entityTypeManagerMock,
      $this->languageHelperMock,
      $this->themeConfiguratorParserMock,
      $this->menuBuilderMock,
      $this->configFactoryMock,
      $this->fileUrlGenerator
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->themeConfiguratorServiceMock = $this->createMock(ThemeConfiguratorService::class);
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->menuStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->menuBuilderMock = $this->createMock(MenuBuilder::class);
    $this->termStorageMock = $this->getMockBuilder(stdClass::class)
      ->setMethods(['loadTree'])
      ->getMock();
    $this->immutableConfigMock = $this->createMock(ImmutableConfig::class);
    $this->fileUrlGenerator = $this->createMock(FileUrlGenerator::class);
  }

  /**
   * Test Block creation.
   */
  public function testBlockShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(7))
      ->method('get')
      ->withConsecutive(
        [$this->equalTo('mars_common.theme_configurator_service')],
        [$this->equalTo('entity_type.manager')],
        [$this->equalTo('mars_common.language_helper')],
        [$this->equalTo('mars_common.theme_configurator_parser')],
        [$this->equalTo('mars_common.menu_builder')],
        [$this->equalTo('config.factory')],
        [$this->equalTo('file_url_generator')]
      )
      ->will($this->onConsecutiveCalls(
        $this->themeConfiguratorServiceMock,
        $this->entityTypeManagerMock,
        $this->languageHelperMock,
        $this->themeConfiguratorParserMock,
        $this->menuBuilderMock,
        $this->configFactoryMock,
        $this->fileUrlGenerator
      ));

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->withConsecutive(
        [$this->equalTo('menu')],
        [$this->equalTo('taxonomy_term')]
      );

    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];
    $this->footerBlock::create($this->containerMock, $this->configuration, 'footer_block', $definitions);
  }

  /**
   * Test configuration form.
   */
  public function testBuildConfigurationFormProperly() {
    $this->menuStorageMock
      ->expects($this->exactly(2))
      ->method('load')
      ->withConsecutive(
        [$this->equalTo('top footer menu')],
        [$this->equalTo('legal menu links')]
      )
      ->will($this->onConsecutiveCalls('', ''));

    $config_form = $this->footerBlock->buildConfigurationForm(
      [],
      $this->formStateMock
    );
    $this->assertCount(15, $config_form);
    $this->assertArrayHasKey('top_footer_menu', $config_form);
    $this->assertArrayHasKey('override_text_color', $config_form);
    $this->assertArrayHasKey('legal_links', $config_form);
    $this->assertArrayHasKey('marketing', $config_form);
    $this->assertArrayHasKey('corporate_tout', $config_form);
    $this->assertArrayHasKey('social_links_toggle', $config_form);
    $this->assertArrayHasKey('region_selector_toggle', $config_form);
    $this->assertArrayHasKey('cta_button_label', $config_form);
  }

  /**
   * Test building block.
   */
  public function testBuildBlockRenderArrayProperly() {
    $this->themeConfiguratorParserMock
      ->expects($this->exactly(1))
      ->method('getLogoFromTheme')
      ->willReturn('');

    $this->themeConfiguratorParserMock
      ->expects($this->exactly(1))
      ->method('getBrandBorder')
      ->willReturn(new SVG('<svg xmlns="http://www.w3.org/2000/svg" />', 'id'));

    $this->menuBuilderMock
      ->expects($this->exactly(2))
      ->method('getMenuItemsArray')
      ->willReturn([]);

    $this->termStorageMock
      ->expects($this->any())
      ->method('loadTree')
      ->willReturn([]);

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->immutableConfigMock);

    $this->immutableConfigMock
      ->method('getCacheContexts')
      ->willReturn([]);

    $this->immutableConfigMock
      ->method('getCacheTags')
      ->willReturn([]);

    $this->immutableConfigMock
      ->method('getCacheMaxAge')
      ->willReturn(0);

    $build = $this->footerBlock->build();

    $this->assertCount(24, $build);
    $this->assertArrayHasKey('#cache', $build);
    $this->assertArrayHasKey('#top_footer_menu', $build);
    $this->assertArrayHasKey('#legal_links', $build);
    $this->assertArrayHasKey('#marketing', $build);
    $this->assertArrayHasKey('#corporate_tout_text', $build);
    $this->assertArrayHasKey('#corporate_tout_url', $build);
    $this->assertArrayHasKey('#cta_button_label', $build);
    $this->assertArrayHasKey('#region_title', $build);
    $this->assertArrayHasKey('#social_header', $build);
    $this->assertArrayHasKey('#text_color_override', $build);
    $this->assertCount(0, $build['#social_links']);
    $this->assertArrayHasKey('#region_selector', $build);
    $this->assertEquals('footer_block', $build['#theme']);
  }

}
