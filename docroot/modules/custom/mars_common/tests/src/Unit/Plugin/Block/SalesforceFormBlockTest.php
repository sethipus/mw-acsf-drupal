<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\Plugin\Block\SalesforceFormBlock;

/**
 * Class SalesforceFormBlockTest.
 *
 * @package Drupal\Tests\mars_common\Unit
 * @covers \Drupal\mars_common\Plugin\Block\SalesforceFormBlock
 */
class SalesforceFormBlockTest extends UnitTestCase {

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
   * Tested Salesforce Form block.
   *
   * @var \Drupal\mars_common\Plugin\Block\SalesforceFormBlock
   */
  private $salesforceFormBlock;

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
      'form_type' => 'formstack',
      'form_id' => 'form_id',
    ];
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    // We should create it in test to import different configs.
    $this->salesforceFormBlock = new SalesforceFormBlock(
      $this->configuration,
      'salesforce_form_block',
      $definitions
    );
  }

  /**
   * Create all mocks for tests.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
  }

  /**
   * Test configuration form.
   */
  public function testBuildConfigurationFormProperly() {
    $config_form = $this->salesforceFormBlock->buildConfigurationForm([], $this->formStateMock);
    $this->assertCount(7, $config_form);
    $this->assertArrayHasKey('form_type', $config_form);
    $this->assertArrayHasKey('form_id', $config_form);
  }

  /**
   * Test building block.
   */
  public function testBuildBlockRenderArrayProperly() {
    $build = $this->salesforceFormBlock->build();

    $this->assertCount(3, $build);
    $this->assertArrayHasKey('#form_type', $build);
    $this->assertArrayHasKey('#form_id', $build);
    $this->assertEquals('salesforce_form_block', $build['#theme']);
  }

}
