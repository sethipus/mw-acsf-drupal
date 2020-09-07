<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Newsletter Signup Form block.
 *
 * @Block(
 *   id = "newsletter_signup_form",
 *   admin_label = @Translation("MARS: Newsletter Signup Form"),
 *   category = @Translation("Mars Common")
 * )
 */
class NewsletterSignupFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();

    $build['#form_type'] = $conf['form_type'] ?? 'widget';
    $build['#theme'] = 'newsletter_signup_form_block';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'label_display' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['form_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Form type'),
      '#default_value' => $this->configuration['form_type'] ?? 'widget',
      '#required' => TRUE,
      '#options' => [
        'widget' => $this->t('Widget form'),
        'page'   => $this->t('Form for page'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    parent::blockSubmit($form, $form_state);
    $this->configuration['form_type'] = $form_state->getValue('form_type');
  }

}
