<?php

namespace Drupal\Tests\mars_product\Unit\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Url;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\MediaHelper;
use Drupal\mars_media\SVG\SVG;
use Drupal\mars_product\Plugin\Block\ProductContentPairUpBlock;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;

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
    'entity_priority' => ProductContentPairUpBlock::ARTICLE_OR_RECIPE_FIRST,
    'lead_card_eyebrow' => 'Master Card Eyebrow',
    'lead_card_title' => 'Master Card Title',
    'cta_link_text' => 'CTA Link text',
    'supporting_card_eyebrow' => 'Supporting Card Eyebrow',
    'background' => 'media:1',
    'select_background_color' => '',
  ];

  private const DEFINITION = [
    'id' => 'product_content_pair_up_block',
    'provider' => 'mars_product',
    'admin_label' => 'test',
  ];

  private const PLUGIN_ID = 'product_content_pair_up_block';
  private const BACKGROUND_SOURCE = 'test_source';
  private const CTA_LINK_URL = 'https://test.test';

  /**
   * System under test.
   *
   * @var \Drupal\mars_product\Plugin\Block\ProductContentPairUpBlock
   */
  private $block;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManagerMock;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_media\MediaHelper
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
   * Immutable config mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ImmutableConfig
   */
  private $immutableConfig;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();

    $this->configMock
    ->method('getEditable')
    ->with('mars_common.character_limit_page')
    ->willReturn($this->immutableConfig);

    \Drupal::setContainer($this->containerMock);
    $this->block = new ProductContentPairUpBlock(
      self::CONFIGURATION,
      self::PLUGIN_ID,
      self::DEFINITION,
      $this->languageManagerMock,
      $this->configMock,
      $this->entityTypeManagerMock,
      $this->themeConfiguratorParserMock,
      $this->languageHelperMock,
      $this->mediaHelperMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(6))
      ->method('get')
      ->willReturnMap(
        [
          [
            'language_manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->languageManagerMock,
          ],
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
            'mars_media.media_helper',
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
    $this->assertCount(17, $config_form);
    $this->assertArrayHasKey('title', $config_form);
    $this->assertArrayHasKey('entity_priority', $config_form);
    $this->assertArrayHasKey('article_recipe', $config_form);
    $this->assertArrayHasKey('product', $config_form);
    $this->assertArrayHasKey('lead_card_eyebrow', $config_form);
    $this->assertArrayHasKey('lead_card_title', $config_form);
    $this->assertArrayHasKey('cta_link_text', $config_form);
    $this->assertArrayHasKey('supporting_card_eyebrow', $config_form);
    $this->assertArrayHasKey('background', $config_form);
    $this->assertArrayHasKey('override_text_color', $config_form);
    $this->assertArrayHasKey('use_dark_overlay', $config_form);
    $this->assertIsArray($config_form['background']);
  }

  /**
   * Test submitting block.
   */
  public function testShouldBlockSubmit() {
    $form_data = [];

    $this->formStateMock
      ->expects($this->exactly(12))
      ->method('getValue')
      ->willReturn('', NULL);

    $this->block->blockSubmit(
      $form_data,
      $this->formStateMock
    );
  }

  /**
   * Test building block when main entity was provided.
   */
  public function testShouldBuildProperlyWithMainEntity() {
    $configuration = $this->block->getConfiguration();
    $configuration['article_recipe'] = '123';
    // Background was not added in the entity browser form.
    unset($configuration['background']);
    $this->block->setConfiguration($configuration);

    $build = $this->block->build();

    $this->assertArrayHasKey('#lead_card_entity', $build);
    $this->assertArrayHasKey('#lead_card_eyebrow', $build);
    $this->assertArrayHasKey('#lead_card_title', $build);
    $this->assertEquals(self::CTA_LINK_URL, $build['#cta_link_url']);
    $this->assertArrayHasKey('#cta_link_text', $build);
    $this->assertEquals(self::BACKGROUND_SOURCE, $build['#background']);
    $this->assertEquals('product_content_pair_up_block', $build['#theme']);
    // Supporting card was not added on purpose.
    $this->assertArrayNotHasKey('#supporting_card_entity', $build);
    $this->assertArrayNotHasKey('#supporting_card_entity_view', $build);
  }

  /**
   * Test building block when supporting entity was provided.
   */
  public function testShouldBuildProperlyWithSupportingEntity() {
    $configuration = $this->block->getConfiguration();
    $configuration['entity_priority'] = ProductContentPairUpBlock::PRODUCT_FIRST;
    $configuration['article_recipe'] = '123';
    $this->block->setConfiguration($configuration);

    $build = $this->block->build();

    $this->assertArrayHasKey('#supporting_card_entity', $build);
    $this->assertArrayHasKey('#supporting_card_entity_view', $build);
    $this->assertEquals(self::BACKGROUND_SOURCE, $build['#background']);
    $this->assertEquals('product_content_pair_up_block', $build['#theme']);
    // Main card was not added on purpose.
    $this->assertArrayNotHasKey('#lead_card_entity', $build);
    $this->assertArrayNotHasKey('#lead_card_eyebrow', $build);
    $this->assertArrayNotHasKey('#lead_card_title', $build);
    $this->assertArrayNotHasKey('#cta_link_text', $build);
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $nodeMock = $this->createNodeMock();
    $nodeStorageMock = $this->createMock(EntityStorageInterface::class);
    $nodeStorageMock
      ->method('load')
      ->willReturn($nodeMock);

    $viewbuilderMock = $this->createMock(EntityViewBuilderInterface::class);
    $viewbuilderMock
      ->method('view')
      ->willReturn([]);

    $this->entityTypeManagerMock = $this->createMock(EntityTypeManager::class);
    $this->entityTypeManagerMock
      ->method('getStorage')
      ->willReturn($nodeStorageMock);
    $this->entityTypeManagerMock
      ->method('getViewBuilder')
      ->willReturn($viewbuilderMock);

    $this->configMock = $this->createMock(ConfigFactoryInterface::class);
    $this->immutableConfig = $this->createMock(ImmutableConfig::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);

    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
    $this->languageHelperMock->method('translate')
      ->will(
        $this->returnCallback(
          function ($arg) {
            return $arg;
          })
      );
    $languageMock = $this->createMock(Language::class);
    $languageMock
      ->expects($this->any())
      ->method('getId')
      ->willReturn('en');
    $this->languageManagerMock = $this->createMock(LanguageManagerInterface::class);
    $this->languageManagerMock
      ->expects($this->any())
      ->method('getCurrentLanguage')
      ->willReturn($languageMock);

    $this->themeConfiguratorParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->themeConfiguratorParserMock
      ->method('getGraphicDivider')
      ->willReturn(new SVG('<svg xmlns="http://www.w3.org/2000/svg" />', 'id'));
    $this->themeConfiguratorParserMock
      ->method('getBrandShapeWithoutFill')
      ->willReturn(new SVG('<svg xmlns="http://www.w3.org/2000/svg" />', 'id'));

    $this->mediaHelperMock = $this->createMock(MediaHelper::class);
    $this->mediaHelperMock->method('getEntityMainMediaId')
      ->willReturn('1');
    $this->mediaHelperMock->method('getMediaParametersById')
      ->willReturn(
        [
          'src' => self::BACKGROUND_SOURCE,
          'title' => 'test_title',
          'alt' => 'test_image_alt',
        ]
      );
    $this->mediaHelperMock
      ->method('getIdFromEntityBrowserSelectValue')
      ->willReturn(1);
  }

  /**
   * Mock recipe node.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   Mock node object.
   */
  private function createNodeMock() {
    $urlMock = $this->getMockBuilder(Url::class)
      ->disableOriginalConstructor()
      ->getMock();

    $urlMock->expects($this->any())
      ->method('toString')
      ->willReturn($urlMock)
      ->willReturn(self::CTA_LINK_URL);

    $node = $this->getMockBuilder(NodeInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $node->expects($this->any())
      ->method('bundle')
      ->willReturn('product');

    $node->expects($this->any())
      ->method('getTranslation')
      ->willReturn($node);

    $node->expects($this->any())
      ->method('toUrl')
      ->willReturn($urlMock);

    return $node;
  }

}
