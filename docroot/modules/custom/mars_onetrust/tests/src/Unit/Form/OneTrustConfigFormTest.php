<?php

namespace Drupal\Tests\mars_onetrust\Unit\Form;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\mars_onetrust\Form\OneTrustConfigForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_onetrust\Form\OneTrustConfigForm
 * @group mars
 * @group mars_onetrust
 */
class OneTrustConfigFormTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_onetrust\Form\OneTrustConfigForm
   */
  private $oneTrustConfigForm;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translationMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\Config
   */
  private $configMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Messenger\MessengerInterface
   */
  private $messengerMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->oneTrustConfigForm = new OneTrustConfigForm(
      $this->configFactoryMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(1))
      ->method('get')
      ->willReturnMap(
        [
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configFactoryMock,
          ],
        ]
      );
    $this->oneTrustConfigForm::create($this->containerMock);
  }

  /**
   * Test.
   */
  public function testShouldReturnProperId() {
    $this->assertSame(
      'onetrust_config_form',
      $this->oneTrustConfigForm->getFormId()
    );
  }

  /**
   * Test.
   */
  public function testShouldBuildForm() {
    $form_data = [];

    $this->containerMock
      ->expects($this->exactly(1))
      ->method('get')
      ->willReturnMap(
        [
          [
            'string_translation',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->translationMock,
          ],
        ]
      );

    $this->configFactoryMock
      ->expects($this->once())
      ->method('getEditable')
      ->willReturn(
        $this->configMock
      );

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('default_value');

    $form = $this->oneTrustConfigForm->buildForm(
      $form_data,
      $this->formStateMock
    );
    $this->assertCount(4, $form);
  }

  /**
   * Test.
   */
  public function testShouldSubmitForm() {
    $form_data = [];

    $this->configFactoryMock
      ->expects($this->once())
      ->method('getEditable')
      ->willReturn(
        $this->configMock
      );

    $this->configMock
      ->expects($this->any())
      ->method('set')
      ->willReturn($this->configMock);

    $this->configMock
      ->expects($this->once())
      ->method('save');

    $this->containerMock
      ->expects($this->exactly(1))
      ->method('get')
      ->willReturnMap(
        [
          [
            'string_translation',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->translationMock,
          ],
        ]
      );

    $this->oneTrustConfigForm->setMessenger($this->messengerMock);
    $this->oneTrustConfigForm->submitForm($form_data, $this->formStateMock);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->configMock = $this->createMock(Config::class);
    $this->messengerMock = $this->createMock(MessengerInterface::class);
  }

}
