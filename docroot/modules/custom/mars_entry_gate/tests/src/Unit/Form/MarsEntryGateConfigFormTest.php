<?php

namespace Drupal\Tests\mars_entry_gate\Unit\Form;

use Drupal;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\mars_entry_gate\Form\EntryGateConfigForm;
use Drupal\mars_onetrust\Form\OneTrustConfigForm;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_entry_gate\Form\EntryGateConfigForm
 */
class MarsEntryGateConfigFormTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_entry_gate\Form\EntryGateConfigForm
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
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Condition\ConditionManager
   */
  private $conditionPluginManagerMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    Drupal::setContainer($this->containerMock);

    $this->form = new EntryGateConfigForm(
      $this->configFactoryMock,
      $this->conditionPluginManagerMock
    );
    $this->form->setMessenger($this->messengerMock);
  }

  /**
   * Tests that create method works.
   *
   * @test
   */
  public function shouldInstantiateProperly() {
    $form = OneTrustConfigForm::create($this->containerMock);

    $this->assertInstanceOf(OneTrustConfigForm::class, $form);
  }

  /**
   * Tests that form has the proper id.
   *
   * @test
   */
  public function shouldReturnProperId() {
    $this->assertSame(
      'entry_gate_config_form',
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
      'enabled',
      'title',
      'description',
      'heading',
      'marketing_message',
      'minimum_age',
      'error_title',
      'error_link_1',
      'error_link_2',
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
      ['enabled', NULL, TRUE],
      ['title', NULL, 'test_title'],
      ['description', NULL, 'test_description'],
      ['heading', NULL, 'test_heading'],
      ['marketing_message', NULL, ['value' => 'test_marketing_message']],
      ['minimum_age', NULL, 20],
      ['error_title', NULL, 'test_error_title'],
      ['error_link_1', NULL, 'http://error1.test'],
      ['error_link_2', NULL, 'http://error2.test'],
    ];

    $this->formStateMock
      ->method('getValue')
      ->willReturnMap($valueMap);

    $this->form->submitForm($form_data, $this->formStateMock);

    foreach ($valueMap as $mapItem) {
      $key = $mapItem[0];
      $value = $mapItem[2];

      if ($key === 'marketing_message') {
        $value = $value['value'];
      }

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
    $this->conditionPluginManagerMock = $this->createMock(ConditionManager::class);

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
      ->with('mars_entry_gate.settings')
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
            'plugin.manager.condition',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->conditionPluginManagerMock,
          ],
        ]
      );
  }

}
