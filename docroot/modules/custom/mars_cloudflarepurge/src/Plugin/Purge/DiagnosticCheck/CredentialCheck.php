<?php

namespace Drupal\mars_cloudflarepurge\Plugin\Purge\DiagnosticCheck;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\purge\Plugin\Purge\DiagnosticCheck\DiagnosticCheckBase;
use Drupal\purge\Plugin\Purge\DiagnosticCheck\DiagnosticCheckInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Checks if valid zone credentials have been entered for CloudFlare.
 *
 * @PurgeDiagnosticCheck(
 *   id = "cloudflare_creds",
 *   title = @Translation("CloudFlare - Zone Credentials"),
 *   description = @Translation("Checks to see if the supplied zone credentials for CloudFlare are valid."),
 *   dependent_queue_plugins = {},
 *   dependent_purger_plugins = {"customcloudflare"}
 * )
 */
class CredentialCheck extends DiagnosticCheckBase implements DiagnosticCheckInterface {
  /**
   * The settings configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs a CredentialCheck object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $config_factory->get('cloudflarepurge.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function run() {
    // Checking Zone credentials already exists.
    if (!empty($this->config->get('zone_id')) && !empty($this->config->get('authorization'))) {
      $zone_credentials_found = TRUE;
    }
    else {
      $zone_credentials_found = $this->config->get('zone_credentials_found');
    }

    if (!$zone_credentials_found) {
      $this->recommendation = $this->t("Zone credentials are not found. Go to cloudflare purge config (/admin/config/cloudflare-cache-clear) to add zone credentials.",);
      return self::SEVERITY_ERROR;
    }

    $this->recommendation = $this->t('Zone credentials detected.');
    return self::SEVERITY_OK;
  }

}
