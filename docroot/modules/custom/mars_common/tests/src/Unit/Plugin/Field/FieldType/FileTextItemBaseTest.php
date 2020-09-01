<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Field\FieldType;

use Drupal\Core\Entity\Plugin\DataType\EntityReference;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\file\Entity\File;
use Drupal\mars_common\Plugin\Field\FieldType\CarouselItem;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\TypedData\FieldItemDataDefinitionInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;

/**
 * @coversDefaultClass \Drupal\mars_common\Plugin\Field\FieldType\FileTextItemBase
 * @group mars
 * @group mars_common
 */
class FileTextItemBaseTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_common\Plugin\Field\FieldType\FileTextItemBase
   */
  private $fileTextItemBase;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\TypedData\FieldItemDataDefinitionInterface
   */
  private $definitionMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\TypedData\DataDefinitionInterface
   */
  private $dataDefinitionMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\TypedData\TypedDataManagerInterface
   */
  private $typedDataManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\TypedData\TypedDataInterface
   */
  private $typedDataMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\FieldStorageDefinitionInterface
   */
  private $fieldStorageMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\FieldDefinitionInterface
   */
  private $fieldDefinitionMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   */
  private $streamWrapperManagerMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\file\Entity\File
   */
  private $fileMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\Plugin\DataType\EntityReference
   */
  private $entityReferenceMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    \Drupal::setContainer($this->containerMock);

    $this->fileTextItemBase = new CarouselItem(
      $this->definitionMock
    );
  }

  /**
   * Test.
   */
  public function testSchema() {
    $schema = $this->fileTextItemBase::schema($this->fieldStorageMock);
    $this->assertIsArray($schema);
  }

  /**
   * Test.
   */
  public function testDefaultFieldSettings() {
    $settings = $this->fileTextItemBase::defaultFieldSettings();
    $this->assertIsArray($settings);
  }

  /**
   * Test.
   */
  public function testStorageSettingsForm() {
    $form = [];

    $this->definitionMock
      ->expects($this->any())
      ->method('getFieldDefinition')
      ->willReturn($this->fieldDefinitionMock);

    $this->fieldDefinitionMock
      ->expects($this->once())
      ->method('getFieldStorageDefinition')
      ->willReturn($this->fieldStorageMock);

    $this->fieldStorageMock
      ->expects($this->once())
      ->method('getSettings')
      ->willReturn([
        'uri_scheme' => 'public',
      ]);

    $element = $this->fileTextItemBase->storageSettingsForm(
      $form,
      $this->formStateMock,
      FALSE
    );
    $this->assertIsArray($element);
    $this->assertIsArray($element['uri_scheme']);
  }

  /**
   * Test.
   */
  public function testFieldSettingsForm() {
    $form = [];

    $this->definitionMock
      ->expects($this->any())
      ->method('getFieldDefinition')
      ->willReturn($this->fieldDefinitionMock);

    $this->fieldDefinitionMock
      ->expects($this->any())
      ->method('getSettings')
      ->willReturn([
        'uri_scheme' => 'public',
        'desc_field' => 'default value',
        'desc_field_required' => 1,
        'file_directory' => 'dir',
        'max_filesize' => 512,
        'description_field' => 'desc',
        'file_extensions' => 'jpg',
      ]);

    $element = $this->fileTextItemBase->fieldSettingsForm(
      $form,
      $this->formStateMock
    );
    $this->assertIsArray($element);
    $this->assertIsArray($element['desc_field']);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->definitionMock = $this->createMock(FieldItemDataDefinitionInterface::class);
    $this->dataDefinitionMock = $this->createMock(DataDefinitionInterface::class);
    $this->typedDataManagerMock = $this->createMock(TypedDataManagerInterface::class);
    $this->typedDataMock = $this->createMock(TypedDataInterface::class);

    $this->fieldStorageMock = $this->createMock(FieldStorageDefinitionInterface::class);
    $this->fieldDefinitionMock = $this->createMock(FieldDefinitionInterface::class);
    $this->streamWrapperManagerMock = $this->createMock(StreamWrapperManagerInterface::class);
    $this->fileMock = $this->createMock(File::class);
    $this->entityReferenceMock = $this->createMock(EntityReference::class);

    $this->definitionMock
      ->expects($this->once())
      ->method('getPropertyDefinitions')
      ->willReturn([
        'target_id' => $this->dataDefinitionMock,
        'entity' => $this->dataDefinitionMock,
        'desc' => $this->dataDefinitionMock,
      ]);

    $this->containerMock
      ->expects($this->any())
      ->method('get')
      ->willReturnMap(
        [
          [
            'typed_data_manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->typedDataManagerMock,
          ],
          [
            'stream_wrapper_manager',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->streamWrapperManagerMock,
          ],
          [
            'string_translation',
            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
            $this->translationMock,
          ],
        ]
      );

    $this->typedDataManagerMock
      ->expects($this->any())
      ->method('getPropertyInstance')
      ->willReturn($this->typedDataMock);

  }

}

namespace Drupal\file\Plugin\Field\FieldType;

/**
 * Stub for drupal translation function.
 *
 * @param string $translatable
 *   String to be translated.
 *
 * @return string
 *   Result.
 */
function t($translatable) {
  return $translatable . ' translated';
}

/**
 * Stub for drupal format_size function.
 *
 * @param int $size
 *   Size.
 *
 * @return string
 *   Result.
 */
function format_size($size) {
  return $size . ' size';
}
