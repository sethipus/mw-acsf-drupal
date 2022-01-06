<?php

namespace Drupal\mars_newsletter;

use Drupal\Core\TempStore\PrivateTempStoreFactory;

class DataLayerService {

  /**
   * The private temp store.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $privateTempStore;

  /**
   * DataLayerService constructor.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $private_temp_store
   *   The private temp store.
   */
  public function __construct(PrivateTempStoreFactory $private_temp_store) {
    $this->privateTempStore = $private_temp_store->get('mars_newsletter');
  }

  /**
   * Adds data about event.
   *
   * @param array $data
   *   The event data.
   */
  public function addData(array $data) {
    $this->privateTempStore->set('data', $data);
  }

  /**
   * Gets data about event.
   */
  public function getData() {
    $data = $this->privateTempStore->get('data');
    $this->privateTempStore->delete('data');
    return $data;
  }
}
