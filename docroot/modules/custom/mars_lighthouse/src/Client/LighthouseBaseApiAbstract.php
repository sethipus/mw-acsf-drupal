<?php

namespace Drupal\mars_lighthouse\Client;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;

/**
 * Class LighthouseBaseApiAbstract.
 *
 * Provide base api abstract.
 *
 * @package Drupal\mars_lighthouse\Service
 */
abstract class LighthouseBaseApiAbstract {

  /**
   * Error code when an access token is expired.
   */
  const TOKEN_IS_EXPIRED_ERROR_CODE = 400;

  /**
   * Error code when an access token is not found at Lighthouse.
   */
  const ACCESS_ERROR_CODE = 403;

  /**
   * Date format required by API.
   */
  const DATE_FORMAT = 'Y-m-d-H-i-s Z';

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
   * LighthouseBaseApiAbstract constructor.
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

}
