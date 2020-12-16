<?php

namespace Drupal\mars_seo\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_lighthouse\Traits\EntityBrowserFormTrait;

/**
 * Configure open graph settings for this site.
 */
class OpenGraphSettingForm extends ConfigFormBase {

  use EntityBrowserFormTrait;

  /**
   * Lighthouse entity browser id.
   */
  const LIGHTHOUSE_ENTITY_BROWSER_ID = 'lighthouse_browser';

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'mars_seo.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'open_graph_settings';
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

    $form['image'] = $this->getEntityBrowserForm(self::LIGHTHOUSE_ENTITY_BROWSER_ID,
      $config->get('og_default_image'), $form_state, 1, 'thumbnail');
    // Convert the wrapping container to a details element.
    $form['image']['#type'] = 'details';
    $form['image']['#title'] = $this->t('Default OG image');
    $form['image']['#open'] = TRUE;

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('og_default_image', $this->getEntityBrowserValue($form_state, 'browser'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
