<?php

namespace Drupal\Tests\mars_recommendations\Unit\Plugin\Block;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mars_common\SVG\SVG;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_recommendations\Plugin\Block\RecommendationsModuleBlock;
use Drupal\mars_recommendations\RecommendationsLogicPluginInterface;
use Drupal\mars_recommendations\RecommendationsService;
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
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $container = new ContainerBuilder();
    $container->set('string_translation', $this->getStringTranslationStub());
    $container->set('mars_recommendations.recommendations_service', $this->createRecommendationsServiceMock());
    $container->set('mars_common.theme_configurator_parser', $this->createThemeConfigurationParserMock());
    Drupal::setContainer($container);

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
  }

  /**
   * Test default configuration for Recommendations Module block.
   */
  public function testDefaultConfiguration() {
    $block = RecommendationsModuleBlock::create(
      Drupal::getContainer(),
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
  public function testDefaultBlockBuildConfigurationForm() {
    $block = RecommendationsModuleBlock::create(
      Drupal::getContainer(),
      [],
      'recommendations_module',
      $this->defaultDefinitions
    );

    $form_state = new FormState();
    $form_state->setFormObject($this->formObjectStub);
    $form = $block->buildConfigurationForm([], $form_state);

    $this->assertIsArray($form);

    $this->assertArrayHasKey('title', $form);
    $this->assertIsArray($form['title']);
    $this->assertEquals($form['title']['#type'], 'textfield');
    $this->assertEquals($form['title']['#title'], $this->t('Title'));
    $this->assertEquals($form['title']['#description'], $this->t('Defaults to <em>More <strong>&lt;Content Type&gt;</strong>s Like This</em>'));
    $this->assertEquals($form['title']['#placeholder'], $this->t('More &lt;Content Type&gt;s Like This'));
    $this->assertEquals($form['title']['#maxwidth'], 55);

    $this->assertArrayHasKey('population', $form);
    $this->assertIsArray($form['population']);

    $this->assertArrayHasKey('plugin_id', $form['population']);
    $this->assertIsArray($form['population']['plugin_id']);
    $this->assertEquals($form['population']['plugin_id']['#type'], 'radios');
    $this->assertEquals($form['population']['plugin_id']['#title'], $this->t('Population Logic'));
    $this->assertArrayEquals($form['population']['plugin_id']['#options'], Drupal::service('mars_recommendations.recommendations_service')->getPopulationLogicOptions());
    $this->assertTrue($form['population']['plugin_id']['#required']);

    $this->assertArrayHasKey('configuration', $form['population']);
    $this->assertIsArray($form['population']['configuration']);
    $this->assertArrayEquals([
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
      Drupal::getContainer(),
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
    $this->assertArrayEquals($form['population']['plugin_id']['#options'], Drupal::service('mars_recommendations.recommendations_service')->getPopulationLogicOptions());
    $this->assertTrue($form['population']['plugin_id']['#required']);
    $this->assertEquals($form['population']['plugin_id']['#default_value'], 'test_plugin_1');

    $this->assertArrayHasKey('configuration', $form['population']);
    $this->assertIsArray($form['population']['configuration']);
    $this->assertEquals($form['population']['configuration']['#type'], 'container');
    $this->assertIsArray($form['population']['configuration']['subform']);
    $this->assertArrayEquals(self::TEST_PLUGIN_CONFIGURATION_FORM, $form['population']['configuration']['subform']);
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
        'test_plugin_2' => 'Test Plugin 2',
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
      ->willReturn(new SVG('<svg xmlns="http://www.w3.org/2000/svg"/>'));

    return $mock;
  }

}
