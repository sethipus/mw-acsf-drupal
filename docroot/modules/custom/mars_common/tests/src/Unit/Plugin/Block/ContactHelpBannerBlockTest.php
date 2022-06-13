<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\Plugin\Block\ContactHelpBannerBlock;
use Drupal\mars_media\SVG\SVG;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class ContactHelpBannerBlockTest - unit tests for component.
 *
 * @package Drupal\Tests\mars_common\Unit
 * @covers \Drupal\mars_common\Plugin\Block\ContactFormBlock
 */
class ContactHelpBannerBlockTest extends UnitTestCase {
  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Tested ContactHelpBannerBlock block.
   *
   * @var \Drupal\mars_common\Plugin\Block\ContactHelpBannerBlock
   */
  private $contactHelpBannerBlock;

  /**
   * ThemeConfiguratorParserMock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParserMock;

  /**
   * Config factory mock.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactoryMock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration = [
    'title' => 'Contact Help',
    'description' => 'Description',
    'social_links_label' => 'See More On',
    'phone_cta_label' => 'Phone',
    'phone_cta_number' => '123456789',
    'email_cta_label' => 'Email Us',
    'email_cta_address' => 'support@mars.com',
    'help_and_contact_cta_label' => 'Help and Contact',
    'help_and_contact_cta_url' => '/help',
    'theme' => 'contact_help_banner_block',
  ];

  /**
   * Brand shape from theme configurator.
   *
   * @var string
   */
  private $svgContent = '<svg width="100" height="100"><circle cx="50" cy="50" r="40" stroke="green" stroke-width="4" fill="yellow" /></svg>';

  /**
   * Social links from theme configurator.
   *
   * @var \string[][]
   */
  private $socialLinks = [
    [
      'title' => 'facebook',
      'url' => 'https://facebook.com',
      'icon' => 'facebook.png',
    ],
  ];

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\LanguageHelper
   */
  private $languageHelperMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $definitions = [
      'provider' => 'test',
      'admin_label' => 'test',
    ];

    $configMock = $this->getMockBuilder(stdClass::class)
      ->setMethods(['get'])
      ->getMock();

    $this->configFactoryMock
      ->method('get')
      ->with('mars_common.character_limit_page')
      ->willReturn($configMock);

    $this->contactHelpBannerBlock = new ContactHelpBannerBlock(
          $this->configuration,
          'contact_help_banner_block',
          $definitions,
          $this->languageHelperMock,
          $this->themeConfiguratorParserMock,
          $this->configFactoryMock
      );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->languageHelperMock = $this->createLanguageHelperMock();
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
  }

  /**
   * Returns Language helper mock.
   *
   * @return \Drupal\mars_common\LanguageHelper|\PHPUnit\Framework\MockObject\MockObject
   *   Theme Configuration Parser service mock.
   */
  private function createLanguageHelperMock() {
    $mock = $this->createMock(LanguageHelper::class);
    $mock->method('translate')
      ->will(
              $this->returnCallback(
                  function ($arg) {
                      return $arg;
                  }
              )
          );

    return $mock;
  }

  /**
   * Test building block.
   *
   * @test
   */
  public function buildBlockRenderArrayProperly() {
    $this->themeConfiguratorParserMock
      ->expects($this->any())
      ->method('getBrandShapeWithoutFill')
      ->willReturn(new SVG($this->svgContent, 'id'));

    $this->themeConfiguratorParserMock
      ->expects($this->any())
      ->method('socialLinks')
      ->willReturn($this->socialLinks);

    $build = $this->contactHelpBannerBlock->build();

    $this->assertCount(13, $build);
    $this->assertEquals($this->configuration['title'], $build['#title']);
    $this->assertEquals($this->configuration['description'], $build['#description']);
    $this->assertEquals($this->configuration['social_links_label'], $build['#social_links_label']);
    $this->assertEquals($this->configuration['phone_cta_label'], $build['#phone_cta_label']);
    $this->assertEquals('tel:' . $this->configuration['phone_cta_number'], $build['#phone_cta_link']);
    $this->assertEquals($this->configuration['email_cta_label'], $build['#email_cta_label']);
    $this->assertEquals('mailto:' . $this->configuration['email_cta_address'], $build['#email_cta_link']);
    $this->assertEquals($this->configuration['help_and_contact_cta_label'], $build['#help_and_contact_cta_label']);
    $this->assertEquals($this->configuration['help_and_contact_cta_url'], $build['#help_and_contact_cta_url']);
    $this->assertEquals($this->socialLinks, $build['#social_menu_items']);
    $this->assertEquals($this->svgContent, $build['#brand_shape']);
    $this->assertArrayHasKey('#text_color_override', $build);
    $this->assertEquals($this->configuration['theme'], $build['#theme']);
  }

}
