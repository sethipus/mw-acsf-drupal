<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\Plugin\Block\FooterBlock;
use Drupal\mars_common\SVG\SVG;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FooterBlockTest.
 *
 * @package Drupal\Tests\mars_common\Unit
 * @covers \Drupal\mars_common\Plugin\Block\FooterBlock
 */
class FooterBlockTest extends UnitTestCase {

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
   * Menu link tree mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTreeMock;

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
   * @var \PHPUnit\Framework\MockObject\MockObject||\Drupal\mars_common\ThemeConfiguratorParser
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
   * Config mock.
   *
   * @var \Drupal\Core\Config\Config|\PHPUnit\Framework\MockObject\MockObject
   */
  private $configMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    Drupal::setContainer($this->containerMock);
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
      $this->menuLinkTreeMock,
      $this->entityTypeManagerMock,
      $this->languageHelperMock,
      $this->themeConfiguratorParserMock,
      $this->configMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->menuLinkTreeMock = $this->createMock(MenuLinkTreeInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->configMock = $this->createMock(ConfigFactoryInterface::class);
    $this->menuStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->termStorageMock = $this->getMockBuilder(stdClass::class)
      ->setMethods(['loadTree'])
      ->getMock();
  }

  /**
   * Test Block creation.
   */
  public function testBlockShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(5))
      ->method('get')
      ->withConsecutive(
        [$this->equalTo('menu.link_tree')],
        [$this->equalTo('entity_type.manager')],
        [$this->equalTo('mars_common.language_helper')],
        [$this->equalTo('mars_common.theme_configurator_parser')],
        [$this->equalTo('config.factory')]
      )
      ->will($this->onConsecutiveCalls(
        $this->menuLinkTreeMock,
        $this->entityTypeManagerMock,
        $this->languageHelperMock,
        $this->themeConfiguratorParserMock,
        $this->configMock
      ));

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->withConsecutive(
        [$this->equalTo('menu')],
        [$this->equalTo('taxonomy_term')]
      )
      ->will($this->onConsecutiveCalls($this->menuLinkTreeMock, $this->termStorageMock));

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
    $this->assertCount(11, $config_form);
    $this->assertArrayHasKey('top_footer_menu', $config_form);
    $this->assertArrayHasKey('legal_links', $config_form);
    $this->assertArrayHasKey('marketing', $config_form);
    $this->assertArrayHasKey('corporate_tout', $config_form);
    $this->assertArrayHasKey('social_links_toggle', $config_form);
    $this->assertArrayHasKey('region_selector_toggle', $config_form);
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

    $this->menuLinkTreeMock
      ->expects($this->exactly(2))
      ->method('load')
      ->willReturn([]);

    $this->menuLinkTreeMock
      ->expects($this->exactly(2))
      ->method('transform')
      ->willReturn([]);

    $this->menuLinkTreeMock
      ->expects($this->exactly(2))
      ->method('build');

    $this->termStorageMock
      ->expects($this->any())
      ->method('loadTree')
      ->willReturn([]);

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->createMock(Config::class));

    $build = $this->footerBlock->build();

    $this->assertCount(14, $build);
    $this->assertArrayHasKey('#cache', $build);
    $this->assertArrayHasKey('#top_footer_menu', $build);
    $this->assertArrayHasKey('#legal_links', $build);
    $this->assertArrayHasKey('#marketing', $build);
    $this->assertArrayHasKey('#corporate_tout_text', $build);
    $this->assertArrayHasKey('#corporate_tout_url', $build);
    $this->assertArrayHasKey('#region_title', $build);
    $this->assertArrayHasKey('#social_header', $build);
    $this->assertCount(0, $build['#social_links']);
    $this->assertArrayHasKey('#region_selector', $build);
    $this->assertEquals('footer_block', $build['#theme']);
  }

}
