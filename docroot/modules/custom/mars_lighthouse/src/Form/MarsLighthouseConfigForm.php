<?php

namespace Drupal\mars_lighthouse\Form;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_lighthouse\Client\LighthouseDefaultsProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration form for Mars lighthouse integration.
 */
class MarsLighthouseConfigForm extends ConfigFormBase {

  /**
   * Service to get default values.
   *
   * @var \Drupal\mars_lighthouse\Client\LighthouseDefaultsProvider
   */
  private $defaultsProvider;

  /**
   * Default api key.
   */
  const DEFAULT_API_KEY = 'v1';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('mars_lighthouse.config_defaults_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    LighthouseDefaultsProvider $defaults_provider
  ) {
    parent::__construct($config_factory);
    $this->defaultsProvider = $defaults_provider;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mars_lighthouse_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'mars_lighthouse.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('mars_lighthouse.settings');

    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#default_value' => $config->get('client_id'),
      '#attributes' => [
        'placeholder' => $this->defaultsProvider->getDefaultUsername(),
      ],
    ];

    $form['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client secret'),
      '#default_value' => $config->get('client_secret'),
      '#attributes' => [
        'placeholder' => $this->defaultsProvider->getDefaultPassword(),
      ],
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key'),
      '#default_value' => $config->get('api_key'),
      '#attributes' => [
        'placeholder' => $this->defaultsProvider->getDefaultApiKey(),
      ],
    ];

    $form['base_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base path'),
      '#default_value' => $config->get('base_path'),
      '#attributes' => [
        'placeholder' => $this->defaultsProvider->getDefaultBasePath(),
      ],
    ];

    $form['subpath'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subpath'),
      '#default_value' => $config->get('subpath'),
      '#attributes' => [
        'placeholder' => $this->defaultsProvider->getDefaultSubpath(),
      ],
    ];

    $form['port'] = [
      '#type' => 'number',
      '#title' => $this->t('Port'),
      '#default_value' => $config->get('port'),
      '#attributes' => [
        'placeholder' => $this->defaultsProvider->getDefaultPort(),
      ],
    ];

    $form['sync_mode'] = [
      '#title' => $this->t('Use bulk sync mode'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('sync_mode'),
    ];

    $form['api_version'] = [
      '#type' => 'select',
      '#title' => $this->t('Lighthouse API version'),
      '#options' => [
        'v1' => $this->t('v1'),
        'v2' => $this->t('v2'),
      ],
      '#default_value' => $config->get('api_version') ?? static::DEFAULT_API_KEY,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('mars_lighthouse.settings');
    $this->setConfigValue($config, $form_state, 'client_id');
    $this->setConfigValue($config, $form_state, 'client_secret');
    $this->setConfigValue($config, $form_state, 'api_key');
    $this->setConfigValue($config, $form_state, 'base_path');
    $this->setConfigValue($config, $form_state, 'subpath');
    $this->setConfigValue($config, $form_state, 'port');
    $this->setConfigValue($config, $form_state, 'sync_mode');
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Sets a config value, if it's empty then changes it to null.
   *
   * @param \Drupal\Core\Config\Config $config
   *   The config object where we are setting values.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the submit.
   * @param string $key
   *   The key that we are trying to save.
   *
   * @throws \Drupal\Core\Config\ConfigValueException
   */
  private function setConfigValue(
    Config $config,
    FormStateInterface $form_state,
    string $key
  ) {
    $value = $form_state->getValue($key);
    $config->set($key, empty($value) ? NULL : $value);
  }

}
