<?php

namespace Drupal\juicer_io\Model;

/**
 * Exception for feed related errors.
 */
class FeedException extends \Exception {

  /**
   * Wraps an exception.
   *
   * @param \Exception $e
   *   The original exception.
   * @param string $string
   *   Additional error clarification string.
   *
   * @return static
   */
  public static function wrap(\Exception $e, string $string): self {
    $message = $string . ' Error: ' . $e->getMessage();
    return new static($message, $e->getCode(), $e);
  }

}
