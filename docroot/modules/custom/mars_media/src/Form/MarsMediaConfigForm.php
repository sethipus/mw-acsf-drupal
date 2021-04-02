<?php

namespace Drupal\mars_media\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for Mars Media module.
 */
class MarsMediaConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mars_media_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'mars_media.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('mars_media.settings');
    $cf_image_resizing = $config->get('cf_resize');

    $form['cf_resize'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Cloudflare image resize configuration'),
      '#description' => $this->t('Enable the cloudflare based image resize functionality. Find out more: https://developers.cloudflare.com/images/'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#tree' => TRUE,
    ];

    $form['cf_resize']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $cf_image_resizing['enabled'] ?? FALSE,
    ];

    $form['cf_resize']['bundles'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Media types'),
      '#description' => $this->t('List of media types where cloudflare resize should be applied.'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#states' => [
        'visible' => [
          [':input[name="cf_resize[enabled]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['cf_resize']['bundles']['image'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Local Drupal images'),
      '#default_value' => $cf_image_resizing['bundles']['image'] ?? FALSE,
    ];

    $form['cf_resize']['bundles']['lighthouse_image'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Lighthouse images'),
      '#default_value' => $cf_image_resizing['bundles']['lighthouse_image'] ?? FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('mars_media.settings');

    $config->set('cf_resize', $form_state->getValue('cf_resize'));

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
