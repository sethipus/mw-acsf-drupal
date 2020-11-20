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

  private const API_PATHS = [
    'get_token' => '/session',
    'refresh_token' => '/session/refresh',
    'search' => '/search/001',
    'asset_by_id' => '/asset',
    'assets_by_ids' => '/search/002',
    'get_brands' => '/lookup/brand',
    'get_markets' => '/lookup/market',
    'sent_inventory_report' => '/inventory/acquia',
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
   * The subpath.
   *
   * @var string
   */
  private $subPath;

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
   * @param string $subpath
   *   The subpath.
   */
  public function __construct(
    string $username,
    string $password,
    string $api_key,
    string $base_path,
    int $port,
    string $subpath
  ) {
    $this->username = $username;
    $this->password = $password;
    $this->apiKey = $api_key;
    $this->basePath = $base_path;
    $this->port = $port;
    $this->subPath = $subpath;
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
    if (!isset(self::API_PATHS[$endpoint_type])) {
      throw new LighthouseException('Invalid endpoint type: ' . $endpoint_type);
    }

    $endpoint_path = self::API_PATHS[$endpoint_type];
    return $this->getBasePath() . ':' . $this->getPort() . $this->getSubpath() . $endpoint_path;
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

  /**
   * Returns the subpath.
   *
   * @return string
   *   The subpath.
   */
  private function getSubpath(): string {
    return $this->subPath;
  }

}
