<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Validation\Constraint;

use Drupal;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\mars_common\Plugin\Validation\Constraint\RegExValidationConstraint;
use Drupal\mars_common\Plugin\Validation\Constraint\RegExValidationConstraintValidator;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @coversDefaultClass \Drupal\mars_common\Plugin\Validation\Constraint\RegExValidationConstraintValidator
 * @group mars
 * @group mars_common
 */
class RegExValidationConstraintValidatorTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_common\Plugin\Validation\Constraint\RegExValidationConstraintValidator
   */
  private $validator;

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
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Validator\Context\ExecutionContextInterface
   */
  private $contextMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\mars_common\Plugin\Validation\Constraint\RegExValidationConstraint
   */
  private $constraintMock;

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Field\FieldItemListInterface
   */
  private $fieldItemListMock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createMocks();
    Drupal::setContainer($this->containerMock);

    $this->validator = new RegExValidationConstraintValidator();
  }

  /**
   * Test.
   */
  public function testValidateWhenViolation() {
    $this->constraintMock->regex = '/^\d{0,2}\.{0,1}\d{0,2}$/i';
    $this->constraintMock->errorMessage = 'Error message.';

    $this->contextMock
      ->expects($this->once())
      ->method('getValue')
      ->willReturn($this->fieldItemListMock);

    $this->fieldItemListMock
      ->expects($this->once())
      ->method('getValue')
      ->willReturn([
        ['value' => '112345'],
      ]);

    $this->containerMock
      ->expects($this->once())
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

    $this->contextMock
      ->expects($this->once())
      ->method('addViolation');

    $this->validator->initialize($this->contextMock);
    $this->validator->validate(
      'value',
      $this->constraintMock
    );
  }

  /**
   * Test.
   */
  public function testValidateWhenNoViolation() {
    $this->constraintMock->regex = '/^\d{0,2}\.{0,1}\d{0,2}$/i';
    $this->constraintMock->errorMessage = 'Error message.';

    $this->contextMock
      ->expects($this->once())
      ->method('getValue')
      ->willReturn($this->fieldItemListMock);

    $this->fieldItemListMock
      ->expects($this->once())
      ->method('getValue')
      ->willReturn([
        ['value' => '1.1'],
      ]);

    $this->validator->initialize($this->contextMock);
    $this->validator->validate(
      'value',
      $this->constraintMock
    );
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->containerMock = $this->createMock(ContainerInterface::class);
    $this->translationMock = $this->createMock(TranslationInterface::class);
    $this->contextMock = $this->createMock(ExecutionContextInterface::class);
    $this->constraintMock = $this->createMock(RegExValidationConstraint::class);
    $this->fieldItemListMock = $this->createMock(FieldItemListInterface::class);
  }

}
