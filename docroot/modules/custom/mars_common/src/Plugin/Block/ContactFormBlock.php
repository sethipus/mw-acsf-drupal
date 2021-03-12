<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Contact Form block.
 *
 * @Block(
 *   id = "contact_form",
 *   admin_label = @Translation("MARS: Contact Form"),
 *   category = @Translation("Mars Common")
 * )
 */
class ContactFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();

    $build['#form_id'] = $conf['form_id'] ?? '';
    $build['#theme'] = 'contact_form_block';

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

    $form['form_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form script endpoint'),
      '#default_value' => $this->configuration['form_id'] ?? '',
      '#required' => TRUE,
      '#size' => 65,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    parent::blockSubmit($form, $form_state);
    $this->configuration['form_id'] = $form_state->getValue('form_id');
  }

}
