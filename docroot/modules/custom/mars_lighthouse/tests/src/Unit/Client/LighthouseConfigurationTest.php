<?php

namespace Drupal\Tests\mars_lighthouse\Unit\Client;

use Drupal\mars_lighthouse\Client\LighthouseConfiguration;
use Drupal\mars_lighthouse\LighthouseException;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for LighthouseConfiguration class.
 *
 * @coversDefaultClass \Drupal\mars_lighthouse\Client\LighthouseConfiguration
 */
class LighthouseConfigurationTest extends UnitTestCase {

  /**
   * The configuration object.
   *
   * @var \Drupal\mars_lighthouse\Client\LighthouseConfiguration
   */
  private $configuration;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->configuration = new LighthouseConfiguration(
      'test_username',
      'test_password',
      'test_api_key',
      'https://base_path.test',
      80,
      'v1',
    );
  }

  /**
   * Tests that it returns the correct username.
   *
   * @test
   */
  public function shouldReturnCorrectUsername() {
    $this->assertEquals('test_username', $this->configuration->getUsername());
  }

  /**
   * Tests that it returns the correct password.
   *
   * @test
   */
  public function shouldReturnCorrectPassword() {
    $this->assertEquals('test_password', $this->configuration->getPassword());
  }

  /**
   * Tests that it returns the correct password.
   *
   * @test
   */
  public function shouldReturnCorrectApiKey() {
    $this->assertEquals('test_api_key', $this->configuration->getApiKey());
  }

  /**
   * Data provider for endpoints.
   */
  public function endpointDataProvider() {
    return [
      [
        LighthouseConfiguration::ENDPOINT_GET_TOKEN,
        'https://base_path.test:80/lh-integration/api/v1/session',
      ],
      [
        LighthouseConfiguration::ENDPOINT_REFRESH_TOKEN,
        'https://base_path.test:80/lh-integration/api/v1/session/refresh',
      ],
      [
        LighthouseConfiguration::ENDPOINT_SEARCH,
        'https://base_path.test:80/lh-integration/api/v1/search/001',
      ],
      [
        LighthouseConfiguration::ENDPOINT_ASSET_BY_ID,
        'https://base_path.test:80/lh-integration/api/v1/asset',
      ],
      [
        LighthouseConfiguration::ENDPOINT_GET_BRANDS,
        'https://base_path.test:80/lh-integration/api/v1/lookup/brand',
      ],
      [
        LighthouseConfiguration::ENDPOINT_GET_MARKETS,
        'https://base_path.test:80/lh-integration/api/v1/lookup/market',
      ],
    ];
  }

  /**
   * Tests that it returns the correct password.
   *
   * @param string $endpointType
   *   The endpoint type string.
   * @param string $expectedUrl
   *   The expected url.
   *
   * @test
   * @dataProvider endpointDataProvider
   */
  public function shouldGenerateCorrectEndpointUrls(
    string $endpointType,
    string $expectedUrl
  ) {
    $this->assertEquals(
      $expectedUrl,
      $this->configuration->getEndpointFullPath($endpointType)
    );
  }

  /**
   * Tests that it throws exception for invalid type values.
   *
   * @test
   */
  public function shouldThrowExceptionInCaseOfInvalidType() {
    $this->expectException(LighthouseException::class);
    $this->configuration->getEndpointFullPath('invalid_type_value');
  }

}
