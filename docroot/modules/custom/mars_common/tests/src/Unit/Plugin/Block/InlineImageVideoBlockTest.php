<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\mars_common\MediaHelper;
use Drupal\mars_common\Plugin\Block\InlineImageVideoBlock;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * @coversDefaultClass \Drupal\mars_common\Plugin\Block\InlineImageVideoBlock
 * @group mars
 * @group mars_common
 */
class InlineImageVideoBlockTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_common\Plugin\Block\InlineImageVideoBlock
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
    ];
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    $this->block = new InlineImageVideoBlock(
      $this->configuration,
      'inline_image_video_block',
      $definitions,
      $this->mediaHelperMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(1))
      ->method('get')
      ->willReturnMap(
        [
          [
            'mars_common.media_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->mediaHelperMock,
          ],
        ]
      );

    $this->block::create(
      $this->containerMock,
      $this->configuration,
      'inline_image_video_block',
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
    $this->assertArrayHasKey('title', $config_form);
  }

  /**
   * Test building block.
   */
  public function testShouldBuildWhenImage() {
    $this->block->setConfiguration([
      'image' => 'image_id',
      'title' => 'title',
      'description' => 'description',
      'svg_asset' => 1,
      'block_content_type' => 'image',
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

    $build = $this->block->build();
    $this->assertEquals('inline_image_video_block', $build['#theme']);
  }

  /**
   * Test building block.
   */
  public function testShouldBuildWhenVideo() {
    $this->block->setConfiguration([
      'video' => 'video_id',
      'title' => 'title',
      'description' => 'description',
      'svg_asset' => 1,
      'block_content_type' => 'video',
    ]);

    $this->mediaHelperMock
      ->expects($this->once())
      ->method('getIdFromEntityBrowserSelectValue')
      ->willReturn(12);

    $this->mediaHelperMock
      ->expects($this->once())
      ->method('getMediaParametersById')
      ->willReturn([
        'src' => 'src',
      ]);

    $build = $this->block->build();
    $this->assertEquals('inline_image_video_block', $build['#theme']);
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
  }

}
