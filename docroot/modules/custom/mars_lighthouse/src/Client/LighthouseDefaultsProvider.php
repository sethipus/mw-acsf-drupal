<?php

namespace Drupal\mars_lighthouse\Client;

use Drupal\Core\Site\Settings;

/**
 * Class that gets default values from Drupal settings.
 */
class LighthouseDefaultsProvider {

  /**
   * Settings object.
   *
   * @var \Drupal\Core\Site\Settings
   */
  private $settings;

  /**
   * Api version 1 key.
   */
  const API_KEY_VERSION_1 = 'v1';

  /**
   * Api version 2 key.
   */
  const API_KEY_VERSION_2 = 'v2';

  /**
   * LighthouseDefaultsProvider constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   The settings object.
   */
  public function __construct(Settings $settings) {
    $this->settings = $settings;
  }

  /**
   * Returns the default username.
   *
   * @return string|null
   *   The default username.
   */
  public function getDefaultUsername(): ?string {
    return $this->getDefaultValue('client_id');
  }

  /**
   * Returns the default password.
   *
   * @return string|null
   *   The default password.
   */
  public function getDefaultPassword(): ?string {
    return $this->getDefaultValue('client_secret');
  }

  /**
   * Returns the default API key.
   *
   * @return string|null
   *   The default API key.
   */
  public function getDefaultApiKey(): ?string {
    return $this->getDefaultValue('api_key');
  }

  /**
   * Returns the default base path.
   *
   * @return string|null
   *   The default base path.
   */
  public function getDefaultBasePath(): ?string {
    return $this->getDefaultValue('base_path');
  }

  /**
   * Returns the default port.
   *
   * @return int|null
   *   The default port.
   */
  public function getDefaultPort(): ?int {
    return $this->getDefaultValue('port');
  }

  /**
   * Returns the default api version.
   *
   * @return string|null
   *   The default api version.
   */
  public function getDefaultApiVersion(): ?string {
    return $this->settings->get('mars_lighthouse_defaults')['api_version'] ?? static::API_KEY_VERSION_1;
  }

  /**
   * Returns the default value under the given key.
   *
   * @param string $settings_key
   *   The key of the default value.
   *
   * @return mixed|null
   *   The default value under the given key.
   */
  private function getDefaultValue(string $settings_key) {
    return $this->settings->get('mars_lighthouse_defaults')[$settings_key] ?? NULL;
  }

}
