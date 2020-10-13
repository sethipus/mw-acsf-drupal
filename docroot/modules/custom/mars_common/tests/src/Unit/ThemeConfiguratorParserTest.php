<?php

namespace Drupal\Tests\mars_common\Unit;

use Drupal;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ThemeConfiguratorParserTest.
 *
 * @package Drupal\Tests\mars_common\Unit
 * @covers \Drupal\mars_common\ThemeConfiguratorParser
 */
class ThemeConfiguratorParserTest extends UnitTestCase {

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Tested ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  private $themeConfiguratorParser;

  /**
   * Entity type manager mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManagerMock;

  /**
   * Config Factory mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactoryMock;

  /**
   * File storage.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorageMock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration = [
    'brand_shape' => '<svg xmlns="http://www.w3.org/2000/svg" width="125" height="15" fill="none" viewBox="0 0 125 15">
    <path fill="#EAAA00" fill-rule="evenodd" d="M100.004 12.542L87.502 0l-12.5 12.542L62.502 0 50 12.542 37.501 0l-12.5 12.542L12.498 0 0 12.542V15h125v-2.458L112.501 0l-12.497 12.542z" clip-rule="evenodd"/>
</svg>',
    'logo' => [
      'path' => 'logo-path',
    ],
    'social' => [
      [
        'name' => 'facebook',
        'link' => 'link',
        'icon' => [34],
      ],
    ],
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    Drupal::setContainer($this->containerMock);

    $fileMock = $this->getMockBuilder(stdClass::class)
      ->setMethods(['createFileUrl'])
      ->getMock();

    $fileMock
      ->expects($this->any())
      ->method('createFileUrl')
      ->willReturn('http://mars.com');

    $this->fileStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn($fileMock);

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->withConsecutive(
        [$this->equalTo('file')]
      )
      ->will($this->onConsecutiveCalls($this->fileStorageMock));

    $configMock = $this->getMockBuilder(stdClass::class)
      ->setMethods(['get'])
      ->getMock();

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->with('emulsifymars.settings')
      ->willReturn($configMock);

    $configMock
      ->expects($this->once())
      ->method('get')
      ->willReturn($this->configuration);

    $this->themeConfiguratorParser = new ThemeConfiguratorParser(
      $this->entityTypeManagerMock,
      $this->configFactoryMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->fileStorageMock = $this->createMock(EntityStorageInterface::class);
  }

  /**
   * Test getLogoFromTheme.
   */
  public function testGetLogoFromTheme() {
    $getLogoFromTheme = $this->themeConfiguratorParser->getLogoFromTheme();
    $this->assertEquals($this->configuration['logo']['path'], $getLogoFromTheme);
  }

  /**
   * Test socialLinks.
   */
  public function testSocialLinks() {
    $socialLinks = $this->themeConfiguratorParser->socialLinks();
    $this->assertNotEmpty($socialLinks);
  }

  /**
   * Test getSettingValue.
   */
  public function testGetSettingValue() {
    $getSettingValueBrandShape = $this->themeConfiguratorParser->getSettingValue('brand_shape');
    $this->assertEquals($this->configuration['brand_shape'], $getSettingValueBrandShape);
  }

}
