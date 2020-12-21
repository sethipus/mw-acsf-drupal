<?php

namespace Drupal\Tests\mars_product\Unit\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\MediaHelper;
use Drupal\mars_product\Plugin\Block\ProductContentPairUpBlock;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\ThemeConfiguratorParser;

/**
 * @coversDefaultClass \Drupal\mars_product\Plugin\Block\ProductContentPairUpBlock
 * @group mars
 * @group mars_product
 */
class ProductContentPairUpBlockTest extends UnitTestCase {

  private const CONFIGURATION = [
    'id' => 'product_content_pair_up_block',
    'provider' => 'mars_product',
    'title' => 'Title',
    'entity_priority' => 'article_first',
    'lead_card_eyebrow' => 'Master Card Eyebrow',
    'lead_card_title' => 'Master Card Title',
    'cta_link_text' => 'CTA Link text',
    'supporting_card_eyebrow' => 'Supporting Card Eyebrow',
    'background' => 'media:186',
  ];

  private const DEFINITION = [
    'id' => 'product_content_pair_up_block',
    'provider' => 'mars_product',
    'admin_label' => 'test',
  ];

  private const PLUGIN_ID = 'product_content_pair_up_block';

  /**
   * System under test.
   *
   * @var \Drupal\mars_product\Plugin\Block\ProductContentPairUpBlock
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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\LanguageHelper
   */
  private $languageHelperMock;

  /**
   * Mock.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser|\PHPUnit\Framework\MockObject\MockObject
   */
  private $themeConfiguratorParserMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $configMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|\PHPUnit\Framework\MockObject\MockObject
   */
  private $immutableConfigMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $nodeStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->block = new ProductContentPairUpBlock(
      self::CONFIGURATION,
      self::PLUGIN_ID,
      self::DEFINITION,
      $this->configMock,
      $this->entityTypeManagerMock,
      $this->themeConfiguratorParserMock,
      $this->languageHelperMock,
      $this->mediaHelperMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManager::class);
    $this->nodeStorage = $this->createMock(EntityStorageInterface::class);
    $this->immutableConfigMock = $this->createMock(ImmutableConfig::class);
    $this->configMock = $this->createMock(ConfigFactoryInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(5))
      ->method('get')
      ->willReturnMap(
        [
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configMock,
          ],
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'mars_common.theme_configurator_parser',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->themeConfiguratorParserMock,
          ],
          [
            'mars_common.language_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->languageHelperMock,
          ],
          [
            'mars_common.media_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->mediaHelperMock,
          ],
        ]
      );
    $this->block::create(
      $this->containerMock,
      self::CONFIGURATION,
      self::PLUGIN_ID,
      self::DEFINITION
    );
  }

  /**
   * Test configuration form.
   */
  public function testBuildConfigurationFormProperly() {
    $config_form = $this->block->buildConfigurationForm([], $this->formStateMock);
    $this->assertCount(14, $config_form);
    $this->assertArrayHasKey('entity_priority', $config_form);
  }

  /**
   * Test submitting block.
   */
  public function testShouldBlockSubmit() {
    $form_data = [];

    $this->formStateMock
      ->expects($this->exactly(9))
      ->method('getValue')
      ->willReturn('');

    $this->block->blockSubmit(
      $form_data,
      $this->formStateMock
    );
  }

}
