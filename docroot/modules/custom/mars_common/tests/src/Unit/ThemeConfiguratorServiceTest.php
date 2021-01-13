<?php

namespace Drupal\Tests\mars_common\Unit;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Image\ImageFactory;
use Drupal\file\Entity\File;
use Drupal\mars_common\ThemeConfiguratorService;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Unit tests for ThemeConfiguratorServiceTest class.
 */
class ThemeConfiguratorServiceTest extends UnitTestCase {

  /**
   * Theme configurator service.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorService
   */
  protected $themeConfiguratorService;

  /**
   * Image Factory mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Image\ImageFactory
   */
  protected $imageFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Extension\ModuleHandler
   */
  private $moduleHandlerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\File\FileSystemInterface
   */
  private $fileSystemMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactoryMock;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Theme configurator form array.
   */
  const FORM = [
    'settings' => [
      'social' => [],
    ],
    'social' => [
      '0' => [
        'icon' => 'icon.png',
        'link' => 'http://example.com',
        'name' => 'Example social',
      ],
    ],
  ];

  /**
   * Mocked config object.
   *
   * @var \Drupal\Core\Config\Config|\Prophecy\Prophecy\ObjectProphecy
   */
  private $configMock;

  /**
   * Array holding the mocked config values.
   *
   * @var array
   */
  private $configValues;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $this->themeConfiguratorService = new ThemeConfiguratorService(
      $this->imageFactoryMock,
      $this->moduleHandlerMock,
      $this->fileSystemMock,
      $this->configFactoryMock,
      $this->entityTypeManagerMock,
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->imageFactoryMock = $this->createMock(ImageFactory::class);
    $this->moduleHandlerMock = $this->createMock(ModuleHandler::class);
    $this->fileSystemMock = $this->createMock(FileSystemInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
  }

  /**
   * Test method getThemeConfiguratorForm.
   */
  public function testGetThemeConfiguratorForm() {
    $form = [];
    $form_state = (new FormState())->setValues([
      'test' => 'test date',
    ]);
    $storage = [
      'social' => [
        '0' => [
          'icon' => 'icon.png',
          'link' => 'http://example.com',
          'name' => 'Example social',
        ],
      ],
    ];
    $form_state->setStorage($storage);
    $theme_configurator_form = $this->themeConfiguratorService->getThemeConfiguratorForm($form, $form_state);
    $this->assertArrayHasKey('product_rating_and_reviews', $theme_configurator_form);
    $this->assertArrayHasKey('#validate', $theme_configurator_form);
    $this->assertArrayHasKey('#submit', $theme_configurator_form);
  }

  /**
   * Test method themeSettingsAjaxAddSocial.
   */
  public function testThemeSettingsAjaxAddSocial() {
    $form_state = (new FormState())->setValues([
      'test' => 'test date',
    ]);
    $result = $this->themeConfiguratorService->themeSettingsAjaxAddSocial(static::FORM, $form_state);

    $this->assertArrayEquals([
      '0' => [
        'icon' => 'icon.png',
        'link' => 'http://example.com',
        'name' => 'Example social',
      ],
    ], $result);
  }

  /**
   * Test method processImageWidget.
   */
  public function testProcessImageWidget() {
    $fileMock = $this->getMockBuilder(File::class)
      ->disableOriginalConstructor()
      ->getMock();

    $fileMock->expects($this->any())
      ->method('getFileUri')
      ->willReturn('drupal.png');
    $element = [
      '#array_parents' => ['submit'],
      'fids' => [
        '#value' => [
          '0' => '0',
          '1' => '1',
        ],
      ],
      '#files' => [
        '0' => $fileMock,
      ],
      '#preview_image_style' => 'medium',
      '#value' => [
        'width' => '250',
        'height' => '250',
      ],
    ];
    $form_state = (new FormState())->setValues([
      'test' => 'test date',
    ]);
    $complete_form = [];
    $process_image_widget = $this->themeConfiguratorService->processImageWidget($element, $form_state, $complete_form);
    $this->assertArrayHasKey('#array_parents', $process_image_widget);
    $this->assertArrayHasKey('width', $process_image_widget);
    $this->assertArrayHasKey('height', $process_image_widget);
    $this->assertArrayHasKey('preview', $process_image_widget);
  }

  /**
   * Test method processImageWidget.
   */
  public function testThemeSettingsAjaxRemoveSocial() {
    $form_state = (new FormState())->setValues([
      'test' => 'test date',
    ]);
    $result = $this->themeConfiguratorService->themeSettingsAjaxRemoveSocial(static::FORM, $form_state);

    $this->assertArrayEquals([
      '0' => [
        'icon' => 'icon.png',
        'link' => 'http://example.com',
        'name' => 'Example social',
      ],
    ], $result);
  }

  /**
   * Test method formSystemThemeSettingsValidate.
   */
  public function testFormSystemThemeSettingsValidate() {
    $form_state = (new FormState())->setValues([
      'test' => 'test date',
    ]);
    $form = static::FORM;
    $result = $this->themeConfiguratorService->formSystemThemeSettingsValidate($form, $form_state);
    $this->assertEmpty($result);
  }

  /**
   * Test method formSystemThemeSettingsSubmit.
   */
  public function testFormSystemThemeSettingsSubmit() {

    $fileMock = $this->getMockBuilder(File::class)
      ->disableOriginalConstructor()
      ->getMock();

    $fileMock->expects($this->any())
      ->method('getFileUri')
      ->willReturn('drupal.png');

    $form_state = (new FormState())->setValues([
      'test' => 'test date',
      'headline_font' => $fileMock,
    ]);

    $form_state->setUserInput(
      ['form_id' => 'form_id_test'],
    );
    $form = static::FORM;

    $this->configValues = [];
    $this->configHasValue('default_scheme', 'public');
    $this->configMock = $this->createMock(Config::class);
    $this->configMock
      ->method('get')
      ->willReturnCallback(function ($name) {
        if (!isset($this->configValues[$name])) {
          return NULL;
        }
        return $this->configValues[$name];
      });

    $this->configFactoryMock
      ->method('get')
      ->with('system.file')
      ->willReturn(
        $this->configMock
      );

    $result = $this->themeConfiguratorService->formSystemThemeSettingsSubmit($form, $form_state);

    $this->assertEmpty($result);
  }

  /**
   * Sets a config value for the mocked config.
   *
   * @param string $config_key
   *   The key of the config value.
   * @param mixed $value
   *   The value.
   */
  private function configHasValue(string $config_key, $value) {
    $this->configValues[$config_key] = $value;
  }

}

/**
 * ThemeConfiguratorService uses theme_get_setting().
 */
namespace Drupal\mars_common;

if (!function_exists('Drupal\mars_common\theme_get_setting')) {

  /**
   * Retrieves a setting for the current theme or for a given theme.
   *
   * The final setting is obtained from the last value found in the following
   * sources:
   * - the saved values from the global theme settings form
   * - the saved values from the theme's settings form
   * To only retrieve the default global theme setting, an empty string should
   * be given for $theme.
   *
   * @param string $setting_name
   *   The name of the setting to be retrieved.
   * @param string $theme
   *   The name of a given theme; defaults to the current theme.
   *
   * @return array
   *   The value of the requested setting, NULL if the setting does not exist.
   */
  function theme_get_setting($setting_name, $theme = NULL) {
    return ['test'];
  }

}

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
    return 'http://example.com/root/drupal.png';
  }

}
