<?php

namespace Drupal\Tests\salsify_integration\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\TempStore\SharedTempStore;
use Drupal\Core\TempStore\SharedTempStoreFactory;
use Drupal\salsify_integration\SalsifyEmailReport;
use Drupal\Tests\UnitTestCase;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\salsify_integration\SalsifyEmailReport
 * @group mars
 * @group salsify_integration
 */
class SalsifyEmailReportTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\SalsifyEmailReport
   */
  private $salsifyEmailReport;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ImmutableConfig
   */
  private $configMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityInterface
   */
  private $entityMock;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Mail\MailManagerInterface
   */
  private $mailManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\TempStore\SharedTempStoreFactory
   */
  private $sharedTempStoreFactoryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\TempStore\SharedTempStore
   */
  private $sharedTempStoreMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Language\LanguageInterface
   */
  private $languageMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityStorageInterface
   */
  private $entityStorageMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\user\Entity\User
   */
  private $userMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->salsifyEmailReport = new SalsifyEmailReport(
      $this->configFactoryMock,
      $this->entityTypeManagerMock,
      $this->mailManagerMock,
      $this->languageManagerMock,
      $this->sharedTempStoreFactoryMock,
      $this->loggerFactoryMock
    );
  }

  /**
   * Test.
   */
  public function testShouldSendReport() {
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

    $this->languageManagerMock
      ->expects($this->once())
      ->method('getCurrentLanguage')
      ->willReturn($this->languageMock);

    $this->languageMock
      ->expects($this->once())
      ->method('getId')
      ->willReturn('en');

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'salsify_import.email',
            'email@email.email',
          ],
          [
            'send_email',
            TRUE,
          ],
        ]
      );

    $this->entityTypeManagerMock
      ->expects($this->once())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->once())
      ->method('load')
      ->willReturn($this->userMock);

    $this->mailManagerMock
      ->expects($this->once())
      ->method('mail')
      ->willReturn(['result' => TRUE]);

    $this->loggerChannelMock
      ->expects($this->once())
      ->method('notice');

    $this->salsifyEmailReport->sendReport(
      ['error'],
      [1, 2, 3]
    );
  }

  /**
   * Test.
   */
  public function testShouldSendReportWhenException() {
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

    $this->languageManagerMock
      ->expects($this->once())
      ->method('getCurrentLanguage')
      ->willReturn($this->languageMock);

    $this->languageMock
      ->expects($this->once())
      ->method('getId')
      ->willReturn('en');

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'salsify_import.email',
            'email@email.email',
          ],
          [
            'send_email',
            TRUE,
          ],
        ]
      );

    $this->entityTypeManagerMock
      ->expects($this->once())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->once())
      ->method('load')
      ->willReturn($this->userMock);

    $this->mailManagerMock
      ->expects($this->once())
      ->method('mail')
      ->willThrowException(new \Exception('message'));

    $this->loggerChannelMock
      ->expects($this->once())
      ->method('error');

    $this->salsifyEmailReport->sendReport(
      ['error'],
      [1, 2, 3]
    );
  }

  /**
   * Test.
   */
  public function testShouldSaveValidationErrorsWhenNoSavedErrors() {
    $this->sharedTempStoreMock
      ->expects($this->once())
      ->method('get')
      ->willReturn(NULL);

    $this->sharedTempStoreMock
      ->expects($this->once())
      ->method('set');

    $this->salsifyEmailReport->saveValidationErrors(
      ['error']
    );
  }

  /**
   * Test.
   */
  public function testShouldSaveValidationErrorsWhenSavedErrors() {
    $this->sharedTempStoreMock
      ->expects($this->once())
      ->method('get')
      ->willReturn(['error 2']);

    $this->sharedTempStoreMock
      ->expects($this->once())
      ->method('set');

    $this->salsifyEmailReport->saveValidationErrors(
      ['error']
    );
  }

  /**
   * Test.
   */
  public function testShouldSendReportByCron() {
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

    $this->languageManagerMock
      ->expects($this->once())
      ->method('getCurrentLanguage')
      ->willReturn($this->languageMock);

    $this->languageMock
      ->expects($this->once())
      ->method('getId')
      ->willReturn('en');

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'salsify_import.email',
            'email@email.email',
          ],
          [
            'send_email',
            TRUE,
          ],
        ]
      );

    $this->entityTypeManagerMock
      ->expects($this->once())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->once())
      ->method('load')
      ->willReturn($this->userMock);

    $this->mailManagerMock
      ->expects($this->once())
      ->method('mail')
      ->willReturn(['result' => TRUE]);

    $this->loggerChannelMock
      ->expects($this->once())
      ->method('notice');

    $this->sharedTempStoreMock
      ->expects($this->once())
      ->method('get')
      ->willReturn(['error 2']);

    $this->sharedTempStoreMock
      ->expects($this->once())
      ->method('set');

    $this->salsifyEmailReport->sendReportByCron();
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->configMock = $this->createMock(ImmutableConfig::class);
    $this->entityMock = $this->createMock(EntityInterface::class);
    $this->loggerFactoryMock = $this->createMock(LoggerChannelFactoryInterface::class);
    $this->loggerChannelMock = $this->createMock(LoggerChannelInterface::class);
    $this->mailManagerMock = $this->createMock(MailManagerInterface::class);
    $this->languageManagerMock = $this->createMock(LanguageManagerInterface::class);
    $this->sharedTempStoreFactoryMock = $this->createMock(SharedTempStoreFactory::class);
    $this->sharedTempStoreMock = $this->createMock(SharedTempStore::class);
    $this->languageMock = $this->createMock(LanguageInterface::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->user = $this->createMock(User::class);

    $this->loggerFactoryMock
      ->expects($this->once())
      ->method('get')
      ->willReturn($this->loggerChannelMock);

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->configMock);

    $this->sharedTempStoreFactoryMock
      ->expects($this->once())
      ->method('get')
      ->willReturn($this->sharedTempStoreMock);
  }

}
