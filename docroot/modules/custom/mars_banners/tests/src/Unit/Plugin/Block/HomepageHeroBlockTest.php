<?php

namespace Drupal\Tests\mars_banners\Unit\Plugin\Block;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mars_banners\Plugin\Block\HomepageHeroBlock;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_banners\Plugin\Block\HomepageHeroBlock
 * @group mars
 * @group mars_banners
 */
class HomepageHeroBlockTest extends UnitTestCase {

  private const TEST_CONFIGURATION = [
    'id' => 'homepage_hero_block',
    'label' => 'Homepage Hero block',
    'provider' => 'mars_banners',
    'title' => [
      'url' => '',
      'label' => 'Homepage Hero block',
    ],
    'block_type' => 'video',
    'label_display' => '0',
    'eyebrow' => 'test eyebrow',
    'cta' => [
      'url' => 'https://test.test',
      'title' => 'Explore',
    ],
    'background_video' => 'background video',
    'background_default' => 'background_default',
    'background_image' => [0 => 'background_default'],
    'card' => []
  ];

  private const TEST_DEFENITION = [
    'provider' => 'mars_banners',
    'admin_label' => 'admin_label',
  ];

  /**
   * System under test.
   *
   * @var \Drupal\mars_banners\Plugin\Block\HomepageHeroBlock
   */
  private $homepageBlock;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translationMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManagerMock;

  /**
   * File storage.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorageMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactoryMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->withConsecutive(
        [$this->equalTo('file')]
      )
      ->will($this->onConsecutiveCalls($this->fileStorageMock));

    $this->homepageBlock = new HomepageHeroBlock(
      self::TEST_CONFIGURATION,
      'homepage_hero_block',
      self::TEST_DEFENITION,
      $this->entityTypeManagerMock,
      $this->configFactoryMock
    );
  }

  /**
   * Test.
   */
  public function testShouldBuild() {
    $block_build = $this->homepageBlock->build();
    $this->assertSame(
      'Homepage Hero block',
      $block_build['#title_label']
    );
    $this->assertIsArray($block_build);
  }

  /**
   * Test.
   */
  public function testShouldBuildConfigurationForm() {
    $form_array = [];

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'string_translation',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->translationMock,
          ],
        ]
      );

    $configMock = $this->getMockBuilder(stdClass::class)
      ->setMethods(['get'])
      ->getMock();

    $this->configFactoryMock
      ->expects($this->once())
      ->method('get')
      ->with('emulsifymars.settings')
      ->willReturn(
        $configMock
      );

    $configMock
      ->expects($this->once())
      ->method('get')
      ->willReturn([]);

    $block_form = $this->homepageBlock->buildConfigurationForm(
      $form_array,
      $this->formStateMock
    );
    $this->assertSame(
      'textfield',
      $block_form['eyebrow']['#type']
    );
    $this->assertIsArray($block_form);
  }

  /**
   * Test.
   */
  public function testShouldBlockSubmit() {
    $form_data = [];

    $this->formStateMock
      ->expects($this->once())
      ->method('getValues')
      ->willReturn(self::TEST_CONFIGURATION);

    $this->homepageBlock->blockSubmit(
      $form_data,
      $this->formStateMock
    );
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->fileStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
  }

}
