<?php

namespace Drupal\Tests\mars_product\Unit\Form;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_product\Form\WtbConfigForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_product\Form\WtbConfigForm
 * @group mars
 * @group mars_product
 */
class WtbConfigFormTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_product\Form\WtbConfigForm
   */
  private $form;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Form\FormStateInterface
   */
  private $formStateMock;

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
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\LanguageHelper
   */
  private $languageHelperMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->form = new WtbConfigForm(
      $this->configFactoryMock,
      $this->languageHelperMock
    );
  }

  /**
   * Test.
   */
  public function testShouldGetFormId() {
    $form_id = $this->form->getFormId();
    $this->assertSame(
      'wtb_config_form',
      $form_id
    );
  }

  /**
   * Test.
   */
  public function testShouldBuildForm() {
    $form = [];

    $this->containerMock
      ->expects($this->any())
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
      ->expects($this->any())
      ->method('getEditable')
      ->willReturn(
        $this->configMock
      );

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('default_value');

    $form = $this->form->buildForm(
      $form,
      $this->formStateMock
    );
    $this->assertIsArray($form);
    $this->assertNotEmpty($form['general']['commerce_vendor']);
  }

  /**
   * Test.
   */
  public function testShouldSubmitForm() {
    $form = [];

    $this->configFactoryMock
      ->expects($this->exactly(6))
      ->method('getEditable')
      ->willReturn(
        $this->configMock
      );

    $this->containerMock
      ->expects($this->any())
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

    $this->form->setMessenger($this->messengerMock);

    $this->configMock
      ->expects($this->any())
      ->method('set');

    $this->formStateMock
      ->expects($this->any())
      ->method('getValue')
      ->willReturn('value');

    $this->form->submitForm(
      $form,
      $this->formStateMock
    );
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->configMock = $this->createMock(Config::class);
    $this->messengerMock = $this->createMock(MessengerInterface::class);
    $this->languageHelperMock = $this->createMock(LanguageHelper::class);
  }

}
