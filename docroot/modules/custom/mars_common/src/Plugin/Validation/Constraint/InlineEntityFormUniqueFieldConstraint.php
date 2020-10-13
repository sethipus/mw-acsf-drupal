<?php

namespace Drupal\mars_common\Plugin\Validation\Constraint;

use Drupal\Core\Validation\Plugin\Validation\Constraint\UniqueFieldConstraint;

/**
 * Provides an InlineEntityFormUniqueField constraint.
 *
 * @Constraint(
 *   id = "MarsCommonInlineEntityFormUniqueField",
 *   label = @Translation("InlineEntityFormUniqueField", context = "Validation"),
 * )
 *
 * @DCG
 * To apply this constraint on third party entity types implement either
 * hook_entity_base_field_info_alter() or hook_entity_bundle_field_info_alter().
 */
class InlineEntityFormUniqueFieldConstraint extends UniqueFieldConstraint {

  /**
   * Error message pattern.
   *
   * @var string
   */
  public $message = 'A @entity_type with @field_name %value already exists.';

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Drupal\mars_common\Plugin\Validation\Constraint\InlineEntityFormUniqueFieldConstraintValidator';
  }

}
