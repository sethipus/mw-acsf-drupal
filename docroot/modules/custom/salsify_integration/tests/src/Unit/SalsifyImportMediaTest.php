<?php

namespace Drupal\Tests\salsify_integration\Unit;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Utility\Token;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\salsify_integration\Salsify;
use Drupal\salsify_integration\SalsifyImportMedia;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\FileRepositoryInterface;

/**
 * @coversDefaultClass \Drupal\salsify_integration\SalsifyImportMedia
 * @group mars
 * @group salsify_integration
 */
class SalsifyImportMediaTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\SalsifyImportMedia
   */
  private $salsifyImportMedia;

  /**
   * Mock.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $containerMock;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandlerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ImmutableConfig
   */
  private $configMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ImmutableConfig
   */
  private $config2Mock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Cache\CacheBackendInterface
   */
  private $cacheBackendMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\salsify_integration\Salsify
   */
  private $salsifyMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translationMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Utility\Token
   */
  private $tokenMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\File\FileSystemInterface
   */
  private $fileSystemMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityStorageInterface
   */
  private $entityStorageMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\media\Entity\Media
   */
  private $entityMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\Query\QueryInterface
   */
  private $queryMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\file\Entity\File
   */
  private $fileMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\file\FileRepositoryInterface
   */
  private $fileRepositoryMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);
    $this->salsifyImportMedia = new SalsifyImportMedia(
      $this->configFactoryMock,
      $this->entityTypeManagerMock,
      $this->cacheBackendMock,
      $this->salsifyMock,
      $this->moduleHandlerMock,
      $this->tokenMock,
      $this->fileSystemMock,
      $this->fileRepositoryMock
    );
  }

  /**
   * Test.
   */
  public function testShouldProcessSalsifyMediaItem() {
    $field = [
      'salsify_id' => 'media_field',
    ];
    $product_data = [
      'media_field' => 'media_1',
      'salsify:digital_assets' => [
        [
          'salsify:id' => 'media_1',
          'salsify:updated_at' => 'now',
          'salsify:salsify:created_at' => 'now',
          'salsify:url' => 'url',
          'salsify:filename' => 'filename?query',
          'salsify:name' => 'media_1',
          'salsify:status' => 'ok',
        ],
      ],
    ];

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'entity_type.manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->entityTypeManagerMock,
          ],
          [
            'config.factory',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->configFactoryMock,
          ],
        ]
      );

    $this->entityTypeManagerMock
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityStorageMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('load')
      ->willReturn($this->entityMock);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('loadByProperties')
      ->willReturn([]);

    $this->entityStorageMock
      ->expects($this->any())
      ->method('getQuery')
      ->willReturn($this->queryMock);

    $this->queryMock
      ->expects($this->any())
      ->method('condition')
      ->willReturnSelf();

    $this->queryMock
      ->expects($this->any())
      ->method('execute')
      ->willReturn([1]);

    $this->entityMock
      ->expects($this->any())
      ->method('getChangedTime')
      ->willReturn(strtotime('now') - 10000);

    $this->entityMock
      ->expects($this->any())
      ->method('bundle')
      ->willReturn('bundle');

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'config1',
            $this->configMock,
          ],
          [
            'config2',
            $this->config2Mock,
          ],
        ]);

    $this->configFactoryMock
      ->expects($this->any())
      ->method('listAll')
      ->willReturn(['config1', 'config2']);

    $this->configFactoryMock
      ->expects($this->any())
      ->method('getEditable')
      ->willReturn($this->configMock);

    $this->configMock
      ->expects($this->any())
      ->method('get')
      ->willReturn('salsify:url');

    $this->config2Mock
      ->expects($this->any())
      ->method('get')
      ->willReturn('field_name');

    $this->configMock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'product',
        'method' => 'manual',
        'entity_type' => 'node',
        'salsify_data_type' => 'complex',
      ]);

    $this->config2Mock
      ->expects($this->any())
      ->method('getRawData')
      ->willReturn([
        'bundle' => 'product',
        'method' => 'manual',
        'entity_type' => 'node',
        'salsify_data_type' => 'complex',
      ]);

    $this->entityMock
      ->expects($this->any())
      ->method('getName')
      ->willReturn('name');

    $result = $this->salsifyImportMedia->processSalsifyMediaItem(
      $field,
      $product_data
    );
    $this->assertNotEmpty($result);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->moduleHandlerMock = $this->createMock(ModuleHandlerInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->configMock = $this->createMock(ImmutableConfig::class);
    $this->config2Mock = $this->createMock(ImmutableConfig::class);
    $this->cacheBackendMock = $this->createMock(CacheBackendInterface::class);
    $this->salsifyMock = $this->createMock(Salsify::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->tokenMock = $this->createMock(Token::class);
    $this->fileSystemMock = $this->createMock(FileSystemInterface::class);
    $this->entityStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->entityMock = $this->createMock(Media::class);
    $this->queryMock = $this->createMock(QueryInterface::class);
    $this->fileMock = $this->createMock(File::class);
    $this->fileRepositoryMock = $this->createMock(FileRepositoryInterface::class);

    $this->configFactoryMock
      ->expects($this->any())
      ->method('get')
      ->willReturn($this->configMock);
  }

}

namespace Drupal\salsify_integration;

/**
 * Mock implementation of function.
 *
 * @param mixed $url
 *   File url.
 *
 * @return string
 *   File content.
 */
function file_get_contents($url) {
  return 'file_content';
}

/**
 * Mock implementation of function.
 *
 * @param string $data
 *   Data.
 * @param string|null $destination
 *   Destination.
 * @param int $replace
 *   The replace behavior.
 *
 * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
 * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
 *
 * @return mixed
 *   File entity or null.
 */
function file_save_data($data, $destination, $replace) {
  // In order to mock please use \Drupal::entityTypeManager()-...
  return NULL;
}
