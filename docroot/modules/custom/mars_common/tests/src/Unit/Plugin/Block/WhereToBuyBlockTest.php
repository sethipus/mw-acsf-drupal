<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_common\Plugin\Block\WhereToBuyBlock;
use Drupal\mars_product\Plugin\Block\PdpHeroBlock;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_common\Plugin\Block\WhereToBuyBlock
 * @group mars
 * @group mars_common
 */
class WhereToBuyBlockTest extends UnitTestCase {

  private const CONFIGURATION = [
    'id' => 'where_to_buy_block',
    'label' => 'MARS: Where To Buy',
    'provider' => 'mars_common',
    'label_display' => '1',
    'widget_id' => 'test_widget_id',
    'context_mapping' => [],
    'commerce_vendor' => PdpHeroBlock::VENDOR_PRICE_SPIDER,
  ];

  private const DEFINITION = [
    'id' => 'where_to_buy_block',
    'provider' => 'mars_common',
    'admin_label' => 'test',
  ];

  private const PLUGIN_ID = 'where_to_buy_block';

  /**
   * System under test.
   *
   * @var \Drupal\mars_common\Plugin\Block\WhereToBuyBlock
   */
  private $block;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Language\LanguageInterface
   */
  private $languageMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translationMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface
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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ImmutableConfig
   */
  private $wtbGlobalConfig;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_media\MediaHelper
   */
  private $mediaHelperMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityStorageInterface
   */
  private $entityStorageMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\node\NodeInterface
   */
  private $nodeMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\EntityReferenceFieldItemListInterface
   */
  private $fieldItemListMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->block = new WhereToBuyBlock(
      self::CONFIGURATION,
      self::PLUGIN_ID,
      self::DEFINITION,
      $this->languageManagerMock,
      $this->entityTypeManagerMock,
      $this->mediaHelperMock,
      $this->wtbGlobalConfig,
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(4))
      ->method('get')
      ->willReturnMap(
        [
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configFactoryMock,
          ],
          [
            'language_manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->languageManagerMock,
          ],
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'mars_media.media_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->mediaHelperMock,
          ],
        ]
      );

    $this->configFactoryMock
      ->method('get')
      ->with('mars_product.wtb.settings')
      ->willReturn($this->wtbGlobalConfig);

    $this->block::create(
      $this->containerMock,
      self::CONFIGURATION,
      self::PLUGIN_ID,
      self::DEFINITION
    );
  }

  /**
   * Test.
   */
  public function testShouldBuildConfigurationForm() {
    $form = [];

    $this->containerMock
      ->expects($this->exactly(1))
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

    $form = $this->block->buildConfigurationForm(
      $form,
      $this->formStateMock
    );
    $this->assertIsArray($form['widget_id']);
  }

  /**
   * Test.
   */
  public function testShouldBlockSubmit() {
    $form = [];

    $this->formStateMock
      ->expects($this->once())
      ->method('getValues')
      ->willReturn(self::CONFIGURATION);

    $this->block->blockSubmit(
      $form,
      $this->formStateMock
    );
  }

  /**
   * Test.
   */
  public function testShouldDefaultConfiguration() {
    $default_conf = $this->block->defaultConfiguration();
    $this->assertSame(
      self::CONFIGURATION['widget_id'],
      $default_conf['widget_id']
    );
  }

  /**
   * Test.
   */
  public function testShouldBuildWhenPriceSpider() {
    $this->block->setConfiguration([
      'widget_id' => 'test_widget_id',
      'commerce_vendor' => PdpHeroBlock::VENDOR_PRICE_SPIDER,
    ]);

    $build = $this->block->build();
    $this->assertArrayHasKey('#theme', $build);
    $this->assertArrayHasKey('#widget_id', $build);
    $this->assertArrayHasKey('#commerce_vendor', $build);
    $this->assertArrayHasKey('#product_sku', $build);
  }

  /**
   * Test.
   */
  public function testShouldBuildWhenCommerceConnector() {
    $this->block->setConfiguration([
      'id' => 'where_to_buy_block',
      'label' => 'MARS: Where To Buy',
      'provider' => 'mars_common',
      'label_display' => '1',
      'widget_id' => 'test_widget_id',
      'context_mapping' => [],
    ]);

    $this->wtbGlobalConfig
      ->method('get')
      ->with('commerce_vendor')
      ->willReturn(PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR);

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('loadByProperties')
      ->willReturn([$this->nodeMock]);

    $this->nodeMock
      ->expects($this->any())
      ->method('id')
      ->willReturn('id');

    $this->nodeMock
      ->expects($this->once())
      ->method('label')
      ->willReturn('label');

    $this->nodeMock->target_id = '123';
    $this->nodeMock->value = 'value';

    $this->nodeMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->fieldItemListMock);

    $this->fieldItemListMock
      ->expects($this->once())
      ->method('referencedEntities')
      ->willReturn([
        $this->nodeMock,
      ]);

    $this->mediaHelperMock
      ->expects($this->once())
      ->method('getMediaParametersById')
      ->willReturn([
        'error' => TRUE,
        'src' => 'src',
        'alt' => 'alt',
      ]);

    $this->languageManagerMock
      ->expects($this->once())
      ->method('getCurrentLanguage')
      ->willReturn($this->languageMock);

    $this->languageMock
      ->expects($this->once())
      ->method('getId')
      ->willReturn('en');

    $this->block->build();
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->languageManagerMock = $this->createMock(LanguageManagerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->wtbGlobalConfig = $this->createMock(ImmutableConfig::class);
    $this->languageMock = $this->createMock(LanguageInterface::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->nodeMock = $this->createMock(NodeInterface::class);
    $this->fieldItemListMock = $this->createMock(EntityReferenceFieldItemListInterface::class);
  }

}
