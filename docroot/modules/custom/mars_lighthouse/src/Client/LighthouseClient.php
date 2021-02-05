<?php

namespace Drupal\mars_lighthouse\Client;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigInstallerInterface;
use Drupal\Core\Installer\InstallerKernel;
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
   * Headers parameters.
   *
   * @var array
   */
  private $headerParams = [];

  /**
   * The config installer.
   *
   * @var \Drupal\Core\Config\ConfigInstallerInterface
   */
  private $configInstaller;

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
   * @param \Drupal\Core\Config\ConfigInstallerInterface $config_installer
   *   The config installer.
   */
  public function __construct(
    ClientInterface $http_client,
    LighthouseConfiguration $config,
    LoggerChannelFactoryInterface $logger_factory,
    LighthouseAuthTokenProvider $lighthouse_auth_token_provider,
    ConfigInstallerInterface $config_installer
  ) {
    parent::__construct($http_client, $config, $logger_factory);
    $this->lighthouseAuthTokenProvider = $lighthouse_auth_token_provider;
    $this->configInstaller = $config_installer;
    if (!$this->configInstaller->isSyncing() && !InstallerKernel::installationAttempted()) {
      $this->headerParams = $this->lighthouseAuthTokenProvider->getAccessToken();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function search(&$total_found, $text = '', $filters = [], $sort_by = [], $offset = 0, $limit = 10, $media_type = 'image'): array {
    if (!isset($this->headerParams['mars_lighthouse.headers']) && !isset($this->headerParams['mars_lighthouse.access_token'])) {
      return [];
    }

    $body = [
      'requestTime' => date(static::DATE_FORMAT),
      'token' => $this->headerParams['mars_lighthouse.access_token'],
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
          'headers' => $this->headerParams['mars_lighthouse.headers'],
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
    if (!isset($this->headerParams['mars_lighthouse.headers']) && !isset($this->headerParams['mars_lighthouse.access_token'])) {
      return [];
    }

    $endpoint_full_path = $this->config->getEndpointFullPath(LighthouseConfiguration::ENDPOINT_ASSET_BY_ID) . '/' . $id;

    $content = $this->get($endpoint_full_path);

    return $content['assetList'][0] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getAssetsByIds(array $request_data, string $date): array {
    if (!isset($this->headerParams['mars_lighthouse.headers']) && !isset($this->headerParams['mars_lighthouse.access_token'])) {
      return [];
    }

    $body = [
      'token' => $this->headerParams['mars_lighthouse.access_token'],
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
          'headers' => $this->headerParams['mars_lighthouse.headers'],
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
    if (!isset($this->headerParams['mars_lighthouse.headers']) && !isset($this->headerParams['mars_lighthouse.access_token'])) {
      return [];
    }

    $body = [
      'token' => $this->headerParams['mars_lighthouse.access_token'],
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
          'headers' => $this->headerParams['mars_lighthouse.headers'],
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
    if (!isset($this->headerParams['mars_lighthouse.headers']) && !isset($this->headerParams['mars_lighthouse.access_token'])) {
      return [];
    }
    $endpoint_full_path = $this->config->getEndpointFullPath(LighthouseConfiguration::ENDPOINT_GET_BRANDS);

    $content = $this->get($endpoint_full_path, $this->headerParams);

    return $content['valueList'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getMarkets(): array {
    if (!isset($this->headerParams['mars_lighthouse.headers']) && !isset($this->headerParams['mars_lighthouse.access_token'])) {
      return [];
    }
    $endpoint_full_path = $this->config->getEndpointFullPath(LighthouseConfiguration::ENDPOINT_GET_MARKETS);

    $content = $this->get($endpoint_full_path, $this->headerParams);

    return $content['valueList'] ?? [];
  }

  /**
   * Performs GET request to Lighthouse API.
   *
   * @param string $endpoint_full_path
   *   Endpoint to trigger.
   *
   * @return array
   *   Response data.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseAccessException
   * @throws \Drupal\mars_lighthouse\LighthouseException
   * @throws \Drupal\mars_lighthouse\TokenIsExpiredException
   */
  protected function get(string $endpoint_full_path): array {
    $this->headerParams['mars_lighthouse.headers']['Content-Type'] = 'application/json';
    try {
      /**@var \Psr\Http\Message\ResponseInterface $response */
      $response = $this->httpClient->get(
        $endpoint_full_path,
        [
          'headers' => $this->headerParams['mars_lighthouse.headers'],
          'query' => [
            'token' => $this->headerParams['mars_lighthouse.access_token'],
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
