<?php

namespace Drupal\mars_lighthouse\Client;

use Drupal\mars_lighthouse\LighthouseException;

/**
 * Holds configuration values for lighthouse api calls.
 */
class LighthouseConfiguration {

  public const ENDPOINT_GET_TOKEN = 'get_token';

  public const ENDPOINT_REFRESH_TOKEN = 'refresh_token';

  public const ENDPOINT_SEARCH = 'search';

  public const ENDPOINT_ASSET_BY_ID = 'asset_by_id';

  public const ENDPOINT_ASSETS_BY_IDS = 'assets_by_ids';

  public const ENDPOINT_GET_BRANDS = 'get_brands';

  public const ENDPOINT_GET_MARKETS = 'get_markets';

  public const ENDPOINT_SENT_INVENTORY_REPORT = 'sent_inventory_report';

  /**
   * Api version 1 endpoints.
   */
  private const API_PATHS_V1 = [
    'get_token' => '/lh-integration/api/v1/session',
    'refresh_token' => '/lh-integration/api/v1/session/refresh',
    'search' => '/lh-integration/api/v1/search/001',
    'asset_by_id' => '/lh-integration/api/v1/asset',
    'assets_by_ids' => '/lh-integration/api/v1/search/002',
    'get_brands' => '/lh-integration/api/v1/lookup/brand',
    'get_markets' => '/lh-integration/api/v1/lookup/market',
    'sent_inventory_report' => '/lh-integration/api/v1/inventory/acquia',
  ];

  /**
   * Api version 2 endpoints.
   */
  private const API_PATHS_V2 = [
    'get_token' => '/lh-integration/api/v1/session',
    'refresh_token' => '/lh-integration/api/v1/session/refresh',
    'search' => '/lh-integration/api/v1/search/003',
    'asset_by_id' => '/lh-integration/api/v2/asset',
    'assets_by_ids' => '/lh-integration/api/v1/search/004',
    'get_brands' => '/lh-integration/api/v1/lookup/brand',
    'get_markets' => '/lh-integration/api/v1/lookup/market',
    'sent_inventory_report' => '/lh-integration/api/v1/inventory/acquia',
  ];

  /**
   * The username.
   *
   * @var string
   */
  private $username;

  /**
   * The password.
   *
   * @var string
   */
  private $password;

  /**
   * The API key.
   *
   * @var string
   */
  private $apiKey;

  /**
   * The base path.
   *
   * @var string
   */
  private $basePath;

  /**
   * The port number.
   *
   * @var int
   */
  private $port;

  /**
   * The api version.
   *
   * @var string
   */
  private $apiVersion;

  /**
   * LighthouseConfiguration constructor.
   *
   * @param string $username
   *   The username.
   * @param string $password
   *   The password.
   * @param string $api_key
   *   The API key.
   * @param string $base_path
   *   The base path.
   * @param int $port
   *   The port.
   * @param string $api_version
   *   The version.
   */
  public function __construct(
    string $username,
    string $password,
    string $api_key,
    string $base_path,
    int $port,
    string $api_version
  ) {
    $this->username = $username;
    $this->password = $password;
    $this->apiKey = $api_key;
    $this->basePath = $base_path;
    $this->port = $port;
    $this->apiVersion = $api_version;
  }

  /**
   * Returns the username.
   *
   * @return string
   *   The username.
   */
  public function getUsername(): string {
    return $this->username;
  }

  /**
   * Returns the password.
   *
   * @return string
   *   The password.
   */
  public function getPassword(): string {
    return $this->password;
  }

  /**
   * Returns API key.
   *
   * @return string
   *   The API key.
   */
  public function getApiKey(): string {
    return $this->apiKey;
  }

  /**
   * Build an endpoint full path.
   *
   * @param string $endpoint_type
   *   Endpoint name.
   *
   * @return string
   *   Endpoint full path.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  public function getEndpointFullPath(string $endpoint_type): string {
    if ($this->apiVersion == LighthouseDefaultsProvider::API_KEY_VERSION_1) {
      if (!isset(self::API_PATHS_V1[$endpoint_type])) {
        throw new LighthouseException('Invalid endpoint type: ' . $endpoint_type);
      }

      $endpoint_path = self::API_PATHS_V1[$endpoint_type];
      return $this->getBasePath() . ':' . $this->getPort() . $endpoint_path;
    }
    elseif ($this->apiVersion == LighthouseDefaultsProvider::API_KEY_VERSION_2) {
      if (!isset(self::API_PATHS_V2[$endpoint_type])) {
        throw new LighthouseException('Invalid endpoint type: ' . $endpoint_type);
      }

      $endpoint_path = self::API_PATHS_V2[$endpoint_type];
      return $this->getBasePath() . ':' . $this->getPort() . $endpoint_path;
    }
    else {
      throw new LighthouseException('Invalid endpoint type: ' . $endpoint_type);
    }

  }

  /**
   * Returns the base path.
   *
   * @return string
   *   The base path.
   */
  private function getBasePath(): string {
    return $this->basePath;
  }

  /**
   * Returns the port number.
   *
   * @return int
   *   The port number.
   */
  private function getPort(): int {
    return $this->port;
  }

}
