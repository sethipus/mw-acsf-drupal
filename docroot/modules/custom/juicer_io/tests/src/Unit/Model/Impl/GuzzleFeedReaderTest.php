<?php

namespace Drupal\Tests\juicer_io\Unit\Model\Impl;

use Drupal\juicer_io\Entity\FeedConfiguration;
use Drupal\juicer_io\Model\FeedException;
use Drupal\juicer_io\Model\Impl\GuzzleFeedReader;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;

/**
 * Unit tests for GuzzleFeedReader class.
 *
 * @coversDefaultClass \Drupal\juicer_io\Model\Impl\GuzzleFeedReader
 */
class GuzzleFeedReaderTest extends UnitTestCase {

  /**
   * Mocked guzzle client service.
   *
   * @var \GuzzleHttp\ClientInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $guzzleClient;

  /**
   * Mocked feed config object.
   *
   * @var \Drupal\juicer_io\Entity\FeedConfiguration|\PHPUnit\Framework\MockObject\MockObject
   */
  private $config;

  /**
   * Mocked response object.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Http\Message\ResponseInterface
   */
  private $response;

  /**
   * Body of the mocked response.
   *
   * @var string
   */
  private $responseBody;

  /**
   * Api url of the feed config.
   *
   * @var string
   */
  private $apiUrl;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->guzzleClient = $this->createMock(ClientInterface::class);
    $this->config = $this->createMock(FeedConfiguration::class);
    $this->response = $this->createMock(ResponseInterface::class);

    $this->guzzleClient
      ->method('request')
      ->willReturn($this->response);
    $this->response->method('getBody')
      ->willReturnReference($this->responseBody);
    $this->config->method('getUrl')
      ->willReturnReference($this->apiUrl);

    $this->responseBodyWillBe('{}');
    $this->apiUrlIs('http://test.com');
  }

  /**
   * Tests that get request is made to the proper url.
   *
   * @test
   */
  public function shouldMakeGetRequestBasedOnConfig() {
    $feed_reader = new GuzzleFeedReader($this->config, $this->guzzleClient);
    $this->apiUrlIs('http://test.com/api/feed_id');

    $this->expectsRequestWithUri(function (Uri $uri) {
      return 'http' === $uri->getScheme() &&
        'test.com' === $uri->getHost() &&
        '/api/feed_id' === $uri->getPath();
    });

    $feed_reader->read([], NULL);
  }

  /**
   * Tests that query string is empty if there are no conditions.
   *
   * @test
   */
  public function shouldNotContainQueryParamsIfThereAreNoFilterOrMaxValue() {
    $feed_reader = new GuzzleFeedReader($this->config, $this->guzzleClient);
    $types_for_filter = [];
    $limit = NULL;

    $this->expectsRequestWithUri(function (Uri $uri) {
      return $uri->getQuery() === '';
    });

    $feed_reader->read($types_for_filter, $limit);
  }

  /**
   * Tests that request will contain proper filter part in query string.
   *
   * @test
   */
  public function shouldSetQueryForFilterTypes() {
    $feed_reader = new GuzzleFeedReader($this->config, $this->guzzleClient);
    $types_for_filter = ['type1', 'type2'];
    $expected_query = 'filter=type1,type2';

    $this->expectsRequestWithUri(function (Uri $uri) use ($expected_query) {
      return str_contains($uri->getQuery(), $expected_query);
    });

    $feed_reader->read($types_for_filter, NULL);
  }

  /**
   * Tests that request will contain proper limit part in query string.
   *
   * @test
   */
  public function shouldSetQueryForMaxLimit() {
    $feed_reader = new GuzzleFeedReader($this->config, $this->guzzleClient);
    $limit = 5;

    $this->expectsRequestWithUri(function (Uri $uri) {
      return $uri->getQuery() === 'per=5&page=1';
    });

    $feed_reader->read([], $limit);
  }

  /**
   * Tests that the response will be properly decoded.
   *
   * @test
   */
  public function shouldJsonDecodeTheResponse() {
    $feed_reader = new GuzzleFeedReader($this->config, $this->guzzleClient);
    $this->responseBodyWillBe('{"data_key": "data_value"}');

    $readData = $feed_reader->read([], NULL);

    $this->assertArrayHasKey('data_key', $readData);
    $this->assertEquals('data_value', $readData['data_key']);
  }

  /**
   * Tests that exception is thrown if the feed data is invalid.
   *
   * @test
   */
  public function shouldThrowExceptionInCaseOfInvalidJsonFeedData() {
    $feed_reader = new GuzzleFeedReader($this->config, $this->guzzleClient);
    $this->responseBodyWillBe('{this" is an invalid json encoded string');

    $this->expectException(FeedException::class);

    $feed_reader->read([], NULL);
  }

  /**
   * Tests that get request is made to the proper url.
   *
   * @test
   */
  public function shouldThrowExceptionInCaseOfGuzzleError() {
    $feed_reader = new GuzzleFeedReader($this->config, $this->guzzleClient);
    $this->guzzleClient
      ->method('request')
      ->willThrowException($this->createMock(GuzzleException::class));

    $this->expectException(FeedException::class);

    $feed_reader->read([], NULL);
  }

  /**
   * Sets api url in the mocked configuration.
   *
   * @param string $api_url
   *   The api url.
   */
  private function apiUrlIs(string $api_url): void {
    $this->apiUrl = $api_url;
  }

  /**
   * Sets the mocked response data.
   *
   * @param string $feed_raw_data
   *   Response data.
   */
  private function responseBodyWillBe(string $feed_raw_data): void {
    $this->responseBody = $feed_raw_data;
  }

  /**
   * Validate the request uri with a callback.
   *
   * @param \Closure $callback
   *   The callback which should return true if the URI is correct.
   */
  private function expectsRequestWithUri(\Closure $callback) {
    $this->guzzleClient
      ->expects($this->once())
      ->method('request')
      ->with('GET', $this->callback($callback))
      ->willReturn($this->response);
  }

}
