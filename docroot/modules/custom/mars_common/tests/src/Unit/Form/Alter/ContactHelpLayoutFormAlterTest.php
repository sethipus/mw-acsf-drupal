<?php

namespace Drupal\Tests\mars_common\Unit\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\layout_builder\DefaultsSectionStorageInterface;
use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\layout_builder\Form\DefaultsEntityForm;
use Drupal\layout_builder\Section;
use Drupal\mars_common\Form\Alter\ContactHelpLayoutFormAlter;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\mars_common\Form\Alter\ContactHelpLayoutFormAlter
 * @group mars
 * @group mars_common
 */
class ContactHelpLayoutFormAlterTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_common\Form\Alter\ContactHelpLayoutFormAlter
   */
  private $contactForm;

  /**
   * Mock.
   *
   * @var \Drupal\Core\Form\FormStateInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $formStateMock;

  /**
   * Mock.
   *
   * @var \Drupal\layout_builder\DefaultsSectionStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $sectionStorageMock;

  /**
   * Mock.
   *
   * @var \Drupal\layout_builder\Form\DefaultsEntityForm|\PHPUnit\Framework\MockObject\MockObject
   */
  private $entityFormMock;

  /**
   * Mock.
   *
   * @var \Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay|\PHPUnit\Framework\MockObject\MockObject
   */
  private $layoutBuilderEntityViewDisplayMock;

  /**
   * Mock.
   *
   * @var \Drupal\layout_builder\Section|\PHPUnit\Framework\MockObject\MockObject
   */
  private $sectionMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();

    $this->contactForm = new ContactHelpLayoutFormAlter();
  }

  /**
   * Test.
   */
  public function testValidateWhenError() {
    $form = [];

    $this->formStateMock
      ->expects($this->once())
      ->method('getFormObject')
      ->willReturn($this->entityFormMock);

    $this->entityFormMock
      ->expects($this->once())
      ->method('getSectionStorage')
      ->willReturn($this->sectionStorageMock);

    $this->sectionStorageMock
      ->expects($this->once())
      ->method('getContextValue')
      ->willReturn($this->layoutBuilderEntityViewDisplayMock);

    $this->layoutBuilderEntityViewDisplayMock
      ->expects($this->once())
      ->method('getThirdPartySettings')
      ->willReturn([
        'sections' => [$this->sectionMock],
      ]);

    $this->sectionMock
      ->expects($this->any())
      ->method('getLayoutId')
      ->willReturn('contact_help_parent_page_header');

    $this->sectionMock
      ->expects($this->once())
      ->method('getComponents')
      ->willReturn([]);

    $this->formStateMock
      ->expects($this->once())
      ->method('setErrorByName');

    $this->sectionMock
      ->expects($this->once())
      ->method('getLayoutSettings')
      ->willReturn(['label' => 'test_label']);

    $this->contactForm::validate($form, $this->formStateMock);
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->formStateMock = $this->createMock(FormStateInterface::class);
    $this->sectionStorageMock = $this->createMock(DefaultsSectionStorageInterface::class);
    $this->entityFormMock = $this->createMock(DefaultsEntityForm::class);
    $this->layoutBuilderEntityViewDisplayMock = $this->createMock(LayoutBuilderEntityViewDisplay::class);
    $this->sectionMock = $this->createMock(Section::class);
  }

}

namespace Drupal\mars_common\Form\Alter;

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
