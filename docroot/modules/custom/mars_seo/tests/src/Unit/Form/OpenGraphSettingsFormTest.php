<?php

namespace Drupal\Tests\mars_seo\Unit\Form;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\mars_seo\Form\OpenGraphSettingForm;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\mars_seo\Form\OpenGraphSettingForm
 */
class OpenGraphSettingsFormTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_seo\Form\OpenGraphSettingForm
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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $this->form = new OpenGraphSettingForm(
      $this->configFactoryMock
    );
    $this->form->setMessenger($this->messengerMock);
  }

  /**
   * Tests that create method works.
   *
   * @test
   */
  public function shouldInstantiateProperly() {
    $form = OpenGraphSettingForm::create($this->containerMock);

    $this->assertInstanceOf(OpenGraphSettingForm::class, $form);
  }

  /**
   * Tests that form has the proper id.
   *
   * @test
   */
  public function shouldReturnProperId() {
    $this->assertSame(
      'open_graph_settings',
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
      'image',
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
      ['og_default_image', NULL, 'test.png'],
    ];

    $this->formStateMock
      ->method('getValue')
      ->willReturn('test.png');

    $this->form->submitForm($form_data, $this->formStateMock);

    foreach ($valueMap as $mapItem) {
      $key = $mapItem[0];
      $value = $mapItem[2];

      $this->configProphecy
        ->set($key, $value)
        ->shouldHaveBeenCalled();
    }

    $this->configProphecy
      ->save()
      ->shouldHaveBeenCalled();
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
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->messengerMock = $this->createMock(MessengerInterface::class);

    $this->configProphecy = $this->prophesize(Config::class);
    $this->configProphecy
      ->get(Argument::any())
      ->willReturn('default_value');
    $this->configProphecy
      ->save()
      ->willReturn(NULL);
    $this->configProphecy
      ->set(Argument::any(), Argument::any())
      ->willReturn($this->configProphecy);

    $this->configFactoryMock
      ->method('getEditable')
      ->with('mars_seo.settings')
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
        ]
      );
  }

}
