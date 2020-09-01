<?php

namespace Drupal\juicer_io\Model;

/**
 * Interface for feed configuration.
 */
interface FeedConfigurationInterface {

  /**
   * Returns the url of the api endpoint.
   *
   * @return string
   *   The url of the feed.
   */
  public function getUrl(): string;

}
