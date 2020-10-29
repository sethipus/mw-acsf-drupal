<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\Plugin\Block\CarouselBlock;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\ThemeConfiguratorParser;

/**
 * @coversDefaultClass \Drupal\mars_common\Plugin\Block\CarouselBlock
 * @group mars
 * @group mars_common
 */
class CarouselBlockTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_common\Plugin\Block\CarouselBlock
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
   * @var \Drupal\mars_common\ThemeConfiguratorParser|\PHPUnit\Framework\MockObject\MockObject
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
      'carousel_label' => 'Carousel',
    ];
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    $this->block = new CarouselBlock(
      $this->configuration,
      'list_block',
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
      'list_block',
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
    $config_form = $this->block->buildConfigurationForm([], $this->formStateMock);
    $this->assertArrayHasKey('carousel_label', $config_form);
  }

  /**
   * Test building block.
   */
  public function testShouldBuildWhenListItem() {
    $this->block->setConfiguration([
      'carousel_label' => 'Carousel',
      'carousel' => [
        [
          'item_type' => 'image',
          'description' => 'Some carousel description',
          'image' => 'image_id',
        ],
      ],
    ]);

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
      ->method('getFileWithId')
      ->willReturn('content');

    $build = $this->block->build();
    $this->assertEquals('carousel_component', $build['#theme']);
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
