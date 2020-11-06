<?php

namespace Drupal\mars_common\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Form\SiteInformationForm;

/**
 * Override for the site information form.
 *
 * @internal
 */
class MarsSiteInformationForm extends SiteInformationForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $site_config = $this->config('mars.system.site');

    $form['mars_site_information'] = [
      '#type' => 'details',
      '#title' => $this->t('Additional details'),
      '#open' => TRUE,
    ];
    $form['mars_site_information']['brand'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Brand'),
      '#default_value' => $site_config->get('brand'),
      '#description' => $this->t("How this is used depends on your site's theme."),
    ];
    $form['mars_site_information']['segment'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Segment'),
      '#default_value' => $site_config->get('segment'),
      '#description' => $this->t("How this is used depends on your site's theme."),
    ];
    $form['mars_site_information']['market'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Market'),
      '#default_value' => $site_config->get('market'),
      '#description' => $this->t("For example: US."),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory()->getEditable('mars.system.site')
      ->set('brand', $form_state->getValue('brand'))
      ->set('segment', $form_state->getValue('segment'))
      ->set('market', $form_state->getValue('market'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
