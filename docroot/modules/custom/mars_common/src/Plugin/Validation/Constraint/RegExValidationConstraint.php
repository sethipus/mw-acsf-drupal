<?php

namespace Drupal\mars_common\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Regexp constraint.
 *
 * @Constraint(
 *   id = "RegExValidationConstraint",
 *   label = @Translation("RegEx Validation"),
 * )
 */
class RegExValidationConstraint extends Constraint {

  /**
   * Regex string.
   *
   * @var string
   */
  public $regex;

  /**
   * Error message.
   *
   * @var string
   */
  public $errorMessage;

  /**
   * Class constructor.
   *
   * @param array $options
   *   Options.
   */
  public function __construct(array $options) {
    $options = [
      'regex' => $options['regex'],
      'errorMessage' => $options['errorMessage'],
    ];
    parent::__construct($options);
  }

}
