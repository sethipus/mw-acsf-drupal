<?php

namespace Drupal\mars_lighthouse\Client;

use Drupal\Component\Serialization\Json;
use Drupal\mars_lighthouse\LighthouseClientInterface;
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
   * Lighthouse API configuration.
   *
   * @var \Drupal\mars_lighthouse\Client\LighthouseConfiguration
   */
  protected $config;

  /**
   * LighthouseClient constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   Http client.
   * @param \Drupal\mars_lighthouse\Client\LighthouseConfiguration $config
   *   Client configuration.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   */
  public function __construct(
    ClientInterface $http_client,
    LighthouseConfiguration $config,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->httpClient = $http_client;
    $this->config = $config;
    $this->logger = $logger_factory->get('mars_lighthouse');
  }

  /**
   * {@inheritdoc}
   */
  public function getToken(): array {
    $endpoint_full_path = $this->config->getEndpointFullPath(LighthouseConfiguration::ENDPOINT_GET_TOKEN);

    try {
      /**@var \Psr\Http\Message\ResponseInterface $response */
      $response = $this->httpClient->post(
        $endpoint_full_path,
        [
          'json' => [
            'username' => $this->config->getUsername(),
            'password' => $this->config->getPassword(),
            "apikey" => $this->config->getApiKey(),
            "requestTime" => date(self::DATE_FORMAT),
          ],
        ]
      );
    }
    catch (RequestException $exception) {
      $this->logger->error('Failed to receive access token "%error"', ['%error' => $exception->getMessage()]);
      throw new LighthouseException('Something went wrong while connecting to Lighthouse. Please, check logs or contact site administrator.');
    }

    $header_value = $response->getHeaders()['x-lighthouse-authen'];
    $header_value = is_array($header_value) ? array_shift($header_value) : $header_value;

    $response = $response->getBody()->getContents();
    $response = Json::decode($response);

    return [
      'mars_lighthouse.headers' => ['x-lighthouse-authen' => $header_value],
      'response' => $response,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function refreshToken($params): array {
    $endpoint_full_path = $this->config->getEndpointFullPath(LighthouseConfiguration::ENDPOINT_REFRESH_TOKEN);

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

    $header_value = $response->getHeaders()['x-lighthouse-authen'];
    $header_value = is_array($header_value) ? array_shift($header_value) : $header_value;

    $response = $response->getBody()->getContents();
    $response = Json::decode($response);

    return [
      'mars_lighthouse.headers' => ['x-lighthouse-authen' => $header_value],
      'response' => $response,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function search(&$total_found, $text = '', $filters = [], $sort_by = [], $offset = 0, $limit = 10, $params = [], $media_type = 'image'): array {
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
      /* 'subBrand' => [],
         'subtype' => [],
         'category' => [], */
      'contentType' => $media_type,
      'pagingConfig' => [
        'startRow' => $offset,
        'perPage' => $limit,
      ],
    ];

    $endpoint_full_path = $this->config->getEndpointFullPath(LighthouseConfiguration::ENDPOINT_SEARCH);

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

    $endpoint_full_path = $this->config->getEndpointFullPath(LighthouseConfiguration::ENDPOINT_ASSET_BY_ID) . '/' . $id;

    $content = $this->get($endpoint_full_path, $params);

    return $content['assetList'][0] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getAssetsByIds(array $request_data, string $date, array $params = []): array {
    if (!isset($params['mars_lighthouse.headers']) && !isset($params['mars_lighthouse.access_token'])) {
      return [];
    }

    $body = [
      'token' => $params['mars_lighthouse.access_token'],
      'checkDate' => $date,
      'assets' => $request_data,
    ];

    $endpoint_full_path = $this->config->getEndpointFullPath(LighthouseConfiguration::ENDPOINT_ASSETS_BY_IDS);

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

    $asset_modified = $content['assetModified'] ?? [];
    $asset_with_new_version = $content['assetWithNewVersion'] ?? [];
    $result = array_merge($asset_modified, $asset_with_new_version);
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function sentInventoryReport(array $asset_list, array $params = []): array {
    if (!isset($params['mars_lighthouse.headers']) && !isset($params['mars_lighthouse.access_token'])) {
      return [];
    }

    $body = [
      'token' => $params['mars_lighthouse.access_token'],
      'requestTime' => date(self::DATE_FORMAT),
      'assetList' => $asset_list,
    ];

    $endpoint_full_path = $this->config->getEndpointFullPath(LighthouseConfiguration::ENDPOINT_SENT_INVENTORY_REPORT);

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
    $result = Json::decode($content);

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getBrands(array $params = []): array {
    if (!isset($params['mars_lighthouse.headers']) && !isset($params['mars_lighthouse.access_token'])) {
      return [];
    }
    $endpoint_full_path = $this->config->getEndpointFullPath(LighthouseConfiguration::ENDPOINT_GET_BRANDS);

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
    $endpoint_full_path = $this->config->getEndpointFullPath(LighthouseConfiguration::ENDPOINT_GET_MARKETS);

    $content = $this->get($endpoint_full_path, $params);

    return $content['valueList'] ?? [];
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
