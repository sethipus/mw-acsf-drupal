<?php

namespace Drupal\Tests\mars_recommendations\Unit\Plugin\MarsRecommendationsLogic;

use Drupal\Core\Plugin\Context\ContextInterface;
use Drupal\mars_recommendations\DynamicRecommendationsStrategyPluginBase;
use Drupal\mars_recommendations\DynamicRecommendationsStrategyPluginManager;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;
use Drupal\mars_recommendations\Plugin\MarsRecommendationsLogic\Dynamic;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 * Class DynamicTest - unit tests.
 *
 * @package Drupal\Tests\mars_recommendations\Unit
 * @covers \Drupal\mars_recommendations\Plugin\MarsRecommendationsLogic\Dynamic
 */
class DynamicTest extends UnitTestCase {

  private const TEST_CONFIG = [
    '#type' => 'html_tag',
    '#tag' => 'div',
    '#value' => 'This plugin does not require a specific configuration.',
  ];

  /**
   * System under test.
   *
   * @var \Drupal\mars_recommendations\Plugin\MarsRecommendationsLogic\Dynamic
   */
  private $dynamic;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $entityTypeManagerMock;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Form\FormStateInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $formStateMock;

  /**
   * Mock.
   *
   * @var \Drupal\mars_recommendations\DynamicRecommendationsStrategyPluginManager|\PHPUnit\Framework\MockObject\MockObject
   */
  private $pluginManagerMock;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->createMocks();
    $container = new ContainerBuilder();
    $container->set('plugin.manager.dynamic_recommendations_strategy', $this->pluginManagerMock);
    \Drupal::setContainer($container);
    $this->dynamic = new Dynamic(
      [],
      'dynamic',
      [
        'id' => 'dynamic',
        'provider' => 'mars_recommendations',
        'admin_label' => 'test',
      ],
      $this->pluginManagerMock,
      $this->entityTypeManagerMock
    );

    $this->dynamic->setStringTranslation($this->getStringTranslationStub());
  }

  /**
   * Test.
   */
  public function testGetResultsLimit() {
    $limit = $this->dynamic->getResultsLimit();
    $this->assertEquals(Dynamic::DEFAULT_RESULTS_LIMIT, $limit);
  }

  /**
   * Test.
   */
  public function testGetRecommendations() {
    $node = $this->createNodeMock();
    $nodeContext = $this->createMock(ContextInterface::class);
    $nodeContext
      ->method('getContextValue')
      ->willReturn($node);
    $this->dynamic->setContext('node', $nodeContext);
    $recommendations = $this->dynamic->getRecommendations();
    $this->assertIsArray($recommendations);
  }

  /**
   * Test.
   */
  public function testBuildConfigurationFormProperly() {
    $form = [];
    $config = $this->dynamic->buildConfigurationForm($form, $this->formStateMock);
    $this->assertEquals(self::TEST_CONFIG, $config);
  }

  /**
   * Test.
   */
  public function testValidateConfigurationFormProperly() {
    $form = [];
    $this->formStateMock
      ->expects($this->once())
      ->method('setValue')
      ->with('population_plugin_configuration', []);

    $this->dynamic->validateConfigurationForm($form, $this->formStateMock);
  }

  /**
   * Test.
   */
  public function testSubmitConfigurationFormProperly() {
    $form = [];
    $this->formStateMock
      ->expects($this->never())
      ->method($this->anything());

    $this->dynamic->submitConfigurationForm($form, $this->formStateMock);
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks() {
    $this->pluginManagerMock = $this->createPluginManagerMock();
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
  }

  /**
   * Creates plugin manager mock.
   *
   * @return \Drupal\mars_recommendations\DynamicRecommendationsStrategyPluginManager|\PHPUnit\Framework\MockObject\MockObject
   *   Plugin manager mock.
   */
  private function createPluginManagerMock() {
    $plugin_mock = $this->createMock(DynamicRecommendationsStrategyPluginBase::class);
    $plugin_mock
      ->expects($this->any())
      ->method('setContext')
      ->willReturn(TRUE);
    $plugin_mock
      ->expects($this->any())
      ->method('generate')
      ->willReturn([]);
    $plugin_manager_mock = $this->createMock(DynamicRecommendationsStrategyPluginManager::class);
    $plugin_manager_mock
      ->expects($this->any())
      ->method('createInstance')
      ->willReturn($plugin_mock);

    return $plugin_manager_mock;
  }

  /**
   * Creates a node mock.
   *
   * @return \Drupal\node\Entity\Node|\PHPUnit\Framework\MockObject\MockObject
   *   Node mock.
   */
  private function createNodeMock() {
    $mock = $this->createMock(Node::class);
    $mock
      ->expects($this->any())
      ->method('getType')
      ->willReturn('test');

    return $mock;
  }

}
