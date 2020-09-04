<?php

namespace Drupal\Tests\mars_common\Unit\Plugin\Validation\Constraint;

use Drupal\mars_common\Plugin\Validation\Constraint\RegExValidationConstraint;
use Drupal\mars_common\Plugin\Validation\Constraint\RegExValidationConstraintValidator;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\mars_common\Plugin\Validation\Constraint\RegExValidationConstraint
 * @group mars
 * @group mars_common
 */
class RegExValidationConstraintTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\mars_common\Plugin\Validation\Constraint\RegExValidationConstraint
   */
  private $constraint;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->constraint = new RegExValidationConstraint([
      'regex' => '/^\d{0,2}\.{0,1}\d{0,2}$/i',
      'errorMessage' => 'Message',
    ]);
  }

  /**
   * Test.
   */
  public function testValidatedBy() {
    $validator = $this->constraint->validatedBy();
    $this->assertSame(
      RegExValidationConstraintValidator::class,
      $validator
    );
  }

}
