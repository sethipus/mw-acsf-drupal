<?php

namespace Drupal\mars_lighthouse\Client;

use Drupal\Component\Serialization\Json;
use Drupal\mars_lighthouse\LighthouseClientInterface;
use Drupal\Core\Config\ConfigFactory;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Class LighthouseClient.
 *
 * @package Drupal\mars_lighthouse\Client
 */
class LighthouseClient implements LighthouseClientInterface {

  /**
   * An http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * LighthouseClient constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   Http client.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Config factory.
   */
  public function __construct(ClientInterface $http_client, ConfigFactory $config_factory) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    // TODO: will be updated to editable config.
    // Return test credentials.
    return [
      'client_id' => 'DrupalTest',
      'client_secret' => 'DrupalTest@1234',
      'base_path' => 'https://lighthouse-api-dev.mars.com',
      'subpath' => '/lh-integration/api/v1',
      'port' => '443',
      'api_key' => 'sample-apikey',
      'header_name' => 'x-lighthouse-authen',
    ];
  }

  /**
   * Returns API paths.
   *
   * @return array
   *   Array path name => path.
   */
  public function getApiPaths() {
    return [
      'get_token' => '/session',
      'search' => '/search',
      'asset_by_id' => '/asset',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getToken() {
    $configuration = $this->getConfiguration();
    $endpoint_full_path = $this->getEndpointFullPath('get_token');

    try {
      /**@var \Psr\Http\Message\ResponseInterface $response */
      $response = $this->httpClient->post(
        $endpoint_full_path,
        [
          'json' => [
            'username' => 'DrupalTest',
            'password' => 'DrupalTest@1234',
            "apikey" => $configuration['api_key'],
            "requestTime" => date('Y-m-d-H-i-s Z'),
          ],
        ]
      );
    }
    catch (RequestException $exception) {
      \Drupal::logger('mars_lighthouse')
        ->error('Failed to receive access token "%error"', ['%error' => $exception->getMessage()]);
      return [];
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
  public function search($text = '', $filters = [], $sort_by = [], $offset = 0, $limit = 10, $params = []) {
    if (!isset($params['mars_lighthouse.headers']) && !isset($params['mars_lighthouse.access_token'])) {
      return [];
    }

    $body = [
      'requestTime' => date('Y-m-d-H-i-s Z'),
      'token' => $params['mars_lighthouse.access_token'],
      'text' => $text,
      'orderBy' => '',
      'brand' => [],
      'subBrand' => [],
      'subtype' => [],
      'category' => [],
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
      \Drupal::logger('mars_lighthouse')
        ->error('Failed to run search "%error"', ['%error' => $exception->getMessage()]);
      return [];
    }

    $content = $response->getBody()->getContents();
    $content = Json::decode($content);

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
      \Drupal::logger('mars_lighthouse')
        ->error('Failed to run search "%error"', ['%error' => $exception->getMessage()]);
      return [];
    }

    $content = $response->getBody()->getContents();
    $content = Json::decode($content);

    return $content['assetList'][0] ?? [];
  }

  /**
   * Build an endpoint full path.
   *
   * @param string $endpoint_path
   *   Endpoint name.
   *
   * @return string
   *   Endpoint full path.
   */
  protected function getEndpointFullPath(string $endpoint_path): string {
    $configuration = $this->getConfiguration();
    $endpoint_path = $this->getApiPaths()[$endpoint_path] ?? '';
    return $configuration['base_path'] . ':' . $configuration['port'] . $configuration['subpath'] . $endpoint_path;
  }

}
