<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an iFrame block.
 *
 * @Block(
 *   id = "iframe_block",
 *   admin_label = @Translation("MARS: iFrame"),
 *   category = @Translation("Mars Common")
 * )
 */
class IFrameBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();
    $build['#url'] = $conf['url'] ?? '';
    $build['#accessibility_title'] = $conf['accessibility_title'] ?? '';
    $build['#theme'] = 'iframe_block';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $conf = $this->getConfiguration();

    return [
      'label_display' => FALSE,
      'accessibility_title' => $conf['accessibility_title'] ?? '',
      'url' => $conf['url'] ?? '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $character_limit_config = \Drupal::config('mars_common.character_limit_page');
    $form['accessibility_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Accessibility Title'),
      '#maxlength' => !empty($character_limit_config->get('iframe_accessibility_title')) ? $character_limit_config->get('iframe_accessibility_title') : 150,
      '#default_value' => $this->configuration['accessibility_title'] ?? '',
      '#required' => TRUE,
    ];
    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $this->configuration['url'] ?? '',
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
    $this->configuration['url'] = $form_state->getValue('url');
    $this->configuration['accessibility_title'] = $form_state->getValue('accessibility_title');
  }

}
