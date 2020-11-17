<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\Plugin\Block\FlexibleFramerBlock;
use Drupal\mars_common\SVG\SVG;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * @coversDefaultClass \Drupal\mars_common\Plugin\Block\FlexibleFramerBlock
 * @group mars
 * @group mars_common
 */
class FlexibleFramerBlockTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_common\Plugin\Block\FlexibleFramerBlock
   */
  private $block;

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
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\MediaHelper
   */
  private $mediaHelperMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\ThemeConfiguratorParser
   */
  private $themeConfiguratorParserMock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->configuration = [
      'form_url' => 'http',
      'items' => [
        [
          'title' => 'title',
          'item_image' => 'media:186',
          'cta' => ['title' => 'title', 'url' => 'https://test.com'],
          'description' => 'desc',
        ],
      ],
      'with_cta' => 1,
      'with_image' => 1,
      'with_description' => 1,
    ];
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    $this->block = new FlexibleFramerBlock(
      $this->configuration,
      'flexible_framer_block',
      $definitions,
      $this->mediaHelperMock,
      $this->themeConfiguratorParserMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap(
        [
          [
            'mars_common.media_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->mediaHelperMock,
          ],
          [
            'mars_common.theme_configurator_parser',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->themeConfiguratorParserMock,
          ],
        ]
      );

    $this->block::create(
      $this->containerMock,
      $this->configuration,
      'flexible_framer_block',
      [
        'provider'    => 'test',
        'admin_label' => 'test',
      ]
    );
  }

  /**
   * Test configuration form.
   */
  public function testShouldBuildConfigurationForm() {
    $this->formStateMock
      ->expects($this->any())
      ->method('get')
      ->willReturn(NULL);

    $this->formStateMock
      ->expects($this->any())
      ->method('set');

    $config_form = $this->block->buildConfigurationForm([], $this->formStateMock);
    $this->assertArrayHasKey('title', $config_form);
  }

  /**
   * Test configuration form.
   */
  public function testShouldBuildConfigurationFormWhenRemove() {
    $this->formStateMock
      ->expects($this->any())
      ->method('get')
      ->willReturn([1]);

    $this->formStateMock
      ->expects($this->once())
      ->method('getTriggeringElement')
      ->willReturn([
        '#parents' => [0, 0, 0, 'remove_item'],
      ]);

    $this->formStateMock
      ->expects($this->any())
      ->method('set');

    $config_form = $this->block->buildConfigurationForm([], $this->formStateMock);
    $this->assertArrayHasKey('title', $config_form);
  }

  /**
   * Test ajax callback.
   */
  public function testShouldAjaxAddItemCallback() {
    $form = [
      'settings' => ['items' => ['test']],
    ];

    $config_form = $this->block->ajaxAddItemCallback($form, $this->formStateMock);
    $this->assertIsArray($config_form);
  }

  /**
   * Test ajax callback.
   */
  public function testShouldAjaxRemoveItemCallback() {
    $form = [
      'settings' => ['items' => ['test']],
    ];

    $config_form = $this->block->ajaxRemoveItemCallback($form, $this->formStateMock);
    $this->assertIsArray($config_form);
  }

  /**
   * Test ajax callback.
   */
  public function testShouldAddItemSubmitted() {
    $form = [
      'settings' => ['items' => ['test']],
    ];

    $this->formStateMock
      ->expects($this->once())
      ->method('get')
      ->willReturn([1]);

    $this->formStateMock
      ->expects($this->once())
      ->method('set');

    $this->formStateMock
      ->expects($this->once())
      ->method('setRebuild');

    $this->block->addItemSubmitted($form, $this->formStateMock);
  }

  /**
   * Test building block.
   */
  public function testShouldBuild() {
    $this->mediaHelperMock
      ->expects($this->once())
      ->method('getIdFromEntityBrowserSelectValue')
      ->willReturn(123);

    $this->mediaHelperMock
      ->expects($this->once())
      ->method('getMediaParametersById')
      ->willReturn([
        'src' => 'src',
        'alt' => 'alt',
        'title' => 'title',
      ]);

    $this->themeConfiguratorParserMock
      ->expects($this->once())
      ->method('getGraphicDivider');

    $this->themeConfiguratorParserMock
      ->expects($this->once())
      ->method('getBrandBorder2')
      ->willReturn(new SVG('<svg xmlns="http://www.w3.org/2000/svg" />', 'id'));

    $this->themeConfiguratorParserMock
      ->expects($this->once())
      ->method('getSettingValue')
      ->willReturn('value');

    $build = $this->block->build();
    $this->assertEquals('flexible_framer_block', $build['#theme']);
  }

  /**
   * Test building block.
   */
  public function testShouldBlockSubmit() {
    $form_data = [];

    $this->formStateMock
      ->expects($this->once())
      ->method('getValues')
      ->willReturn([]);

    $this->block->blockSubmit(
      $form_data,
      $this->formStateMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
  }

}
namespace Drupal\mars_common\Plugin\Block;

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
  return 'url';
}
