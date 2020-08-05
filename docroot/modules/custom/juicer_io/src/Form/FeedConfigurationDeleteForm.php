<?php

declare(strict_types=1);

namespace Drupal\juicer_io\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Delete form for feed configuration entity.
 */
class FeedConfigurationDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %name?',
      ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.juicer_io_feed.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();

    $this->messenger()
      ->addStatus($this->t(
        'Juicer.io feed configuration deleted: @label.', [
          '@label' => $this->entity->label(),
        ]
      ));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
