<?php

namespace Drupal\Tests\salsify_integration\Unit\Form;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\salsify_integration\Form\ConfigForm;
use Drupal\salsify_integration\SalsifyFields;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\salsify_integration\Form\ConfigForm
 * @group mars
 * @group salsify_integration
 */
class ConfigFormTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\Form\ConfigForm
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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  private $eventDispatcherMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandlerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\SalsifyFields
   */
  private $salsifyFieldsMock;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeInterface
   */
  private $entityTypeMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityStorageInterface
   */
  private $entityStorageMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityInterface
   */
  private $entityMock;

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
    $this->form = new ConfigForm(
      $this->configFactoryMock,
      $this->entityTypeManagerMock,
      $this->eventDispatcherMock,
      $this->moduleHandlerMock,
      $this->salsifyFieldsMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(5))
      ->method('get')
      ->willReturnMap(
        [
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configFactoryMock,
          ],
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'event_dispatcher',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->eventDispatcherMock,
          ],
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
          [
            'salsify_integration.salsify_fields',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyFieldsMock,
          ],
        ]
      );
    $this->form::create($this->containerMock);
  }

  /**
   * Test.
   */
  public function testShouldGetFormId() {
    $form_id = $this->form->getFormId();
    $this->assertSame(
      'salsify_integration_config_form',
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
      ->expects($this->once())
      ->method('getEditable')
      ->willReturn(
        $this->configMock
      );

    $this->configFactoryMock
      ->expects($this->once())
      ->method('get')
      ->willReturn(
        $this->configMock
      );

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('default_value');

    $this->eventDispatcherMock
      ->expects($this->once())
      ->method('dispatch');

    $this->formStateMock
      ->expects($this->any())
      ->method('getValue')
      ->willReturn('test_value');

    $this->entityTypeManagerMock
      ->expects($this->once())
      ->method('getDefinition')
      ->willReturn($this->entityTypeMock);

    $this->entityTypeMock
      ->expects($this->once())
      ->method('getBundleEntityType')
      ->willReturn('bundle');

    $this->entityTypeManagerMock
      ->expects($this->once())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->once())
      ->method('loadMultiple')
      ->willReturn([
        $this->entityMock,
      ]);

    $this->entityMock
      ->expects($this->once())
      ->method('id')
      ->willReturn(123);

    $this->entityMock
      ->expects($this->once())
      ->method('label')
      ->willReturn('label');

    $this->moduleHandlerMock
      ->expects($this->once())
      ->method('moduleExists')
      ->willReturn(TRUE);

    $form = $this->form->buildForm(
      $form,
      $this->formStateMock
    );
    $this->assertIsArray($form);
    $this->assertNotEmpty($form['salsify_operations']['salsify_start_import']);
  }

  /**
   * Test.
   */
  public function testShouldLoadEntityBundles() {
    $form = [
      'salsify_api_settings' => [
        'setup_types' => [],
      ],
    ];

    $response = $this->form->loadEntityBundles(
      $form,
      $this->formStateMock
    );
    $this->assertInstanceOf(
      AjaxResponse::class,
      $response
    );
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->moduleHandlerMock = $this->createMock(ModuleHandlerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->eventDispatcherMock = $this->createMock(ContainerAwareEventDispatcher::class);
    $this->salsifyFieldsMock = $this->createMock(SalsifyFields::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->configMock = $this->createMock(Config::class);
    $this->entityTypeMock = $this->createMock(EntityTypeInterface::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->entityMock = $this->createMock(EntityInterface::class);
    $this->messengerMock = $this->createMock(MessengerInterface::class);
  }

}
