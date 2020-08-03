<?php

declare(strict_types=1);

namespace Drupal\juicer_io\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for FeedConfiguration entity.
 */
class FeedConfigurationForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\juicer_io\Entity\FeedConfiguration $feed */
    $feed = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $feed->label(),
      '#description' => $this->t('Label for the feed.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $feed->id(),
      '#machine_name' => [
        'exists' => '\Drupal\juicer_io\Entity\FeedConfiguration::load',
      ],
      '#disabled' => !$feed->isNew(),
    ];

    $form['feed_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Feed id'),
      '#maxlength' => 255,
      '#description' => $this->t('The id of the feed. Example: abcd-e764540b-84ec-4aaa-908b-4e69dc5c7ef8'),
      '#default_value' => $feed->get('feed_id'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\juicer_io\Entity\FeedConfiguration $feed */
    $feed = $this->entity;
    $status = $feed->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('Created the %label feed.', [
          '%label' => $feed->label(),
        ]));
        break;

      default:
        $this->messenger()->addStatus($this->t('Saved the %label feed.', [
          '%label' => $feed->label(),
        ]));
    }
    $form_state->setRedirectUrl($feed->toUrl('collection'));
  }

}
