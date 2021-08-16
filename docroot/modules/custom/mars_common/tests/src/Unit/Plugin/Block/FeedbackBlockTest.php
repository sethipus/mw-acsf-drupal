<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormState;
use Drupal\mars_common\Plugin\Block\FeedbackBlock;
use Drupal\poll\Entity\Poll;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Core\Entity\EntityViewBuilderInterface;

/**
 * Class FeedbackBlockTest - unit tests.
 */
class FeedbackBlockTest extends UnitTestCase {

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

  /**
   * Feedback block.
   *
   * @var \Drupal\mars_common\Plugin\Block\FeedbackBlock
   */
  private $feedbackBlock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration;

  /**
   * Entity Type Manager Mock.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $entityTypeManagerMock;

  /**
   * Theme configuration parser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser|\PHPUnit\Framework\MockObject\MockObject
   */
  private $themeConfigurationParserMock;

  /**
   * Poll Storage Mock.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $pollStorageMock;

  /**
   * The poll view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $pollViewBuilder;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $this->configuration = [
      'label_display' => FALSE,
      'poll' => 'poll_test',
    ];

    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    $poll = $this->getMockBuilder(Poll::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->pollStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn($poll);

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturnMap([
        ['poll', $this->pollStorageMock],
      ]);

    $this->pollViewBuilder
      ->expects($this->any())
      ->method('view')
      ->willReturn($poll);

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getViewBuilder')
      ->with('poll')
      ->willReturn($this->pollViewBuilder);

    $this->feedbackBlock = new FeedbackBlock(
      $this->configuration,
      'feedback_block',
      $definitions,
      $this->entityTypeManagerMock,
      $this->themeConfigurationParserMock
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->themeConfigurationParserMock = $this->createMock(ThemeConfiguratorParser::class);
    $this->pollStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->pollViewBuilder = $this->createMock(EntityViewBuilderInterface::class);
  }

  /**
   * Test build.
   */
  public function testBuild() {
    $build = $this->feedbackBlock->build();
    $this->assertArrayHasKey('#poll', $build);
    $this->assertInstanceOf(Poll::class, $build['#poll']);
  }

  /**
   * Test method defaultConfiguration.
   */
  public function testDefaultConfiguration() {
    $default_configuration = $this->feedbackBlock->defaultConfiguration();
    $this->assertEquals(FALSE, $default_configuration['label_display']);
  }

  /**
   * Test method buildConfigurationForm.
   */
  public function testBuildConfigurationForm() {
    $form = [];
    $form_state = (new FormState())->setValues([
      'test' => 'test date',
    ]);
    $build_configuration_form = $this->feedbackBlock->buildConfigurationForm($form, $form_state);
    $this->assertArrayHasKey('poll', $build_configuration_form);
    $this->assertArrayHasKey('provider', $build_configuration_form);
    $this->assertArrayHasKey('admin_label', $build_configuration_form);
    $this->assertArrayHasKey('label', $build_configuration_form);
  }

  /**
   * Test method blockSubmit.
   */
  public function testBlockSubmit() {
    $poll = $this->getMockBuilder(Poll::class)
      ->disableOriginalConstructor()
      ->getMock();
    $form = [];
    $form_state = (new FormState())->setValues([
      'poll' => $poll,
    ]);
    $this->feedbackBlock->blockSubmit($form, $form_state);
    $this->assertInstanceOf(Poll::class, $this->feedbackBlock->getConfiguration()['poll']);
  }

}
