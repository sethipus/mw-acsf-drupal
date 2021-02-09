<?php

namespace Drupal\mars_lighthouse\Client;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\mars_lighthouse\LighthouseAccessException;
use Drupal\mars_lighthouse\LighthouseException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Class LighthouseAuthTokenProvider.
 *
 * Provide token for auth.
 *
 * @package Drupal\mars_lighthouse\Service
 */
class LighthouseAuthTokenProvider extends LighthouseBaseApiAbstract {

  /**
   * Token keys.
   */
  const KEYS_FOR_TOKEN = [
    'mars_lighthouse.access_token',
    'mars_lighthouse.headers',
    'mars_lighthouse.refresh_token',
    'mars_lighthouse.refresh_time',
  ];

  const TTL_CORRECTION_TIME_SEC = 10;

  /**
   * State service for retrieving database info.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * LighthouseClient constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   Http client.
   * @param \Drupal\mars_lighthouse\Client\LighthouseConfiguration $config
   *   Client configuration.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state.
   */
  public function __construct(
    ClientInterface $http_client,
    LighthouseConfiguration $config,
    LoggerChannelFactoryInterface $logger_factory,
    StateInterface $state
  ) {
    parent::__construct($http_client, $config, $logger_factory);
    $this->state = $state;
  }

  /**
   * Get access tokens.
   *
   * @return array
   *   Access tokens.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseAccessException
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  public function getAccessToken() {
    $hash_of_lighthouse_credentials = md5(serialize($this->config));
    if (!$this->tokenExists() || $this->state->get('mars_lighthouse.hash_of_lighthouse_credentials') != $hash_of_lighthouse_credentials) {
      $params = $this->requestToken();
    }
    elseif ($this->tokenExpired()) {
      $params = $this->refreshToken();
    }
    else {
      $params = $this->state->getMultiple(static::KEYS_FOR_TOKEN);
    }
    return $params;
  }

  /**
   * Request access tokens.
   *
   * @return array
   *   Access tokens.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseAccessException
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  private function requestToken(): array {
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
            "requestTime" => date(static::DATE_FORMAT),
          ],
        ]
      );
    }
    catch (RequestException $exception) {
      if ($exception->getCode() == static::ACCESS_ERROR_CODE) {
        $this->logger->error('Invalid credentials, user details supplied are not valid "%error"', ['%error' => $exception->getMessage()]);
        throw new LighthouseAccessException('Invalid credentials, user details supplied are not valid');
      }
      else {
        $this->logger->error('Failed to receive access token "%error"', ['%error' => $exception->getMessage()]);
        throw new LighthouseException('Something went wrong while connecting to Lighthouse. Please, check logs or contact site administrator.');
      }
    }

    $header_value = $response->getHeaders()['x-lighthouse-authen'];
    $header_value = is_array($header_value) ? array_shift($header_value) : $header_value;

    $response = $response->getBody()->getContents();
    $response = Json::decode($response);

    $tokens = [
      'mars_lighthouse.headers' => ['x-lighthouse-authen' => $header_value],
      'response' => $response,
    ];

    $tokens['mars_lighthouse.access_token'] = $tokens['response']['lhisToken'];
    $tokens['mars_lighthouse.refresh_token'] = $tokens['response']['refreshToken'];
    $tokens['mars_lighthouse.refresh_time'] = $tokens['response']['refreshTime'];

    $tokens['mars_lighthouse.hash_of_lighthouse_credentials'] = md5(serialize($this->config));

    unset($tokens['response']);
    $this->state->setMultiple($tokens);
    return $tokens;
  }

  /**
   * Refresh access tokens.
   *
   * @return array
   *   Access tokens.
   *
   * @throws \Drupal\mars_lighthouse\LighthouseAccessException
   * @throws \Drupal\mars_lighthouse\LighthouseException
   */
  private function refreshToken(): array {
    $endpoint_full_path = $this->config->getEndpointFullPath(LighthouseConfiguration::ENDPOINT_REFRESH_TOKEN);

    try {
      /**@var \Psr\Http\Message\ResponseInterface $response */
      $response = $this->httpClient->post(
        $endpoint_full_path,
        [
          'json' => [
            'refreshToken' => $this->state->get('mars_lighthouse.refresh_token') ?: '',
            'token' => $this->state->get('mars_lighthouse.access_token') ?: '',
            'requestTime' => date(static::DATE_FORMAT),
          ],
          'headers' => $this->state->get('mars_lighthouse.headers') ?: '',
        ]
      );
    }
    catch (RequestException $exception) {
      $this->requestToken();
      $this->logger->error('Failed to refresh access token "%error"', ['%error' => $exception->getMessage()]);
    }

    $header_value = $response->getHeaders()['x-lighthouse-authen'];
    $header_value = is_array($header_value) ? array_shift($header_value) : $header_value;

    $response = $response->getBody()->getContents();
    $response = Json::decode($response);

    $tokens = [
      'mars_lighthouse.headers' => ['x-lighthouse-authen' => $header_value],
      'response' => $response,
    ];

    // Save refreshed tokens.
    $tokens['mars_lighthouse.access_token'] = $tokens['response']['lhisToken'];
    $tokens['mars_lighthouse.refresh_token'] = $tokens['response']['refreshToken'];
    $tokens['mars_lighthouse.refresh_time'] = $tokens['response']['refreshTime'];
    unset($tokens['response']);
    $this->state->setMultiple($tokens);

    return $tokens;
  }

  /**
   * Checks if token is expired.
   */
  private function tokenExpired() {
    $tokenExpirationTimeObject = \DateTime::createFromFormat('Y-m-d-H-i-s e', $this->state->get('mars_lighthouse.refresh_time'));
    if (!$tokenExpirationTimeObject instanceof \DateTime) {
      return TRUE;
    }
    $tokenExpirationTimeTimestamp = $tokenExpirationTimeObject->getTimestamp() - static::TTL_CORRECTION_TIME_SEC;
    return $this->tokenExists() && time() > $tokenExpirationTimeTimestamp;
  }

  /**
   * Checks if token exists.
   */
  private function tokenExists() {
    $token_exist = TRUE;
    $tokens = $this->state->getMultiple(static::KEYS_FOR_TOKEN);

    if (!$tokens) {
      $token_exist = FALSE;
    }

    // Check that tokens were saved.
    foreach ($tokens as $value) {
      if (!$value) {
        $token_exist = FALSE;
        break;
      }
    }
    return $token_exist;
  }

}
