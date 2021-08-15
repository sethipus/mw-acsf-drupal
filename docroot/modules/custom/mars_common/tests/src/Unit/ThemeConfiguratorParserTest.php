<?php

namespace Drupal\Tests\mars_common\Unit;

use Drupal;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\Entity\File;
use Drupal\mars_media\SVG\SVGFactory;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Tests\UnitTestCase;
use org\bovigo\vfs\vfsStream;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mars_media\SVG\SVG;
use Drupal\Core\Url;

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
   * Mocked SVG factory service.
   *
   * @var \Drupal\mars_media\SVG\SVGFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  private $svgFactoryMock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration = [
    'brand_shape' => '<svg xmlns="http://www.w3.org/2000/svg" width="125" height="15" fill="none" viewBox="0 0 125 15">
    <path fill="#EAAA00" fill-rule="evenodd" d="M100.004 12.542L87.502 0l-12.5 12.542L62.502 0 50 12.542 37.501 0l-12.5 12.542L12.498 0 0 12.542V15h125v-2.458L112.501 0l-12.497 12.542z" clip-rule="evenodd"/>
</svg>',
    'brand_borders' => [
      0 => 'test',
    ],
    'brand_borders_2' => [
      0 => 'test',
    ],
    'graphic_divider' => [
      0 => 'test',
    ],
    'logo' => [
      'path' => 'logo-path',
    ],
    'logo_alt' => 'test logo alt',
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
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    Drupal::setContainer($this->containerMock);

    $vfsRoot = vfsStream::setup('root');
    $vfsFile = vfsStream::newFile('mock.file')
      ->withContent('mock_content')
      ->at($vfsRoot);

    $fileMock = $this->createMock(File::class);
    $fileMock
      ->expects($this->any())
      ->method('getFileUri')
      ->willReturn($vfsFile->url());

    $fileMock
      ->expects($this->any())
      ->method('id')
      ->willReturn('test');

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

    $this->svgFactoryMock
      ->expects($this->any())
      ->method('createSvgFromFileId')
      ->willReturn(new SVG('<svg xmlns="http://www.w3.org/2000/svg" />', 'id'));

    $this->themeConfiguratorParser = new ThemeConfiguratorParser(
      $this->entityTypeManagerMock,
      $this->configFactoryMock,
      $this->svgFactoryMock,
      new NullLogger()
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
    $this->svgFactoryMock = $this->createMock(SVGFactory::class);
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

  /**
   * Test testGetUrlForFile.
   */
  public function testGetUrlForFile() {
    $url_for_file = $this->themeConfiguratorParser->getUrlForFile('test');
    $url = Url::fromUri('http://example.com/root/mock.file');
    $this->assertEquals($url->getUri(), $url_for_file->getUri());
  }

  /**
   * Test getLogoAltFromTheme.
   */
  public function testGetLogoAltFromTheme() {
    $logo_alt = $this->themeConfiguratorParser->getLogoAltFromTheme();
    $expected = 'test logo alt';
    $this->assertEquals($expected, $logo_alt);
  }

  /**
   * Test getBrandBorder.
   */
  public function testGetBrandBorder() {
    $svg = $this->themeConfiguratorParser->getBrandBorder();

    $expected = '<svg xmlns="http://www.w3.org/2000/svg">
      <defs>
        <pattern id="id-repeat-pattern" patternUnits="userSpaceOnUse"/>
      </defs>
      <rect fill="url(#id-repeat-pattern)" width="100%"/>
    </svg>';

    $this->assertXmlStringEqualsXmlString($expected, (string) $svg);
  }

  /**
   * Test getBrandBorder2.
   */
  public function testGetBrandBorder2() {
    $svg = $this->themeConfiguratorParser->getBrandBorder2();

    $expected = '<svg xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet"/>';

    $this->assertXmlStringEqualsXmlString($expected, (string) $svg);
  }

  /**
   * Test getGraphicDivider.
   */
  public function testGetGraphicDivider() {
    $svg = $this->themeConfiguratorParser->getGraphicDivider();

    $expected = '<svg xmlns="http://www.w3.org/2000/svg"/>';

    $this->assertXmlStringEqualsXmlString($expected, (string) $svg);
  }

  /**
   * Test getBrandShapeWithoutFill.
   */
  public function testGetBrandShapeWithoutFill() {
    $svg = $this->themeConfiguratorParser->getBrandShapeWithoutFill();

    $expected = '<svg xmlns="http://www.w3.org/2000/svg"/>';

    $this->assertXmlStringEqualsXmlString($expected, (string) $svg);
  }

}

/**
 * ThemeConfiguratorParser uses file_create_url().
 */
namespace Drupal\mars_common;

if (!function_exists('Drupal\mars_common\file_create_url')) {

  /**
   * Stub for drupal file_create_url function.
   *
   * @param string $uri
   *   The URI to a file for which we need an external URL, or the path to a
   *   shipped file.
   *
   * @return string
   *   Result.
   */
  function file_create_url($uri) {
    return 'http://example.com/root/mock.file';
  }

}
