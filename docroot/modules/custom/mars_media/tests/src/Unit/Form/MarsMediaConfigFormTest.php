<?php

namespace Drupal\Tests\mars_media\Unit\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\mars_media\Form\MarsMediaConfigForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Unit tests for MarsMediaConfigForm.
 *
 * @covers \Drupal\mars_media\Form\MarsMediaConfigForm
 */
class MarsMediaConfigFormTest extends UnitTestCase {

  /**
   * Mocked config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $configFactoryMock;

  /**
   * Mocked config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|\PHPUnit\Framework\MockObject\MockObject
   */
  private $configMock;

  /**
   * Mocked container.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface
   */
  private $containerMock;

  /**
   * Mocked messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $messengerMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->messengerMock = $this->createMock(MessengerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->configMock = $this->createMock(ImmutableConfig::class);
    $this->configFactoryMock
      ->method('getEditable')
      ->with('mars_media.settings')
      ->willReturn($this->configMock);
    $this->containerMock = $this->createMock(ContainerInterface::class);
    \Drupal::setContainer($this->containerMock);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function buildFormShouldCreateRenderArrayWithFields() {
    $form_instance = $this->createForm();

    $render_array = $form_instance->buildForm(
      [],
      $this->createMock(FormStateInterface::class)
    );

    $this->assertArrayHasKey('cf_resize', $render_array);
  }

  /**
   * Test method.
   *
   * @test
   */
  public function submittedFormValuesShouldBeSetOnTheConfigObject() {
    $mocked_form_state = $this->createMock(FormStateInterface::class);
    $form_instance = $this->createForm();
    $form = [];
    $submitted_values = [
      'enabled' => TRUE,
      'bundles' => [
        'image' => TRUE,
      ],
    ];
    $mocked_form_state
      ->method('getValue')
      ->with('cf_resize')
      ->willReturn($submitted_values);

    $this->configMock
      ->expects($this->once())
      ->method('set')
      ->with('cf_resize', $submitted_values);

    $form_instance->submitForm(
      $form,
      $mocked_form_state
    );

  }

  /**
   * Test method.
   *
   * @test
   */
  public function configShouldBeSavedUponSubmit() {
    $mocked_form_state = $this->createMock(FormStateInterface::class);
    $form_instance = $this->createForm();
    $form = [];

    $this->configMock
      ->expects($this->once())
      ->method('save');

    $form_instance->submitForm(
      $form,
      $mocked_form_state
    );

  }

  /**
   * Creates a new form with mocked dependencies.
   *
   * @return \Drupal\mars_media\Form\MarsMediaConfigForm
   *   A new form instance.
   */
  private function createForm(): MarsMediaConfigForm {
    $form_instance = new MarsMediaConfigForm($this->configFactoryMock);
    $form_instance->setMessenger($this->messengerMock);
    return $form_instance;
  }

}
