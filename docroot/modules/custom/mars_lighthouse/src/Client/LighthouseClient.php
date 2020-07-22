<?php

namespace Drupal\mars_lighthouse\Client;

use Drupal\Component\Serialization\Json;
use Drupal\mars_lighthouse\LighthouseClientInterface;
use Drupal\site_settings\SiteSettingsLoader;
use Drupal\mars_lighthouse\LighthouseException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\mars_lighthouse\TokenIsExpiredException;
use Drupal\mars_lighthouse\LighthouseAccessException;

/**
 * Class LighthouseClient.
 *
 * @package Drupal\mars_lighthouse\Client
 */
class LighthouseClient implements LighthouseClientInterface {

  /**
   * Date format required by API.
   */
  const DATE_FORMAT = 'Y-m-d-H-i-s Z';

  /**
   * Error code when an access token is expired.
   */
  const TOKEN_IS_EXPIRED_ERROR_CODE = 400;

  /**
   * Error code when an access token is not found at Lighthouse.
   */
  const ACCESS_ERROR_CODE = 403;

  /**
   * An http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Logger for this channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Lighthouse API overwritten settings.
   *
   * @var array
   */
  protected $settings;

  /**
   * LighthouseClient constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   Http client.
   * @param \Drupal\site_settings\SiteSettingsLoader $site_settings_loader
   *   Site settings loader.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   */
  public function __construct(ClientInterface $http_client, SiteSettingsLoader $site_settings_loader, LoggerChannelFactoryInterface $logger_factory) {
    $this->httpClient = $http_client;
    $this->settings = $site_settings_loader->loadByFieldset('lighthouse_api');
    $this->logger = $logger_factory->get('mars_lighthouse');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration(): array {
    // TODO: check secrets php for global settings.
    // Check for overwritten settings.
    if (array_key_exists('lighthouse_api', $this->settings) && is_array($this->settings['lighthouse_api'])) {
      $settings = $this->settings['lighthouse_api'];
      foreach ($settings as $key => $value) {
        $settings[str_replace('field_', '', $key)] = $value;
        unset($settings[$key]);
      }
      return $settings;
    }

    throw new LighthouseException('Please, check that Lighthouse configuration is set.');
  }

  /**
   * Returns API paths.
   *
   * @return array
   *   Array path name => path.
   */
  public function getApiPaths(): array {
    return [
      'get_token' => '/session',
      'refresh_token' => '/session/refresh',
      'search' => '/search/001',
      'asset_by_id' => '/asset',
      'get_brands' => '/lookup/brand',
      'get_markets' => '/lookup/market',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getToken(): array {
    $configuration = $this->getConfiguration();
    $endpoint_full_path = $this->getEndpointFullPath('get_token');

    try {
      /**@var \Psr\Http\Message\ResponseInterface $response */
      $response = $this->httpClient->post(
        $endpoint_full_path,
        [
          'json' => [
            'username' => $configuration['client_id'],
            'password' => $configuration['client_secret'],
            "apikey" => $configuration['api_key'],
            "requestTime" => date(self::DATE_FORMAT),
          ],
        ]
      );
    }
    catch (RequestException $exception) {
      $this->logger->error('Failed to receive access token "%error"', ['%error' => $exception->getMessage()]);
      throw new LighthouseException('Something went wrong while connecting to Lighthouse. Please, check logs or contact site administrator.');
    }

    $header_value = $response->getHeaders()[$configuration['header_name']];
    $header_value = is_array($header_value) ? array_shift($header_value) : $header_value;

    $response = $response->getBody()->getContents();
    $response = Json::decode($response);

    return [
      'mars_lighthouse.headers' => [$configuration['header_name'] => $header_value],
      'response' => $response,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function refreshToken($params): array {
    $configuration = $this->getConfiguration();
    $endpoint_full_path = $this->getEndpointFullPath('refresh_token');

    try {
      /**@var \Psr\Http\Message\ResponseInterface $response */
      $response = $this->httpClient->post(
        $endpoint_full_path,
        [
          'json' => [
            'refreshToken' => $params['mars_lighthouse.refresh_token'] ?? '',
            'token' => $params['mars_lighthouse.access_token'] ?? '',
            'requestTime' => date(self::DATE_FORMAT),
          ],
          'headers' => $params['mars_lighthouse.headers'],
        ]
      );
    }
    catch (RequestException $exception) {
      $this->logger->error('Failed to refresh access token "%error"', ['%error' => $exception->getMessage()]);
      throw new LighthouseException('Something went wrong while connecting to Lighthouse. Please, check logs or contact site administrator.');
    }

    $header_value = $response->getHeaders()[$configuration['header_name']];
    $header_value = is_array($header_value) ? array_shift($header_value) : $header_value;

    $response = $response->getBody()->getContents();
    $response = Json::decode($response);

    return [
      'mars_lighthouse.headers' => [$configuration['header_name'] => $header_value],
      'response' => $response,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function search(&$total_found, $text = '', $filters = [], $sort_by = [], $offset = 0, $limit = 10, $params = []): array {
    if (!isset($params['mars_lighthouse.headers']) && !isset($params['mars_lighthouse.access_token'])) {
      return [];
    }

    $body = [
      'requestTime' => date(self::DATE_FORMAT),
      'token' => $params['mars_lighthouse.access_token'],
      'text' => $text,
      'orderBy' => '',
      'brand' => $filters['brand'] ?? '',
      'market' => $filters['market'] ?? '',
      // 'subBrand' => [],
      // 'subtype' => [],
      // 'category' => [],
      'contentType' => 'image',
      'pagingConfig' => [
        'startRow' => $offset,
        'perPage' => $limit,
      ],
    ];

    $endpoint_full_path = $this->getEndpointFullPath('search');

    try {
      /**@var \Psr\Http\Message\ResponseInterface $response */
      $response = $this->httpClient->post(
        $endpoint_full_path,
        [
          'json' => $body,
          'headers' => $params['mars_lighthouse.headers'],
        ]
      );
    }
    catch (RequestException $exception) {
      if ($exception->getCode() == self::TOKEN_IS_EXPIRED_ERROR_CODE) {
        throw new TokenIsExpiredException('Access token is expired.');
      }
      elseif ($exception->getCode() == self::ACCESS_ERROR_CODE) {
        throw new LighthouseAccessException('Access token is invalid. A new one should be forced requested.');
      }
      else {
        $this->logger->error('Failed to run search "%error"', ['%error' => $exception->getMessage()]);
        throw new LighthouseException('Something went wrong while connecting to Lighthouse. Please, check logs or contact site administrator.');
      }
    }

    $content = $response->getBody()->getContents();
    $content = Json::decode($content);

    $total_found = $content['paging']['totalFound'] ?? 0;
    return $content['assetList'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getAssetById(string $id, array $params = []): array {
    if (!isset($params['mars_lighthouse.headers']) && !isset($params['mars_lighthouse.access_token'])) {
      return [];
    }

    $endpoint_full_path = $this->getEndpointFullPath('asset_by_id') . '/' . $id;

    $content = $this->get($endpoint_full_path, $params);

    return $content['assetList'][0] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getBrands(array $params = []): array {
    if (!isset($params['mars_lighthouse.headers']) && !isset($params['mars_lighthouse.access_token'])) {
      return [];
    }
    $endpoint_full_path = $this->getEndpointFullPath('get_brands');

    $content = $this->get($endpoint_full_path, $params);

    return $content['valueList'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getMarkets(array $params = []): array {
    if (!isset($params['mars_lighthouse.headers']) && !isset($params['mars_lighthouse.access_token'])) {
      return [];
    }
    $endpoint_full_path = $this->getEndpointFullPath('get_markets');

    $content = $this->get($endpoint_full_path, $params);

    return $content['valueList'] ?? [];
  }

  /**
   * Build an endpoint full path.
   *
   * @param string $endpoint_path
   *   Endpoint name.
   *
   * @return string
   *   Endpoint full path.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  protected function getEndpointFullPath(string $endpoint_path): string {
    $configuration = $this->getConfiguration();
    $endpoint_path = $this->getApiPaths()[$endpoint_path] ?? '';
    return $configuration['base_path'] . ':' . $configuration['port'] . $configuration['subpath'] . $endpoint_path;
  }

  /**
   * Performs GET request to Lighthouse API.
   *
   * @param string $endpoint_full_path
   *   Endpoint to trigger.
   * @param array $params
   *   Headers and access token.
   *
   * @return array
   *   Response data.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseAccessException
   * @throws \Drupal\mars_lighthouse\LighthouseException
   * @throws \Drupal\mars_lighthouse\TokenIsExpiredException
   */
  protected function get(string $endpoint_full_path, array $params): array {
    $params['mars_lighthouse.headers']['Content-Type'] = 'application/json';
    try {
      /**@var \Psr\Http\Message\ResponseInterface $response */
      $response = $this->httpClient->get(
        $endpoint_full_path,
        [
          'headers' => $params['mars_lighthouse.headers'],
          'query' => [
            'token' => $params['mars_lighthouse.access_token'],
          ],
        ]
      );
    }
    catch (RequestException $exception) {
      if ($exception->getCode() == self::TOKEN_IS_EXPIRED_ERROR_CODE) {
        throw new TokenIsExpiredException('Access token is expired.');
      }
      elseif ($exception->getCode() == self::ACCESS_ERROR_CODE) {
        throw new LighthouseAccessException('Access token is invalid. A new one should be forced requested.');
      }
      else {
        $this->logger->error('Failed to run asset_by_id "%error"', ['%error' => $exception->getMessage()]);
        throw new LighthouseException('Something went wrong while connecting to Lighthouse. Please, check logs or contact site administrator.');
      }
    }

    $content = $response->getBody()->getContents();
    $content = Json::decode($content);
    return $content;
  }

}
