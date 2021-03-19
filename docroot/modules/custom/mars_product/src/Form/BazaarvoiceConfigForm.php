<?php

namespace Drupal\mars_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure bazaarvoice settings for this site.
 */
class BazaarvoiceConfigForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'mars_product.bazaarvoice.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bazaarvoice_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['staging_script_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bazaarvoice staging script url.'),
      '#description' => $this->t(
        'Ex. //apps.bazaarvoice.com/deployments/[brand_name]/main_site/[environment]/[language]/bv.js<br>
[brand_name], [environment] and [language] need to be fill out with right data'
      ),
      '#default_value' => $config->get('staging_script_url'),
    ];

    $form['production_script_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bazaarvoice production script url.'),
      '#description' => $this->t(
        'Ex. //apps.bazaarvoice.com/deployments/[brand_name]/main_site/[environment]/[language]/bv.js<br>
[brand_name], [environment] and [language] need to be fill out with right data'
      ),
      '#default_value' => $config->get('production_script_url'),
    ];

    $form['product_rating_and_reviews'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Product rating and reviews'),
      '#description' => $this->t("Rating and reviews"),
    ];
    $form['product_rating_and_reviews']['show_rating_and_reviews'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Turn on/off rating and reviews per site for all products'),
      '#default_value' => $config->get('show_rating_and_reviews') ?? FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('staging_script_url', $form_state->getValue('staging_script_url'))
      ->set('production_script_url', $form_state->getValue('production_script_url'))
      ->set('show_rating_and_reviews', $form_state->getValue('show_rating_and_reviews'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
