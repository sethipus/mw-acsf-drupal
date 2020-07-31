<?php

namespace Drupal\mars_utils\Time;

/**
 * Utility class for making time related calls more easily mockable.
 */
class TimeProvider {

  /**
   * Returns the current timestamp.
   *
   * @return int
   *   The current timestamp.
   */
  public function timestamp(): int {
    return time();
  }

}
