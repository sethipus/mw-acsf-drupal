<?php

namespace Drupal\mars_lighthouse\Client;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\mars_lighthouse\LighthouseClientInterface;
use Drupal\mars_lighthouse\LighthouseException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Class LighthouseClient.
 *
 * @package Drupal\mars_lighthouse\Client
 */
class LighthouseClient extends LighthouseBaseApiAbstract implements LighthouseClientInterface {

  /**
   * Lighthouse authentication token provider.
   *
   * @var \Drupal\mars_lighthouse\Client\LighthouseAuthTokenProvider
   */
  protected $lighthouseAuthTokenProvider;

  /**
   * LighthouseClient constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   Http client.
   * @param \Drupal\mars_lighthouse\Client\LighthouseConfiguration $config
   *   Client configuration.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   * @param \Drupal\mars_lighthouse\Client\LighthouseAuthTokenProvider $lighthouse_auth_token_provider
   *   Lighthouse auth token provider.
   */
  public function __construct(
    ClientInterface $http_client,
    LighthouseConfiguration $config,
    LoggerChannelFactoryInterface $logger_factory,
    LighthouseAuthTokenProvider $lighthouse_auth_token_provider
  ) {
    parent::__construct($http_client, $config, $logger_factory);
    $this->lighthouseAuthTokenProvider = $lighthouse_auth_token_provider;
  }

  /**
   * {@inheritdoc}
   */
  public function search(&$total_found, $text = '', $filters = [], $sort_by = [], $offset = 0, $limit = 10, $media_type = 'image'): array {
    $params = $this->lighthouseAuthTokenProvider->getAccessToken();
    if (!isset($params['mars_lighthouse.headers']) && !isset($params['mars_lighthouse.access_token'])) {
      return [];
    }

    $body = [
      'requestTime' => date(static::DATE_FORMAT),
      'token' => $params['mars_lighthouse.access_token'],
      'text' => $text,
      'orderBy' => '',
      'brand' => $filters['brand'] ?? '',
      'market' => $filters['market'] ?? '',
      'isTransparent' => $filters['transparent'] ?? '',
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
      $this->logger->error('Failed to run search "%error"', ['%error' => $exception->getMessage()]);
      throw new LighthouseException('Something went wrong while connecting to Lighthouse. Please, check logs or contact site administrator.');
    }

    $content = $response->getBody()->getContents();
    $content = Json::decode($content);

    $total_found = $content['paging']['totalFound'] ?? 0;
    return $content['assetList'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getAssetById(string $id): array {
    $params = $this->lighthouseAuthTokenProvider->getAccessToken();
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
  public function getLatestAssetById(string $id): array {
    $params = $this->lighthouseAuthTokenProvider->getAccessToken();
    if (!isset($params['mars_lighthouse.headers']) && !isset($params['mars_lighthouse.access_token'])) {
      return [];
    }

    $endpoint_full_path = $this->config->getEndpointFullPath(LighthouseConfiguration::ENDPOINT_ASSET_BY_ID) . '/' . $id . '/latest';

    $content = $this->get($endpoint_full_path, $params);

    return $content['assetList'][0] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getAssetsByIds(array $request_data, string $date): array {
    $params = $this->lighthouseAuthTokenProvider->getAccessToken();
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
      $this->logger->error('Failed to run getAssetsByIds "%error"', ['%error' => $exception->getMessage()]);
      throw new LighthouseException('Something went wrong while connecting to Lighthouse. Please, check logs or contact site administrator.');
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
  public function sentInventoryReport(array $asset_list): array {
    $params = $this->lighthouseAuthTokenProvider->getAccessToken();
    if (!isset($params['mars_lighthouse.headers']) && !isset($params['mars_lighthouse.access_token'])) {
      return [];
    }

    $body = [
      'token' => $params['mars_lighthouse.access_token'],
      'requestTime' => date(static::DATE_FORMAT),
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
      $this->logger->error('Failed to run sentInventoryReport "%error"', ['%error' => $exception->getMessage()]);
      throw new LighthouseException('Something went wrong while connecting to Lighthouse. Please, check logs or contact site administrator.');
    }

    $content = $response->getBody()->getContents();
    $result = Json::decode($content);

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getBrands(): array {
    $params = $this->lighthouseAuthTokenProvider->getAccessToken();
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
  public function getMarkets(): array {
    $params = $this->lighthouseAuthTokenProvider->getAccessToken();
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
      $this->logger->error('Failed to run getAssetsById "%error"', ['%error' => $exception->getMessage()]);
      throw new LighthouseException('Something went wrong while connecting to Lighthouse. Please, check logs or contact site administrator.');
    }

    $content = $response->getBody()->getContents();
    $content = Json::decode($content);
    return $content;
  }

}
