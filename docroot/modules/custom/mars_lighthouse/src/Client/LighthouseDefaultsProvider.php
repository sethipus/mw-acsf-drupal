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
   * Returns the default subpath.
   *
   * @return string|null
   *   The default subpath.
   */
  public function getDefaultSubpath(): ?string {
    return $this->getDefaultValue('subpath');
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
