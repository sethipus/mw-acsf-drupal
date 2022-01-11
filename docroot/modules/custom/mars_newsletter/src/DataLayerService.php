<?php

namespace Drupal\mars_newsletter;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class CustomService.
 */
class DataLayerService {

   /**
   * Contains the configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Adds data about event.
   *
   * @param array $data
   *   The event data.
   */
  public function addData(array $data) {
    $config = $this->configFactory->getEditable('emulsifymars.settings');
    $config->set('data', $data);
    $config->save(TRUE);
  }

  /**
   * Gets data about event.
   */
  public function getData() {
    $config = $this->configFactory->getEditable('emulsifymars.settings');
    $data = $config->get('data');
    return $data;
  }
}
