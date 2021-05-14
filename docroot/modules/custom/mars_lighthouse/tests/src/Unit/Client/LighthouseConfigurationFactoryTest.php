<?php

namespace Drupal\Tests\mars_lighthouse\Unit\Client;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mars_lighthouse\Client\LighthouseConfiguration;
use Drupal\mars_lighthouse\Client\LighthouseConfigurationFactory;
use Drupal\mars_lighthouse\Client\LighthouseDefaultsProvider;
use Drupal\mars_lighthouse\LighthouseException;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for LighthouseConfigurationFactory class.
 *
 * @coversDefaultClass \Drupal\mars_lighthouse\Client\LighthouseConfigurationFactory
 */
class LighthouseConfigurationFactoryTest extends UnitTestCase {

  /**
   * Mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactoryMock;

  /**
   * Defaults provider mock.
   *
   * @var \Drupal\mars_lighthouse\Client\LighthouseDefaultsProvider|\PHPUnit\Framework\MockObject\MockObject
   */
  private $defaultsProviderMock;

  /**
   * Lighthouse config factory.
   *
   * @var \Drupal\mars_lighthouse\Client\LighthouseConfigurationFactory
   */
  private $lHconfigFactory;

  /**
   * Mocked config object.
   *
   * @var \Drupal\Core\Config\Config|\Prophecy\Prophecy\ObjectProphecy
   */
  private $configMock;

  /**
   * Array holding the mocked config values.
   *
   * @var array
   */
  private $configValues;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMocks();

    $this->lHconfigFactory = new LighthouseConfigurationFactory(
      $this->defaultsProviderMock,
      $this->configFactoryMock
    );
  }

  /**
   * Tests if the resulting object is of the correct class.
   *
   * @test
   */
  public function shouldReturnAnObjectWithCorrectClass() {
    $expected_api_key = 'expected_api_key';

    $this->configHasValue('client_id', $expected_api_key);

    $config = $this->lHconfigFactory->createConfiguration();

    $this->assertInstanceOf(LighthouseConfiguration::class, $config);
  }

  /**
   * Tests if the config will have the username from the drupal config.
   *
   * @test
   */
  public function shouldCreateConfigWithCorrectUsername() {
    $expected_username = 'expected_username';

    $this->configHasValue('client_id', $expected_username);

    $config = $this->lHconfigFactory->createConfiguration();

    $this->assertEquals($expected_username, $config->getUsername());
  }

  /**
   * Tests if the config will have the default username.
   *
   * @test
   */
  public function shouldCreateConfigWithCorrectDefaultUsername() {
    $expected_username = 'expected_default_username';

    $this->configHasNoValue('client_id');
    $this->defaultsProviderMock
      ->method('getDefaultUsername')
      ->willReturn($expected_username);

    $config = $this->lHconfigFactory->createConfiguration();

    $this->assertEquals($expected_username, $config->getUsername());
  }

  /**
   * Tests if the config will have the password from the drupal config.
   *
   * @test
   */
  public function shouldCreateConfigWithCorrectPassword() {
    $expected_password = 'expected_password';

    $this->configHasValue('client_secret', $expected_password);

    $config = $this->lHconfigFactory->createConfiguration();

    $this->assertEquals($expected_password, $config->getPassword());
  }

  /**
   * Tests if the config will have the default password.
   *
   * @test
   */
  public function shouldCreateConfigWithCorrectDefaultPassword() {
    $expected_password = 'expected_default_password';

    $this->configHasNoValue('client_secret');
    $this->defaultsProviderMock
      ->method('getDefaultPassword')
      ->willReturn($expected_password);

    $config = $this->lHconfigFactory->createConfiguration();

    $this->assertEquals($expected_password, $config->getPassword());
  }

  /**
   * Tests if the config will have the api key from the drupal config.
   *
   * @test
   */
  public function shouldCreateConfigWithCorrectApiKey() {
    $expected_api_key = 'expected_api_key';

    $this->configHasValue('api_key', $expected_api_key);

    $config = $this->lHconfigFactory->createConfiguration();

    $this->assertEquals($expected_api_key, $config->getApiKey());
  }

  /**
   * Tests if the config will have the default api key.
   *
   * @test
   */
  public function shouldCreateConfigWithCorrectDefaultApiKey() {
    $expected_api_key = 'expected_default_api_key';

    $this->configHasNoValue('api_key');
    $this->defaultsProviderMock
      ->method('getDefaultApiKey')
      ->willReturn($expected_api_key);

    $config = $this->lHconfigFactory->createConfiguration();

    $this->assertEquals($expected_api_key, $config->getApiKey());
  }

  /**
   * Test that the config will create endpoint based on drupal config values.
   *
   * @test
   */
  public function shouldCreateConfigWithCorrectEndpointConfiguration() {

    $this->configHasValue('base_path', 'http://x.y');
    $this->configHasValue('port', 80);

    $config = $this->lHconfigFactory->createConfiguration();

    $getTokenEndpoint = $config->getEndpointFullPath(LighthouseConfiguration::ENDPOINT_GET_TOKEN);
    $this->assertStringStartsWith('http://x.y:80/lh-integration/api/v1/', $getTokenEndpoint);
  }

  /**
   * Test that the config will create endpoint based on default values.
   *
   * @test
   */
  public function shouldCreateConfigWithCorrectEndpointConfigurationBasedOnDefaults() {
    $this->configHasNoValue('base_path');
    $this->defaultsProviderMock
      ->method('getDefaultBasePath')
      ->willReturn('http://default.a');

    $this->configHasNoValue('port');
    $this->defaultsProviderMock
      ->method('getDefaultPort')
      ->willReturn(443);

    $config = $this->lHconfigFactory->createConfiguration();

    $getTokenEndpoint = $config->getEndpointFullPath(LighthouseConfiguration::ENDPOINT_GET_TOKEN);
    $this->assertStringStartsWith('http://default.a:443/lh-integration/api/v1/',
      $getTokenEndpoint);
  }

  /**
   * Data provider function for config keys.
   *
   * @return array
   *   The provided data.
   */
  public function configKeysDataProvider() {
    return [
      ['client_id'],
      ['client_secret'],
      ['api_key'],
      ['base_path'],
      ['port'],
      ['api_version'],
    ];
  }

  /**
   * Tests if it throws and exceptions if it couldn't determine a value.
   *
   * @param string $missing_config_key
   *   The missing config key.
   *
   * @test
   * @dataProvider configKeysDataProvider
   */
  public function shouldThrowIfConfigIsMissingAndNoDefaultIsSet(
    string $missing_config_key
  ) {
    $this->configHasNoValue($missing_config_key);

    $this->expectException(LighthouseException::class);

    $this->lHconfigFactory->createConfiguration();
  }

  /**
   * Create all mocks for tests in this file.
   */
  private function createMocks(): void {
    $this->configFactoryMock = $this->createMock(ConfigFactoryInterface::class);
    $this->defaultsProviderMock = $this->createMock(LighthouseDefaultsProvider::class);
    $this->configValues = [];
    $this->configHasValue('client_id', '');
    $this->configHasValue('client_secret', '');
    $this->configHasValue('api_key', '');
    $this->configHasValue('base_path', '');
    $this->configHasValue('port', 10);
    $this->configHasValue('api_version', 'v1');

    $this->configMock = $this->createMock(Config::class);
    $this->configMock
      ->method('get')
      ->willReturnCallback(function ($name) {
        if (!isset($this->configValues[$name])) {
          return NULL;
        }
        return $this->configValues[$name];
      });

    $this->configFactoryMock
      ->method('get')
      ->with('mars_lighthouse.settings')
      ->willReturn(
        $this->configMock
      );
  }

  /**
   * Sets a config value for the mocked config.
   *
   * @param string $config_key
   *   The key of the config value.
   * @param mixed $value
   *   The value.
   */
  private function configHasValue(string $config_key, $value) {
    $this->configValues[$config_key] = $value;
  }

  /**
   * Ensures that the given config key has no value.
   *
   * @param string $config_key
   *   The key of the config value.
   */
  private function configHasNoValue(string $config_key) {
    unset($this->configValues[$config_key]);
  }

}
