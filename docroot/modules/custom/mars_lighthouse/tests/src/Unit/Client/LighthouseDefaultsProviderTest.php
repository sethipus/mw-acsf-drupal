<?php

namespace Drupal\Tests\mars_lighthouse\Unit\Client;

use Drupal\Core\Site\Settings;
use Drupal\mars_lighthouse\Client\LighthouseDefaultsProvider;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for LighthouseDefaultsProvider class.
 *
 * @coversDefaultClass \Drupal\mars_lighthouse\Client\LighthouseDefaultsProvider
 */
class LighthouseDefaultsProviderTest extends UnitTestCase {

  /**
   * Tests that it returns the correct default username from settings.
   *
   * @test
   */
  public function shouldReturnCorrectDefaultUsername() {
    $expected_username = 'expected_username';
    $settings = new Settings([
      'mars_lighthouse_defaults' => [
        'client_id' => $expected_username,
      ],
    ]);

    $defaults_provider = new LighthouseDefaultsProvider($settings);

    $this->assertEquals($expected_username,
      $defaults_provider->getDefaultUsername());
  }

  /**
   * Tests that it returns the correct default password from settings.
   *
   * @test
   */
  public function shouldReturnCorrectDefaultPassword() {
    $expected_password = 'expected_password';
    $settings = new Settings([
      'mars_lighthouse_defaults' => [
        'client_secret' => $expected_password,
      ],
    ]);

    $defaults_provider = new LighthouseDefaultsProvider($settings);

    $this->assertEquals($expected_password,
      $defaults_provider->getDefaultPassword());
  }

  /**
   * Tests that it returns the correct default password from settings.
   *
   * @test
   */
  public function shouldReturnCorrectDefaultApiKey() {
    $expected_api_key = 'expected_api_key';
    $settings = new Settings([
      'mars_lighthouse_defaults' => [
        'api_key' => $expected_api_key,
      ],
    ]);

    $defaults_provider = new LighthouseDefaultsProvider($settings);

    $this->assertEquals($expected_api_key,
      $defaults_provider->getDefaultApiKey());
  }

  /**
   * Tests that it returns the correct default base path from settings.
   *
   * @test
   */
  public function shouldReturnCorrectDefaultBasePath() {
    $expected_base_path = 'expected_base_path';
    $settings = new Settings([
      'mars_lighthouse_defaults' => [
        'base_path' => $expected_base_path,
      ],
    ]);

    $defaults_provider = new LighthouseDefaultsProvider($settings);

    $this->assertEquals($expected_base_path,
      $defaults_provider->getDefaultBasePath());
  }

  /**
   * Tests that it returns the correct default api version from settings.
   *
   * @test
   */
  public function shouldReturnCorrectDefaultApiVersion() {
    $expected_api_version = 'v1';
    $settings = new Settings([
      'mars_lighthouse_defaults' => [
        'api_version' => $expected_api_version,
      ],
    ]);

    $defaults_provider = new LighthouseDefaultsProvider($settings);

    $this->assertEquals($expected_api_version,
      $defaults_provider->getDefaultApiVersion());
  }

  /**
   * Tests that it returns the correct default port from settings.
   *
   * @test
   */
  public function shouldReturnCorrectDefaultPort() {
    $expected_port = 15;
    $settings = new Settings([
      'mars_lighthouse_defaults' => [
        'port' => $expected_port,
      ],
    ]);

    $defaults_provider = new LighthouseDefaultsProvider($settings);

    $this->assertEquals($expected_port, $defaults_provider->getDefaultPort());
  }

}
