<?php

namespace Drupal\mars_common\SVG;

/**
 * Class for generating unique ids.
 *
 * The generated ids are only unique in context of a single generator instance.
 */
class SVGUniqueIdGenerator {

  /**
   * @var int
   */
  private $counter = 0;

  /**
   * Generates a unique id.
   *
   * @return string
   *   The id.
   */
  public function generateId(): string {
    return 'id' . $this->counter++;
  }

}
