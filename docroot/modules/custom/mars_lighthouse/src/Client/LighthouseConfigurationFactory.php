<?php

namespace Drupal\mars_lighthouse\Client;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mars_lighthouse\LighthouseException;

/**
 * Class for creating lighthouse config based on config and default values.
 */
class LighthouseConfigurationFactory {

  /**
   * The defaults provider service.
   *
   * @var \Drupal\mars_lighthouse\Client\LighthouseDefaultsProvider
   */
  private $defaultsProvider;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * LighthouseConfigurationFactory constructor.
   *
   * @param \Drupal\mars_lighthouse\Client\LighthouseDefaultsProvider $defaults_provider
   *   The defaults provider service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(
    LighthouseDefaultsProvider $defaults_provider,
    ConfigFactoryInterface $config_factory
  ) {
    $this->defaultsProvider = $defaults_provider;
    $this->configFactory = $config_factory;
  }

  /**
   * Creates a configuration object based on config and defaults.
   *
   * @return \Drupal\mars_lighthouse\Client\LighthouseConfiguration
   *   The configuration object.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  public function createConfiguration(): LighthouseConfiguration {
    $config = $this->configFactory->get('mars_lighthouse.settings');

    $username = $config->get('client_id') ?? $this->defaultsProvider->getDefaultUsername();
    if ($username === NULL) {
      throw new LighthouseException('Missing default username configuration for lighthouse.');
    }

    $password = $config->get('client_secret') ?? $this->defaultsProvider->getDefaultPassword();
    if ($password === NULL) {
      throw new LighthouseException('Missing default password configuration for lighthouse.');
    }

    $api_key = $config->get('api_key') ?? $this->defaultsProvider->getDefaultApiKey();
    if ($api_key === NULL) {
      throw new LighthouseException('Missing default api key configuration for lighthouse.');
    }

    $base_path = $config->get('base_path') ?? $this->defaultsProvider->getDefaultBasePath();
    if ($base_path === NULL) {
      throw new LighthouseException('Missing default base path configuration for lighthouse.');
    }

    $port = $config->get('port') ?? $this->defaultsProvider->getDefaultPort();
    if ($port === NULL) {
      throw new LighthouseException('Missing default port configuration for lighthouse.');
    }

    $api_version = $config->get('api_version') ?? $this->defaultsProvider->getDefaultApiVersion();
    if ($api_version === NULL) {
      throw new LighthouseException('Missing default api version configuration for lighthouse.');
    }

    return new LighthouseConfiguration(
      $username,
      $password,
      $api_key,
      $base_path,
      $port,
      $api_version
    );
  }

}
