<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Block;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_common\Plugin\Block\NewsletterSignUpFormBlock;

/**
 * Class NewsletterSignUpFormBlockTest.
 *
 * @package Drupal\Tests\mars_common\Unit
 * @covers \Drupal\mars_common\Plugin\Block\newsletterSignUpFormBlock
 */
class NewsletterSignUpFormBlockTest extends UnitTestCase {

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
   * Tested Newsletter SignUp Form block.
   *
   * @var \Drupal\mars_common\Plugin\Block\NewsletterSignUpFormBlock
   */
  private $newsletterSignUpFormBlock;

  /**
   * Test block configuration.
   *
   * @var array
   */
  private $configuration;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->configuration = [
      'form_url' => 'http',
    ];
    $definitions = [
      'provider'    => 'test',
      'admin_label' => 'test',
    ];

    // We should create it in test to import different configs.
    $this->newsletterSignUpFormBlock = new NewsletterSignUpFormBlock(
      $this->configuration,
      'contact_form_block',
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
    $config_form = $this->newsletterSignUpFormBlock->buildConfigurationForm([], $this->formStateMock);
    $this->assertCount(6, $config_form);
    $this->assertArrayHasKey('form_id', $config_form);
  }

  /**
   * Test building block.
   */
  public function testBuildBlockRenderArrayProperly() {
    $build = $this->newsletterSignUpFormBlock->build();

    $this->assertCount(2, $build);
    $this->assertArrayHasKey('#form_url', $build);
    $this->assertEquals('newsletter_signup_form_block', $build['#theme']);
  }

}
