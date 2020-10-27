<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Text component block.
 *
 * @Block(
 *   id = "text_block",
 *   admin_label = @Translation("MARS: Text block"),
 *   category = @Translation("Page components"),
 * )
 */
class TextBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['header'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header'),
      '#maxlength' => 55,
      '#default_value' => $config['header'] ?? '',
    ];

    $form['drop_cap'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Drop cap'),
      '#default_value' => $config['drop_cap'] ?? FALSE,
    ];

    $form['body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#default_value' => $config['body']['value'] ?? '',
    ];

    return $form;
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
  public function build() {
    $config = $this->getConfiguration();

    $build['#content'] = $config['body']['value'];
    $build['#drop_cap'] = $config['drop_cap'];

    $build['#theme'] = 'text_block';

    return $build;
  }

}
