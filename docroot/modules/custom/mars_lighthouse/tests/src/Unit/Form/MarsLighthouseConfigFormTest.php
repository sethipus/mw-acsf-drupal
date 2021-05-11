<?php

namespace Drupal\Tests\mars_lighthouse\Unit\Form;

use Drupal;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\mars_lighthouse\Client\LighthouseDefaultsProvider;
use Drupal\mars_lighthouse\Form\MarsLighthouseConfigForm;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_lighthouse\Form\MarsLighthouseConfigForm
 */
class MarsLighthouseConfigFormTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_lighthouse\Form\MarsLighthouseConfigForm
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
   * @var \Prophecy\Prophecy\ObjectProphecy|\Drupal\Core\Config\Config
   */
  private $configProphecy;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Messenger\MessengerInterface
   */
  private $messengerMock;

  /**
   * Defaults provider mock.
   *
   * @var \Drupal\mars_lighthouse\Client\LighthouseDefaultsProvider|\PHPUnit\Framework\MockObject\MockObject
   */
  private $defaultsProviderMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    Drupal::setContainer($this->containerMock);

    $this->form = new MarsLighthouseConfigForm(
      $this->configFactoryMock,
      $this->defaultsProviderMock
    );
    $this->form->setMessenger($this->messengerMock);
  }

  /**
   * Tests that create method works.
   *
   * @test
   */
  public function shouldInstantiateProperly() {
    $form = MarsLighthouseConfigForm::create($this->containerMock);

    $this->assertInstanceOf(MarsLighthouseConfigForm::class, $form);
  }

  /**
   * Tests that form has the proper id.
   *
   * @test
   */
  public function shouldReturnProperId() {
    $this->assertSame(
      'mars_lighthouse_config_form',
      $this->form->getFormId()
    );
  }

  /**
   * Should create build array with proper keys in it.
   *
   * @test
   */
  public function shouldBuildForm() {
    $form_data = [];

    $form = $this->form->buildForm(
      $form_data,
      $this->formStateMock
    );

    $expected_keys = [
      'client_id',
      'client_secret',
      'api_key',
      'base_path',
      'api_version',
      'port',
    ];

    foreach ($expected_keys as $expected_key) {
      $this->assertArrayHasKey($expected_key, $form);
    }
  }

  /**
   * Should map values to the config.
   *
   * @test
   */
  public function shouldMapValuesToConfig() {
    $form_data = [];
    $valueMap = [
      ['client_id', NULL, 'sample_client_id'],
      ['client_secret', NULL, 'sample_client_secret'],
      ['api_key', NULL, 'sample_api_key'],
      ['base_path', NULL, 'sample_base_path'],
      ['api_version', NULL, 'sample_api_version'],
      ['port', NULL, 80],
    ];

    $this->formStateMock
      ->method('getValue')
      ->willReturnMap($valueMap);

    $this->form->submitForm($form_data, $this->formStateMock);

    foreach ($valueMap as $mapItem) {
      $key = $mapItem[0];
      $value = $mapItem[2];

      $this->configProphecy
        ->set($key, $value)
        ->shouldHaveBeenCalled();
    }

  }

  /**
   * Should map values to the config.
   *
   * @test
   */
  public function shouldSaveConfig() {
    $form_data = [];

    $this->form->submitForm($form_data, $this->formStateMock);

    $this->configProphecy
      ->save()
      ->shouldHaveBeenCalled();
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->messengerMock = $this->createMock(MessengerInterface::class);
    $this->defaultsProviderMock = $this->createMock(LighthouseDefaultsProvider::class);

    $this->configProphecy = $this->prophesize(Config::class);
    $this->configProphecy
      ->get(Argument::any())
      ->willReturn('default_value');
    $this->configProphecy
      ->save()
      ->willReturn(NULL);
    $this->configProphecy
      ->set(Argument::any(), Argument::any())
      ->willReturn(NULL);

    $this->configFactoryMock
      ->method('getEditable')
      ->with('mars_lighthouse.settings')
      ->willReturn(
        $this->configProphecy->reveal()
      );

    $this->containerMock
      ->method('get')
      ->willReturnMap(
        [
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configFactoryMock,
          ],
          [
            'string_translation',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->translationMock,
          ],
          [
            'mars_lighthouse.config_defaults_provider',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->defaultsProviderMock,
          ],
        ]
      );
  }

}
