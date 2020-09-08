<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'MARS: Freeform Story Block' Block.
 *
 * @Block(
 *   id = "freeform_story_block",
 *   admin_label = @Translation("MARS: Freeform Story Block"),
 *   category = @Translation("Mars Common"),
 * )
 */
class FreeformStoryBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['block_aligned'] = [
      '#type' => 'select',
      '#title' => $this->t('Block aligned'),
      '#default_value' => $this->configuration['block_aligned'],
      '#options' => [
        'left_aligned' => $this->t('Left aligned'),
        'right_aligned' => $this->t('Right aligned'),
        'center_aligned' => $this->t('Center aligned'),
      ],
    ];
    $form['header_1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header 1'),
      '#default_value' => $this->configuration['header_1'],
      '#required' => TRUE,
    ];
    $form['header_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header 2'),
      '#default_value' => $this->configuration['header_2'],
    ];
    $form['body'] = [
      '#type' => 'body',
      '#title' => $this->t('Description'),
      '#default_value' => $this->configuration['body'],
      '#maxlength' => 1000,
      '#required' => TRUE,
    ];
    $form['background_shape'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Background shape'),
      '#default_value' => $this->configuration['background_shape'] ?? FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#block_aligned'] = $this->configuration['block_aligned'];
    $build['#header_1'] = $this->configuration['header_1'];
    $build['#header_2'] = $this->configuration['header_2'];
    $build['#body'] = $this->configuration['body'];
    $build['#background_shape'] = $this->configuration['background_shape'];
    $build['#theme'] = 'freeform_story_block';

    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * This method processes the blockForm() form fields when the block
   * configuration form is submitted.
   *
   * The blockValidate() method can be used to validate the form submission.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = $this->getConfiguration();
    return [
      'block_aligned' => $config['block_aligned'] ?? '',
      'header_1' => $config['header_1'] ?? $this->t('Header 1'),
      'header_2' => $config['header_2'] ?? '',
      'body' => $config['body'] ?? '',
      'background_shape' => $config['background_shape'] ?? '',
    ];
  }

}
