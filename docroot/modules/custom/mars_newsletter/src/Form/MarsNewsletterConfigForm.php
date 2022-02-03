<?php
 
/**
 * Configuration for newsletter form.
 */
namespace Drupal\mars_newsletter\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\Config;
use Drupal\mars_common\Traits\OverrideThemeTextColorTrait;
use Drupal\mars_common\ThemeConfiguratorParser;
 
class MarsNewsletterConfigForm extends ConfigFormBase {
 
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'newsletter_form';
  }
 
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
 
    $form = parent::buildForm($form, $form_state);
    $config = $this->configFactory()->getEditable('mars_newsletter_form.settings')->get('newsletter.config_form');
 
    $form['newsletter_toggle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display alert banner newsletter form.'),
      '#default_value' => $config['newsletter_toggle'] ?? FALSE,
    ];

    $form['override_white_color'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Override default theme text color configuration with white for the selected component.'),
      '#default_value' => $config['override_white_color'] ?? FALSE,
    ];

    $form['alert_banner_newsletter_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Alert banner newsletter form title'),
      '#required' => FALSE,
      '#default_value' => $config['alert_banner_newsletter_name'] ?? $this->t('Sign up for newsletter'),
      '#description' => $this->t("For translation, clear this field and use this 'Sign up for newsletter' in User interface translation."),
    ];

    $form['field_required_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Required field message'),
      '#required' => FALSE,
      '#default_value' => $config['field_required_message'] ?? $this->t('This field is required'),
      '#description' => $this->t("For translation, clear this field and use this 'This field is required' in User interface translation."),
    ];

    $form['email_validation_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email validation message'),
      '#required' => FALSE,
      '#default_value' => $config['email_validation_message'] ?? $this->t('Enter valid email ID'),
      '#description' => $this->t("For translation, clear this field and use this 'Enter valid email ID' in User interface translation."),
    ];

    $form['success_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Success message'),
      '#required' => FALSE,
      '#default_value' => $config['success_message'] ?? $this->t('Your subcription is successful'),
      '#description' => $this->t("For translation, clear this field and use this 'Your subcription is successful' in User interface translation."),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];
 
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
 
    $config = $this->configFactory()->getEditable('mars_newsletter_form.settings');
    $config->set('newsletter.config_form', $form_state->getValues());
    $config->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'mars_newsletter_form.settings',
    ];
  }
}
