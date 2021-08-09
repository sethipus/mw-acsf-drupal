<?php

namespace Drupal\pn_acquia_search\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\acquia_search\Helper\Storage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Overrides Acquia search settings to facilitate local development.
 */
class AcquiaSearchOverrideForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pn_acquia_search_override_form';
  }

  /**
   * Centralized place for accessing and updating Acquia Search Solr settings.
   *
   * @var \Drupal\acquia_search\Helper\Storage
   */
  protected $storage;

  /**
   * A cache backend interface.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Constructs a new SearchOverlayForm.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   A cache backend interface.
   */
  public function __construct(CacheBackendInterface $cache) {
    $this->storage = new Storage();
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cache.default'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('pn_acquia_search.settings');

    $form['identifier'] = [
      '#title' => $this->t('Acquia Subscription identifier'),
      '#type' => 'textfield',
      '#default_value' => $this->storage->getIdentifier(),
      '#description' => $this->t('Obtain this from the "Product Keys" section of the Acquia Cloud UI. Example: ABCD-12345'),
    ];
    $form['api_key'] = [
      '#title' => $this->t('Acquia Connector key'),
      '#type' => 'password',
      '#description' => !empty($this->storage->getApiKey()) ? $this->t('Value already provided.') : $this->t('Obtain this from the "Product Keys" section of the Acquia Cloud UI.'),
      '#required' => empty($this->storage->getApiKey()),
    ];
    $form['uuid'] = [
      '#title' => $this->t('Acquia Application UUID'),
      '#type' => 'textfield',
      '#default_value' => $this->storage->getUuid(),
      '#description' => $this->t('Obtain this from the "Product Keys" section of the Acquia Cloud UI.'),
    ];
    $form['api_host'] = [
      '#title' => $this->t('Acquia Search API hostname'),
      '#type' => 'textfield',
      '#description' => $this->t('API endpoint domain or URL. Default value is "https://api.sr-prod02.acquia.com".'),
      '#default_value' => $this->storage->getApiHost(),
    ];
    $form['possible_core_ids'] = [
      '#title' => $this->t('Possible Core IDs'),
      '#type' => 'textarea',
      '#default_value' => $config->get('possible_core_ids'),
      '#description' => $this->t('Specify one or more existing Solr cores that should be made available to the local environment.<br />Obtain these from the "Search" sub-section found under each environment in the Acquia Cloud UI.<br />To create a new index see <a href="@acquia-docs">Managing search indexes</a><br /><em>Enter one core ID per line in this field</em>, for example: <br />ABCD-123456.05dev.abcdef123<br />ABCD-123456.05test.abcdef123<br />ABCD-123456.05live.abcdef123<br />', [
        '@acquia-docs' => 'https://docs.acquia.com/acquia-search/managing-indexes/',
      ]),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Clear Acquia Search Solr indexes cache.
    if (!empty(Storage::getIdentifier())) {
      $cid = 'acquia_search.indexes.' . Storage::getIdentifier();
      $this->cache->delete($cid);
    }
    $this->storage->setApiHost($values['api_host']);
    if (!empty($values['api_key'])) {
      $this->storage->setApiKey($values['api_key']);
    }

    $this->storage->setIdentifier($values['identifier']);
    $this->storage->setUuid($values['uuid']);

    // If one or more core IDs were provided, save them to config.
    if (!empty($values['possible_core_ids'])) {
      $config = $this->config('pn_acquia_search.settings');
      $config->set('possible_core_ids', $values['possible_core_ids']);
      $config->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'pn_acquia_search.settings',
    ];
  }

}
