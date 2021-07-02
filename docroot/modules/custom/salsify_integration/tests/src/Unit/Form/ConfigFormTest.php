<?php

namespace Drupal\Tests\salsify_integration\Unit\Form;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\salsify_integration\Form\ConfigForm;
use Drupal\salsify_integration\MigrationRunner;
use Drupal\salsify_integration\ProductHelper;
use Drupal\salsify_integration\Run\RunResource;
use Drupal\salsify_integration\SalsifyEmailReport;
use Drupal\salsify_integration\SalsifyFields;
use Drupal\salsify_integration\SalsifyProductRepository;
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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\FieldableEntityInterface
   */
  private $fieldableEntityMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Messenger\MessengerInterface
   */
  private $messengerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Cache\CacheTagsInvalidator
   */
  private $cacheTagsInvalidatorMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\Query\QueryInterface
   */
  private $queryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityFieldManagerInterface
   */
  private $fieldManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\field\Entity\FieldConfig
   */
  private $fieldConfigMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\ProductHelper
   */
  private $productHelperMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\SalsifyProductRepository
   */
  private $salsifyProductRepoMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Logger\LoggerChannelInterface
   */
  private $loggerChannelMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\SalsifyEmailReport
   */
  private $salsifyEmailReportMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Queue\QueueFactory
   */
  private $queueFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Queue\QueueInterface
   */
  private $queueMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\Run\RunResource
   */
  private $runsResourceMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\MigrationRunner
   */
  private $migrationRunnerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManagerMock;

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
      $this->salsifyFieldsMock,
      $this->queueFactoryMock,
      $this->runsResourceMock,
      $this->migrationRunnerMock,
      $this->languageManagerMock
    );
  }

  /**
   * Test.
   */
  public function testShouldInstantiateProperly() {
    $this->containerMock
      ->expects($this->exactly(9))
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
          [
            'queue',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->queueFactoryMock,
          ],
          [
            'salsify_integration.salsify.runs',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->runsResourceMock,
          ],
          [
            'salsify_integration.migrations.products',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->migrationRunnerMock,
          ],
          [
            'language_manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->languageManagerMock,
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
      ->expects($this->any())
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

    $default_language_mock = $this->createMock(Language::class);
    $default_language_mock->expects($this->any())->method('getId')->willReturn('en');
    $default_language_mock->expects($this->any())->method('getName')->willReturn('English');
    $default_language_mock->expects($this->any())->method('isDefault')->willReturn(TRUE);
    $this->languageManagerMock->expects($this->any())->method('getLanguages')->willReturn([
      $default_language_mock,
    ]);

    $form = $this->form->buildForm(
      $form,
      $this->formStateMock
    );
    $this->assertIsArray($form);
    $this->assertNotEmpty($form["general"]["salsify_api_approach"]["salsify_operations"]['salsify_start_import']);
    $this->assertNotEmpty($form["general"]["salsify_multichannel_approach"]);
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
   * Test.
   */
  public function testShouldSubmitFormWhenSalsifyImport() {
    $form = [];

    $this->formStateMock
      ->expects($this->any())
      ->method('getTriggeringElement')
      ->willReturn([
        '#id' => 'edit-salsify-start-import',
      ]);

    $this->formStateMock
      ->expects($this->any())
      ->method('getValue')
      ->willReturn('force');

    $this->salsifyFieldsMock
      ->expects($this->any())
      ->method('importProductFields')
      ->willReturn([
        'products' => [
          [
            'salsify:id' => '123123',
            'CMS: Meta Description' => ['meta'],
            'Case Net Weight' => 'value',
            'CMS: Variety' => 'no',
          ],
        ],
        'mapping' => [],
        'market' => 'market',
      ]);

    $this->salsifyFieldsMock
      ->expects($this->any())
      ->method('prepareTermData');

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

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn(
        $this->configMock
      );

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('default_value');

    $this->configMock
      ->expects($this->any())
      ->method('set')
      ->willReturn($this->configMock);

    $this->configMock
      ->expects($this->any())
      ->method('save');

    $this->salsifyFieldsMock
      ->expects($this->any())
      ->method('addChildLinks');

    $this->form->setMessenger($this->messengerMock);

    $this->form->submitForm(
      $form,
      $this->formStateMock
    );

    $this->assertIsArray($form);
  }

  /**
   * Test.
   */
  public function testShouldSubmitFormWhenSaveConfiguration() {
    $form = [];

    $this->formStateMock
      ->expects($this->any())
      ->method('getTriggeringElement')
      ->willReturn([
        '#id' => 'submit',
      ]);

    $this->formStateMock
      ->expects($this->any())
      ->method('getValue')
      ->willReturn('manual');

    $this->configFactoryMock
      ->expects($this->any())
      ->method('getEditable')
      ->willReturn(
        $this->configMock
      );

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn(
        $this->configMock
      );

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('default_value');

    $this->configMock
      ->expects($this->any())
      ->method('set')
      ->willReturn($this->configMock);

    $this->configMock
      ->expects($this->any())
      ->method('delete');

    $this->configMock
      ->expects($this->any())
      ->method('save');

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'cache_tags.invalidator',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->cacheTagsInvalidatorMock,
          ],
          [
            'string_translation',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->translationMock,
          ],
        ]
      );

    $this->form->setMessenger($this->messengerMock);

    $this->messengerMock
      ->expects($this->once())
      ->method('addStatus');

    $this->cacheTagsInvalidatorMock
      ->expects($this->any())
      ->method('invalidateTags');

    $this->form->submitForm(
      $form,
      $this->formStateMock
    );

    $this->assertIsArray($form);
  }

  /**
   * Test.
   */
  public function testShouldBatchProcessItem() {
    $items = [
      [
        'salsify:id' => '123123',
        'GTIN' => '123123',
        'salsify:updated_at' => '123123',
        'CMS: Meta Description' => ['meta'],
        'Case Net Weight' => 'value',
        'CMS: Variety' => 'no',
      ],
      [
        'salsify:id' => '1231232',
        'GTIN' => '1231232',
        'salsify:updated_at' => '1231231',
        'CMS: Meta Description' => ['meta'],
        'Case Net Weight' => 'value',
        'CMS: Variety' => 'no',
      ],
    ];
    $context = [];

    $this->containerMock
      ->expects($this->any())
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
            'entity_field.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->fieldManagerMock,
          ],
          [
            'salsify_integration.product_data_helper',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->productHelperMock,
          ],
          [
            'salsify_integration.salsify_product_repository',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyProductRepoMock,
          ],
          [
            'module_handler',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->moduleHandlerMock,
          ],
        ]
      );

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->configMock);

    $this->configFactoryMock
      ->expects($this->any())
      ->method('listAll')
      ->willReturn(['config1']);

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('salsify_id');

    $this->configMock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'product_variant',
      ]);

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('getQuery')
      ->willReturn($this->queryMock);

    $this->queryMock
      ->expects($this->any())
      ->method('condition')
      ->willReturn($this->queryMock);

    $this->queryMock
      ->expects($this->any())
      ->method('execute')
      ->willReturn(['123']);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn($this->fieldableEntityMock);

    $this->fieldableEntityMock
      ->expects($this->any())
      ->method('set');

    $this->fieldManagerMock
      ->expects($this->any())
      ->method('getFieldDefinitions')
      ->willReturn([$this->fieldConfigMock]);

    $this->productHelperMock
      ->expects($this->any())
      ->method('validateDataRecord')
      ->willReturn(TRUE);

    $this->salsifyProductRepoMock
      ->expects($this->any())
      ->method('updateParentEntities');

    $this->moduleHandlerMock
      ->expects($this->any())
      ->method('alter');

    $this->fieldableEntityMock
      ->expects($this->any())
      ->method('save');

    $this->form::batchProcessItem(
      $items,
      TRUE,
      'product_variant',
      $context
    );
    $this->assertNotEmpty($context['sandbox']['progress']);
  }

  /**
   * Test.
   */
  public function testShouldBatchDeleteItems() {
    $context = [
      'results' => [],
    ];

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap([
          [
            'salsify_integration.salsify_product_repository',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyProductRepoMock,
          ],
      ]);

    $this->salsifyProductRepoMock
      ->expects($this->once())
      ->method('unpublishProducts')
      ->willReturn([1, 2, 3]);

    $this->form::batchDeleteItems(
      [1, 2, 3],
      $context
    );
    $this->assertIsArray($context['results']['deleted_items']);
  }

  /**
   * Test.
   */
  public function testShouldFinished() {
    $results = [
      'validation_errors' => ['errors'],
      'deleted_items' => [1],
    ];

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap([
          [
            'logger.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->loggerFactoryMock,
          ],
          [
            'salsify_integration.email_report',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->salsifyEmailReportMock,
          ],
          [
            'messenger',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->messengerMock,
          ],
      ]);

    $this->loggerFactoryMock
      ->expects($this->once())
      ->method('get')
      ->willReturn($this->loggerChannelMock);

    $this->loggerChannelMock
      ->expects($this->any())
      ->method('info');

    $this->salsifyEmailReportMock
      ->expects($this->once())
      ->method('sendReport');

    $this->messengerMock
      ->expects($this->once())
      ->method('addStatus');

    $this->form::finished(
      TRUE,
      $results,
      []
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
    $this->cacheTagsInvalidatorMock = $this->createMock(CacheTagsInvalidatorInterface::class);
    $this->queryMock = $this->createMock(QueryInterface::class);
    $this->fieldableEntityMock = $this->createMock(FieldableEntityInterface::class);
    $this->fieldManagerMock = $this->createMock(EntityFieldManagerInterface::class);
    $this->fieldConfigMock = $this->createMock(FieldConfig::class);
    $this->productHelperMock = $this->createMock(ProductHelper::class);
    $this->salsifyProductRepoMock = $this->createMock(SalsifyProductRepository::class);
    $this->loggerFactoryMock = $this->createMock(LoggerChannelFactoryInterface::class);
    $this->loggerChannelMock = $this->createMock(LoggerChannelInterface::class);
    $this->salsifyEmailReportMock = $this->createMock(SalsifyEmailReport::class);
    $this->queueFactoryMock = $this->createMock(QueueFactory::class);
    $this->queueMock = $this->createMock(QueueInterface::class);
    $this->migrationRunnerMock = $this->createMock(MigrationRunner::class);
    $this->runsResourceMock = $this->createMock(RunResource::class);
    $this->languageManagerMock = $this->createMock(LanguageManagerInterface::class);

    $this->queueFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->queueMock);
  }

}

namespace Drupal\salsify_integration\Form;

/**
 * {@inheritdoc}
 */
function batch_set(array $details) {
  return NULL;
}

/**
 * {@inheritdoc}
 */
function t(string $string) {
  return $string;
}
