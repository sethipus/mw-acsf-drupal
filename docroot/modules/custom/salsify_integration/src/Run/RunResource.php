<?php

namespace Drupal\salsify_integration\Run;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpFoundation\Response;

use function GuzzleHttp\json_decode;

/**
 * Salsify channel runner.
 */
class RunResource {

  /**
   * Resource URI.
   */
  protected const URI = 'https://app.salsify.com/api/orgs/%s/channels/%s/runs';

  /**
   * Bearer authentication header.
   */
  protected const HEADER_BEARER_AUTH = 'Bearer %s';

  /**
   * Run ID 'latest' value.
   */
  protected const LATEST = 'latest';

  /**
   * HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerChannelFactory;

  /**
   * Constructor.
   */
  public function __construct(
    ClientInterface $httpClient,
    ConfigFactoryInterface $configFactory,
    LoggerChannelFactoryInterface $loggerChannelFactory
  ) {
    $this->httpClient = $httpClient;
    $this->configFactory = $configFactory;
    $this->loggerChannelFactory = $loggerChannelFactory;
  }

  /**
   * Request channel to create export data.
   *
   * @return RunResponse
   *   Run response object.
   */
  public function create() {
    $config = $this->configFactory->getEditable('salsify_integration.settings');

    $config->set('salsify_integration.salsify_multichannel_approach.run_id', '');
    $config->save();

    $uri = sprintf(
      static::URI,
      $config->get('salsify_multichannel_approach.org_id'),
      $config->get('salsify_multichannel_approach.channel_id'),
    );

    $response = $this->httpClient->post($uri, [
      RequestOptions::HEADERS => [
        'Authorization' => sprintf(
          static::HEADER_BEARER_AUTH,
          $config->get('salsify_multichannel_approach.api_key'),
        ),
      ],
    ]);

    if ($response->getStatusCode() == Response::HTTP_CREATED) {
      /** @var RunResponse $run */
      $run = json_decode($response->getBody()->getContents());

      if ($run->status === RunResponse::STATUS_RUNNING) {
        $config->set('salsify_multichannel_approach.run_id', $run->id);
        $config->save();
      }

      return $run;
    }
    else {
      $this->loggerChannelFactory->get(static::class)->error($response->getStatusCode() . ' ' . $response->getReasonPhrase());
    }
  }

  /**
   * Read the latest import.
   *
   * @return RunResponse
   *   Run response object.
   */
  public function read($id = NULL) {
    $config = $this->configFactory->getEditable('salsify_integration.settings');

    if (empty($id)) {
      $id = $config->get('salsify_multichannel_approach.run_id');
    }

    if (empty($id)) {
      $id = static::LATEST;
    }

    $uri = sprintf(
        static::URI,
        $config->get('salsify_multichannel_approach.org_id'),
        $config->get('salsify_multichannel_approach.channel_id'),
      ) . '/' . $id;

    $response = $this->httpClient->get($uri, [
      RequestOptions::HEADERS => [
        'Authorization' => sprintf(
          static::HEADER_BEARER_AUTH,
          $config->get('salsify_multichannel_approach.api_key'),
        ),
      ],
    ]);

    if ($response->getStatusCode() == Response::HTTP_OK) {
      /** @var RunResponse $run */
      $run = json_decode($response->getBody()->getContents());

      if ($run->status === RunResponse::STATUS_COMPLETED) {
        $config->set('salsify_multichannel_approach.url', $run->product_export_url);
        $config->save();
        return $run;
      }
      else {
        sleep(10);
        $this->read();
      }
      return $run;
    }
    else {
      $this->loggerChannelFactory->get(static::class)->error($response->getStatusCode() . ' ' . $response->getReasonPhrase());
    }
  }

}
