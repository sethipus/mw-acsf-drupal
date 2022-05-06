<?php

namespace Drupal\Tests\mars_recommendations\Unit\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\layout_builder\Form\ConfigureBlockFormBase;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_media\SVG\SVG;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_recommendations\Plugin\Block\RecommendationsModuleBlock;
use Drupal\mars_recommendations\RecommendationsLogicPluginInterface;
use Drupal\mars_recommendations\RecommendationsService;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the Recommendations Module block core functionality.
 *
 * @covers \Drupal\mars_recommendations\Plugin\Block\RecommendationsModuleBlock
 */
class RecommendationsModuleBlockTest extends UnitTestCase {

  use StringTranslationTrait;

  const TEST_PLUGIN_CONFIGURATION_FORM = [
    'element1' => [
      '#type' => 'item',
      '#markup' => 'Test markup',
    ],
    'element2' => [
      '#type' => 'textfield',
      '#title' => 'Items criteria',
      '#required' => TRUE,
    ],
  ];

  private const TEST_FORM_SETTINGS = [
    '#parents' => [
      'test',
    ],
    'population' => [
      'configuration' => [
        'subform' => [
          '#parents' => [
            'test',
          ],
        ],
      ],
    ],
  ];

  private const TEST_FORM = [
    'settings' => self::TEST_FORM_SETTINGS,
  ];

  /**
   * Recommendations Module block's default plugin definitions.
   *
   * @var array
   */
  private $defaultDefinitions = [
    'provider' => 'test',
    'admin_label' => 'test',
  ];

  /**
   * Recommendation Module block's default configuration.
   *
   * @var array
   */
  private $defaultConfiguration = [];

  /**
   * Form object stub.
   *
   * @var \Drupal\Core\Form\FormInterface
   */
  private $formObjectStub;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Form\FormStateInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $formStateMock;

  /**
   * Config factory mock.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactoryMock;

  /**
   * Immutable config mock.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $immutableConfigMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();
    $container->set('string_translation', $this->getStringTranslationStub());
    $container->set('config.factory', $this->createConfigFactoryMock());
    $container->set('mars_recommendations.recommendations_service', $this->createRecommendationsServiceMock());
    $container->set('mars_common.theme_configurator_parser', $this->createThemeConfigurationParserMock());
    $container->set('mars_common.language_helper', $this->createLanguageHelperMock());
    \Drupal::setContainer($container);

    $this->formObjectStub = new class extends FormBase {

      /**
       * {@inheritdoc}
       */
      public function getFormId() {
        return 'test_form_id';
      }

      /**
       * {@inheritdoc}
       */
      public function buildForm(array $form, FormStateInterface $form_state) {
        return [];
      }

      /**
       * {@inheritdoc}
       */
      public function submitForm(array &$form, FormStateInterface $form_state) {}

    };

    $this->formStateMock = $this->createMock(FormStateInterface::class);
  }

  /**
   * Test block build properly.
   */
  public function testBuildImProperly() {
    $block = RecommendationsModuleBlock::create(
      \Drupal::getContainer(),
      [],
      'recommendations_module',
      $this->defaultDefinitions
    );

    $build = $block->build();
    $this->assertEquals([], $build);
  }

  /**
   * Test block build properly.
   */
  public function testBuildProperly() {
    $block = RecommendationsModuleBlock::create(
      \Drupal::getContainer(),
      [
        'population_plugin_id' => 'test_plugin_1',
        'title' => 'test_title',
      ],
      'recommendations_module',
      $this->defaultDefinitions
    );

    // Mock node context.
    $node_mock = $this->getMockBuilder(NodeInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node_context_mock = $this->getMockBuilder(Context::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node_context_mock
      ->expects($this->once())
      ->method('getContextValue')
      ->willReturn($node_mock);

    $block->setContext('node', $node_context_mock);

    $build = $block->build();

    $this->assertEquals('recommendations_module_block', $build['#theme']);
    $this->assertEquals('test_title', $build['#title']);
    $this->assertArrayHasKey('#graphic_divider', $build);
    $this->assertArrayHasKey('#brand_border', $build);
    $this->assertArrayHasKey('#recommended_items', $build);
  }

  /**
   * Test default configuration for Recommendations Module block.
   */
  public function testDefaultConfiguration() {
    $block = RecommendationsModuleBlock::create(
      \Drupal::getContainer(),
      [],
      'recommendations_module',
      $this->defaultDefinitions
    );

    $default_configuration = $block->defaultConfiguration();

    $this->assertIsArray($default_configuration);
    $this->assertArrayHasKey('label_display', $default_configuration);
    $this->assertEquals($default_configuration['label_display'], FALSE);
  }

  /**
   * Tests default configuration form for block adding.
   */
  public function testDefaultBlockBuildConfigurationFormByConf() {
    $block = RecommendationsModuleBlock::create(
      \Drupal::getContainer(),
      [],
      'recommendations_module',
      $this->defaultDefinitions
    );

    $section_storage_mock = $this->createMock(SectionStorageInterface::class);
    $section_storage_mock
      ->expects($this->any())
      ->method('getSection')
      ->willReturn($this->createMock(Section::class));
    $section_storage_mock
      ->expects($this->any())
      ->method('getContextValues')
      ->willReturn([
        'entity' => $this->createMock(EntityInterface::class),
        'display' => 'test',
      ]);

    $this->formStateMock->setFormObject($this->formObjectStub);
    $this->formStateMock
      ->expects($this->any())
      ->method('getFormObject')
      ->willReturn($this->createMock(ConfigureBlockFormBase::class));
    $this->formStateMock
      ->expects($this->any())
      ->method('getBuildInfo')
      ->willReturn([
        'args' => [$section_storage_mock, 0],
      ]);

    $form = $block->buildConfigurationForm([], $this->formStateMock);

    $this->assertIsArray($form);

    $this->assertArrayHasKey('title', $form);
    $this->assertIsArray($form['title']);
    $this->assertEquals($form['title']['#type'], 'textfield');
    $this->assertEquals($form['title']['#title'], $this->t('Title'));
    $this->assertEquals($form['title']['#description'], $this->t('Defaults to <em>More <strong>&lt;Content Type&gt;</strong>s Like This</em>'));
    $this->assertEquals($form['title']['#placeholder'], $this->t('More &lt;Content Type&gt;s Like This'));
    $this->assertEquals($form['title']['#maxlength'], 55);

    $this->assertArrayHasKey('population', $form);
    $this->assertIsArray($form['population']);

    $this->assertArrayHasKey('plugin_id', $form['population']);
    $this->assertIsArray($form['population']['plugin_id']);
    $this->assertEquals($form['population']['plugin_id']['#type'], 'radios');
    $this->assertEquals($form['population']['plugin_id']['#title'], $this->t('Population Logic'));
    $this->assertEquals($form['population']['plugin_id']['#options'], \Drupal::service('mars_recommendations.recommendations_service')->getPopulationLogicOptions());
    $this->assertTrue($form['population']['plugin_id']['#required']);

    $this->assertArrayHasKey('configuration', $form['population']);
    $this->assertIsArray($form['population']['configuration']);
    $this->assertEquals([
      '#type' => 'container',
      '#attributes' => [
        'id' => 'recommendations-population-configuration',
      ],
      'subform' => [],
    ], $form['population']['configuration']);
  }

  /**
   * Tests default configuration form for block adding.
   */
  public function testDefaultBlockBuildConfigurationFormByFormState() {
    $block = RecommendationsModuleBlock::create(
      \Drupal::getContainer(),
      [],
      'recommendations_module',
      $this->defaultDefinitions
    );

    $this->formStateMock->setFormObject($this->formObjectStub);
    $this->formStateMock
      ->expects($this->any())
      ->method('has')
      ->with('population_plugin_id')
      ->willReturn(TRUE);

    $form = $block->buildConfigurationForm([], $this->formStateMock);

    $this->assertIsArray($form);

    $this->assertArrayHasKey('title', $form);
    $this->assertIsArray($form['title']);
    $this->assertEquals($form['title']['#type'], 'textfield');
    $this->assertEquals($form['title']['#title'], $this->t('Title'));
    $this->assertEquals($form['title']['#description'], $this->t('Defaults to <em>More <strong>&lt;Content Type&gt;</strong>s Like This</em>'));
    $this->assertEquals($form['title']['#placeholder'], $this->t('More &lt;Content Type&gt;s Like This'));
    $this->assertEquals($form['title']['#maxlength'], 55);

    $this->assertArrayHasKey('population', $form);
    $this->assertIsArray($form['population']);

    $this->assertArrayHasKey('plugin_id', $form['population']);
    $this->assertIsArray($form['population']['plugin_id']);
    $this->assertEquals($form['population']['plugin_id']['#type'], 'radios');
    $this->assertEquals($form['population']['plugin_id']['#title'], $this->t('Population Logic'));
    $this->assertEquals($form['population']['plugin_id']['#options'], \Drupal::service('mars_recommendations.recommendations_service')->getPopulationLogicOptions());
    $this->assertTrue($form['population']['plugin_id']['#required']);

    $this->assertArrayHasKey('configuration', $form['population']);
    $this->assertIsArray($form['population']['configuration']);
    $this->assertEquals([
      '#type' => 'container',
      '#attributes' => [
        'id' => 'recommendations-population-configuration',
      ],
      'subform' => [],
    ], $form['population']['configuration']);
  }

  /**
   * Tests configured block configuration form.
   */
  public function testConfiguredRecommendationsBlockBuildConfigurationForm() {
    $block = RecommendationsModuleBlock::create(
      \Drupal::getContainer(),
      [
        'population_plugin_id' => 'test_plugin_1',
        'population_plugin_configuration' => [],
      ],
      'recommendations_module',
      $this->defaultDefinitions
    );

    $form_state = new FormState();
    $form_state->setFormObject($this->formObjectStub);
    $form = $block->buildConfigurationForm([], $form_state);

    $this->assertIsArray($form);

    $this->assertArrayHasKey('plugin_id', $form['population']);
    $this->assertIsArray($form['population']['plugin_id']);
    $this->assertEquals($form['population']['plugin_id']['#type'], 'radios');
    $this->assertEquals($form['population']['plugin_id']['#title'], $this->t('Population Logic'));
    $this->assertEquals($form['population']['plugin_id']['#options'], \Drupal::service('mars_recommendations.recommendations_service')->getPopulationLogicOptions());
    $this->assertTrue($form['population']['plugin_id']['#required']);
    $this->assertEquals($form['population']['plugin_id']['#default_value'], 'test_plugin_1');

    $this->assertArrayHasKey('configuration', $form['population']);
    $this->assertIsArray($form['population']['configuration']);
    $this->assertEquals($form['population']['configuration']['#type'], 'container');
    $this->assertIsArray($form['population']['configuration']['subform']);
    $this->assertEquals(self::TEST_PLUGIN_CONFIGURATION_FORM, $form['population']['configuration']['subform']);
  }

  /**
   * Test.
   */
  public function testBlockValidateProperlyByTrigger() {
    $block = RecommendationsModuleBlock::create(
      \Drupal::getContainer(),
      [
        'population_plugin_id' => 'test_plugin_1',
        'population_plugin_configuration' => [],
      ],
      'recommendations_module',
      $this->defaultDefinitions
    );

    $form = [];
    $this->formStateMock
      ->expects($this->any())
      ->method('getTriggeringElement')
      ->willReturn(
        [
          '#name' => 'settings[population][plugin_id]',
        ]
      );

    $this->formStateMock
      ->expects($this->once())
      ->method('setRebuild');

    $this->formStateMock
      ->expects($this->any())
      ->method('getValue')
      ->willReturn([
        'plugin_id' => 'id',
      ]);

    $block->validateConfigurationForm($form, $this->formStateMock);
  }

  /**
   * Test.
   */
  public function testBlockValidateProperly() {
    $block = RecommendationsModuleBlock::create(
      \Drupal::getContainer(),
      [
        'population_plugin_id' => 'test_plugin_1',
        'population_plugin_configuration' => [],
      ],
      'recommendations_module',
      $this->defaultDefinitions
    );

    $form = self::TEST_FORM_SETTINGS;

    $this->formStateMock
      ->expects($this->once())
      ->method('has')
      ->with('population_logic_plugin')
      ->willReturn(TRUE);

    $this->formStateMock
      ->expects($this->any())
      ->method('get')
      ->with('population_logic_plugin')
      ->willReturn(\Drupal::getContainer()
        ->get('mars_recommendations.recommendations_service')->getPopulationLogicPlugin('test_plugin', []));

    $this->formStateMock
      ->expects($this->any())
      ->method('getValues')
      ->willReturn([]);

    $this->formStateMock
      ->expects($this->any())
      ->method('getTriggeringElement')
      ->willReturn(
        [
          '#name' => 'name',
        ]
      );

    $block->blockValidate($form, $this->formStateMock);
  }

  /**
   * Test.
   */
  public function testBlockSubmitProperly() {
    $block = RecommendationsModuleBlock::create(
      \Drupal::getContainer(),
      [
        'population_plugin_id' => 'test_plugin_1',
        'population_plugin_configuration' => [],
      ],
      'recommendations_module',
      $this->defaultDefinitions
    );

    $form = self::TEST_FORM;

    $this->formStateMock
      ->expects($this->once())
      ->method('has')
      ->with('population_logic_plugin')
      ->willReturn(TRUE);

    $this->formStateMock
      ->expects($this->any())
      ->method('get')
      ->with('population_logic_plugin')
      ->willReturn(\Drupal::getContainer()
        ->get('mars_recommendations.recommendations_service')->getPopulationLogicPlugin('test_plugin', []));

    $this->formStateMock
      ->expects($this->never())
      ->method('setRebuild');

    $this->formStateMock
      ->expects($this->any())
      ->method('getValues')
      ->willReturn([]);

    $block->blockSubmit($form, $this->formStateMock);
  }

  /**
   * Test.
   */
  public function testGetPopulationSettingsFormProperly() {
    $block = RecommendationsModuleBlock::create(
      \Drupal::getContainer(),
      [
        'population_plugin_id' => 'test_plugin_1',
        'population_plugin_configuration' => [],
      ],
      'recommendations_module',
      $this->defaultDefinitions
    );

    $expected = [
      'subform' => [
        '#parents' => [
          'test',
        ],
      ],
    ];

    $form = self::TEST_FORM;
    $configuration_form = $block->getPopulationLogicSettingsForm($form, $this->formStateMock);

    $this->assertEquals($expected, $configuration_form);
  }

  /**
   * Returns Recommendations Service mock for block configuration.
   *
   * @return \Drupal\mars_recommendations\RecommendationsService|\PHPUnit\Framework\MockObject\MockObject
   *   Recommendations service mock.
   */
  private function createRecommendationsServiceMock() {
    $mock = $this->getMockBuilder(RecommendationsService::class)
      ->disableOriginalConstructor()
      ->getMock();

    $mock->method('getPopulationLogicOptions')
      ->willReturn([
        'test_plugin_1' => 'Test Plugin 1',
      ]);

    $plugin = new class implements RecommendationsLogicPluginInterface {

      /**
       * {@inheritdoc}
       */
      public function getResultsLimit(): int {
        return self::UNLIMITED;
      }

      /**
       * {@inheritdoc}
       */
      public function buildConfigurationForm(array &$form, FormStateInterface $form_state) {
        return RecommendationsModuleBlockTest::TEST_PLUGIN_CONFIGURATION_FORM;
      }

      /**
       * {@inheritdoc}
       */
      public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {}

      /**
       * {@inheritdoc}
       */
      public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {}

      /**
       * {@inheritdoc}
       */
      public function getRecommendations() {
        return [];
      }

      /**
       * {@inheritdoc}
       */
      public function getRenderedRecommendations() {
        return [];
      }

    };

    $mock->method('getPopulationLogicPlugin')->willReturn($plugin);
    return $mock;
  }

  /**
   * Returns Theme Configuration Parser mock for block configuration.
   *
   * @return \Drupal\mars_common\ThemeConfiguratorParser|\PHPUnit\Framework\MockObject\MockObject
   *   Theme Configuration Parser service mock.
   */
  private function createThemeConfigurationParserMock() {
    $mock = $this->createMock(ThemeConfiguratorParser::class);
    $mock
      ->method('getGraphicDivider')
      ->willReturn(new SVG('<svg xmlns="http://www.w3.org/2000/svg"/>', 'id'));

    return $mock;
  }

  /**
   *
   */
  private function createConfigFactoryMock() {
    $configMock = $this->createMock(ConfigFactoryInterface::class);
    $this->immutableConfigMock = $this->createMock(ImmutableConfig::class);
    $configMock
      ->method('getEditable')
      ->with('mars_common.character_limit_page')
      ->willReturn($this->immutableConfigMock);

    return $configMock;
  }

  /**
   * Returns Language helper mock.
   *
   * @return \Drupal\mars_common\LanguageHelper|\PHPUnit\Framework\MockObject\MockObject
   *   Theme Configuration Parser service mock.
   */
  private function createLanguageHelperMock() {
    $mock = $this->createMock(LanguageHelper::class);
    $mock->method('translate')
      ->will(
        $this->returnCallback(function ($arg) {
          return $arg;
        })
      );

    return $mock;
  }

}
