<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Salesforce Form block.
 *
 * @Block(
 *   id = "salesforce_form",
 *   admin_label = @Translation("MARS: Salesforce Form"),
 *   category = @Translation("Mars Common")
 * )
 */
class SalesforceFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();

    $build['#form_type'] = $conf['form_type'] ?? 'formstack';
    $build['#form_id'] = $conf['form_id'] ?? '';
    $build['#theme'] = 'salesforce_form_block';

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
      '#default_value' => $this->configuration['form_type'] ?? 'formstack',
      '#required' => TRUE,
      '#options' => [
        'formstack' => $this->t('Contact form'),
        'salesforce'   => $this->t('Newsletter signup form'),
      ],
    ];

    $form['form_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form ID'),
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
    $this->configuration['form_type'] = $form_state->getValue('form_type');
    $this->configuration['form_id'] = $form_state->getValue('form_id');
  }

}
