<?php

namespace Drupal\Tests\mars_recommendations\Unit\Plugin\Block;

use Drupal\Core\Form\FormState;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mars_recommendations\Plugin\Block\RecommendationsModuleBlock;
use Drupal\mars_recommendations\RecommendationsService;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the Recommendations Module block core functionality.
 *
 * @covers \Drupal\mars_recommendations\Plugin\Block\RecommendationsModuleBlock
 */
class RecommendationsModuleBlockTest extends UnitTestCase {

  use StringTranslationTrait;

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
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $container = new ContainerBuilder();
    $container->set('string_translation', $this->getStringTranslationStub());
    $container->set('mars_recommendations.recommendations_service', $this->createRecommendationsServiceMock());
    \Drupal::setContainer($container);
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
  public function testDefaultBlockBuildConfigurationForm() {
    $block = RecommendationsModuleBlock::create(
      \Drupal::getContainer(),
      [],
      'recommendations_module',
      $this->defaultDefinitions
    );

    $form_state = new FormState();
    $form = $block->buildConfigurationForm([], $form_state);

    $this->assertIsArray($form);

    $this->assertArrayHasKey('title', $form);
    $this->assertIsArray($form['title']);
    $this->assertEquals($form['title']['#type'], 'textfield');
    $this->assertEquals($form['title']['#title'], $this->t('Title'));
    $this->assertEquals($form['title']['#description'], $this->t('Defaults to <em>More <strong>&lt;Content Type&gt;</strong>s Like This</em>'));
    $this->assertEquals($form['title']['#placeholder'], $this->t('More &lt;Content Type&gt;s Like This'));
    $this->assertEquals($form['title']['#maxwidth'], 35);

    $this->assertArrayHasKey('population_logic', $form);
    $this->assertIsArray($form['population_logic']);
    $this->assertEquals($form['population_logic']['#type'], 'radios');
    $this->assertEquals($form['population_logic']['#title'], $this->t('Population Logic'));
    $this->assertArrayEquals($form['population_logic']['#options'], \Drupal::service('mars_recommendations.recommendations_service')->getPopulationLogicOptions());
    $this->assertTrue($form['population_logic']['#required']);
  }

  /**
   * Tests valid path for block structure build.
   */
  public function testBuildHappyPath() {
    $node = $this->createNodeMock();
    $this->createNodeContextMock($node);
  }

  /**
   * Creates node mock.
   *
   * @param string $type
   *   Node type.
   *
   * @return \Drupal\node\Entity\Node|\PHPUnit\Framework\MockObject\MockObject
   *   Node mock.
   */
  private function createNodeMock($type = 'product') {
    $node = $this->getMockBuilder('Drupal\node\Entity\Node')
      ->disableOriginalConstructor()
      ->getMock();

    $node->expects($this->any())
      ->method('getType')
      ->willReturn($type);

    return $node;
  }

  /**
   * Creates node context mock.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Node for context value.
   *
   * @return \Drupal\Core\Plugin\Context\ContextInterface|\PHPUnit\Framework\MockObject\MockObject
   *   Context mock.
   */
  private function createNodeContextMock(Node $node) {
    $nodeContext = $this->getMockBuilder(Context::class)
      ->disableOriginalConstructor()
      ->getMock();

    $nodeContext->expects($this->exactly(1))
      ->method('getContextValue')
      ->willReturn($node);

    return $nodeContext;
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
        'dynamic' => 'Dynamic',
        'manual' => 'Manual',
      ]);

    return $mock;
  }

}
