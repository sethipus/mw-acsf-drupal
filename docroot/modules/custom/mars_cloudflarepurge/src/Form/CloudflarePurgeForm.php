<?php

namespace Drupal\mars_cloudflarepurge\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\mars_cloudflarepurge\CloudflarePurgeCredentials;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Default ConfigFormBase for the mars_cloudflarepurge module.
 */
class CloudflarePurgeForm extends ConfigFormBase {

  /**
   * Request service.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Mars cloudflare purge constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, RequestStack $request_stack) {
    parent::__construct($config_factory);
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'cloudflarepurge.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId():string {
    return 'cloudflarepurge_form';
  }

  /**
   * Build the form.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form State interface.
   *
   * @return array
   *   Return array.
   */
  public function buildForm(array $form, FormStateInterface $form_state):array {
    $config = $this->configFactory()->getEditable('cloudflarepurge.settings');

    $form['cloudflarepurge_form']['zone_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Zone ID'),
      '#size' => 60,
      '#required' => TRUE,
      '#default_value' => !empty($config->get('zone_id')) ? $config->get('zone_id') : '',
      '#attributes' => [
        'placeholder' => [
          'Zone ID',
        ],
      ],
      '#description' => $this->t('Enter Cloudflare Zone Id.'),
    ];
    $form['cloudflarepurge_form']['authorization'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Authorization'),
      '#size' => 60,
      '#required' => TRUE,
      '#default_value' => !empty($config->get('authorization')) ? $config->get('authorization') : '',
      '#attributes' => [
        'placeholder' => [
          'Authorization',
        ],
      ],
      '#description' => $this->t('Enter Cloudflare Authorization Key.'),
    ];

    $form['cloudflarepurge_form']['purge_everything_toggle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Purge everything'),
      '#default_value' => $config->get('purge_everything_toggle') ?? FALSE,
    ];
    $form['cloudflarepurge_form']['purge_specific_url_toggle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Purge specific URL'),
      '#default_value' => $config->get('purge_specific_url_toggle') ?? FALSE,
    ];
    $form['cloudflarepurge_form']['purge_specific_url'] = [
      '#type' => 'textarea',
      '#default_value' => !empty($config->get('purge_specific_url')) ? $config->get('purge_specific_url') : '',
      '#description' => $this->t('<ul><li> Enter one URL per line</li><li>Do not add https:// or http:// on URLs</li><li>www.example.com/sample OR example.com/sample</li><li>While entering multiple URLs in Purge specific URL, base domain URL is not required if full path URL of same domain is present</li></ul>'),
      '#title' => $this->t('Specific URLs for cloudflare purge'),
      '#states' => [
        'visible' => [
          ':input[name="purge_specific_url_toggle"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#name' => 'submit',
      '#value' => $this->t('Clear Cache'),
      '#submit' => ['::cloudflareClearCache'],
    ];

    return parent::buildForm($form, $form_state);

  }

  /**
   * Cloudflare cache clear.
   */
  public function cloudflareClearCache(array &$form, FormStateInterface $form_state) {
    $zoneId = $form_state->getValue('zone_id');
    $authorization = $form_state->getValue('authorization');
    $purge_everything_toggle = $form_state->getValue('purge_everything_toggle');
    // Gettings specific URLs array changed to required format.
    $purge_specific_url_toggle = $form_state->getValue('purge_specific_url_toggle');
    $specific_url_values = $form_state->getValue('purge_specific_url');
    if ($specific_url_values && $purge_specific_url_toggle) {
      $specific_url_values = explode(PHP_EOL, $specific_url_values);
      foreach ($specific_url_values as $value) {
        if (!empty($value)) {
          $value = trim(preg_replace('/\s+/', ' ', $value));
          if ($value) {
            $specific_urls[] = $value;
          }
        }
      }
      $specific_urls = '"' . implode('","', $specific_urls) . '"';
    }
    else {
      $specific_urls = "";
    }
    // Purge everything for specific zone ID.
    if ($zoneId != NULL && $authorization != NULL && $purge_everything_toggle) {
      $results = CloudflarePurgeCredentials::cfPurgeCache($zoneId, $authorization, $specific_urls, $purge_specific_url_toggle);
      if ($results == 200) {
        $this->messenger()->addMessage($this->t('Cloudflare was purged everything successfully.'));
      }
      else {
        $this->messenger()->addError($this->t('Error is @results', ['@results' => $results]));
      }
    }
    // Cloudflare purge for specific URLs.
    elseif ($zoneId != NULL && $authorization != NULL && $purge_specific_url_toggle && $specific_urls) {
      $results = CloudflarePurgeCredentials::cfPurgeCache($zoneId, $authorization, $specific_urls, $purge_specific_url_toggle);
      if ($results == 200) {
        $this->messenger()->addMessage($this->t('Cloudflare was purged successfully for specific URLs.'));
      }
      else {
        $this->messenger()->addError($this->t('Error is @results', ['@results' => $results]));
      }
    }
    else {
      $this->messenger()->addError($this->t('Please choose one checkbox Cloudflare purge everything or Cloudflare purge for specific URLs.'));
    }

    return new RedirectResponse($this->getCurrentUrl());

  }

  /**
   * Cloudflare form validation.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $purge_everything_toggle = $form_state->getValue('purge_everything_toggle');
    $purge_specific_url_toggle = $form_state->getValue('purge_specific_url_toggle');
    $purge_specific_url = $form_state->getValue('purge_specific_url');
    if ($purge_everything_toggle && $purge_specific_url_toggle) {
      $form_state->setErrorByName('purge_everything_toggle', $this->t('Please choose one checkbox Cloudflare purge everything or Cloudflare purge for specific URLs.'));
      $form_state->setErrorByName('purge_specific_url_toggle', '');
    }
    if ($purge_specific_url_toggle && !$purge_specific_url) {
      $form_state->setErrorByName('purge_specific_url', $this->t('Please provide the specific URLs'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Save all configs on submit.
    $this->configFactory()->getEditable('cloudflarepurge.settings')
      ->set('zone_id', $form_state->getValue('zone_id'))
      ->set('authorization', $form_state->getValue('authorization'))
      ->set('purge_everything_toggle', $form_state->getValue('purge_everything_toggle'))
      ->set('purge_specific_url_toggle', $form_state->getValue('purge_specific_url_toggle'))
      ->set('purge_specific_url', $form_state->getValue('purge_specific_url'))
      ->save();
    parent::submitForm($form, $form_state);

  }

  /**
   * Stay on the same page.
   */
  public function getCurrentUrl() {
    return $this->requestStack->getCurrentRequest()->server->get('HTTP_REFERER');
  }

}
