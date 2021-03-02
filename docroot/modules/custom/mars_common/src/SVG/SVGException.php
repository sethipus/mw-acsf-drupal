<?php

namespace Drupal\mars_common\SVG;

/**
 * Class InvalidFileException
 *
 * @package mars_common\src\SVG
 */
class SVGException extends \Exception {

  /**
   * Creates and exception for when file does not exists.
   *
   * @param string $uri
   *   The uri of the file.
   *
   * @return static
   *   The new exception object.
   */
  public static function missingPhysicalFile(string $uri): self {
    $message = sprintf("SVG file at %s does not exists.", $uri);
    return new self($message);
  }
  /**
   * Creates and exception for when file could not be read from.
   *
   * @param string $uri
   *   The uri of the file.
   *
   * @return static
   *   The new exception object.
   */
  public static function readingFromFileFailed(string $uri): self {
    $message = sprintf("SVG file at %s could not be read.", $uri);
    return new self($message);
  }

  /**
   * Creates and exception for when a file entity is missing.
   *
   * @param string $file_id
   *   The id of the file entity.
   *
   * @return static
   *   The new exception object.
   */
  public static function missingDrupalFile(string $file_id): self {
    $message = sprintf("File entity does not exists with %s id.", $file_id);
    return new self($message);
  }

}
