<?php

namespace Drupal\mars_common\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Implementing Custom RegEx Validator Class.
 */
class RegExValidationConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $field = $this->context->getValue();
    $value = $field->getValue();
    if (!empty($value)) {
      if (!preg_match_all($constraint->regex, $value[0]['value'])) {
        $translation = \Drupal::translation();
        $this->context->addViolation(
          $translation->translate($constraint->errorMessage)
        );
      }
    }
  }

}
