<?php

namespace Drupal\mars_common\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the InlineEntityFormUniqueField constraint.
 */
class InlineEntityFormUniqueFieldConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if (!$item = $items->first()) {
      return;
    }

    $field_name = $items->getFieldDefinition()->getName();

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $items->getEntity();

    if (!isset($entity->_inline_entity_form_entities)) {
      return;
    }

    $matching_entities = array_filter($entity->_inline_entity_form_entities, function ($ief_entity) use ($entity, $field_name) {
      return $entity->get($field_name)->getValue() == $ief_entity->get($field_name)->getValue();
    });

    if (!empty($matching_entities)) {
      $this->context->addViolation($constraint->message, [
        '%value' => $item->value,
        '@entity_type' => $entity->getEntityType()->getSingularLabel(),
        '@field_name' => mb_strtolower($items->getFieldDefinition()->getLabel()),
      ]);
    }
  }

}
