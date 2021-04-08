<?php

namespace Drupal\mars_media\Media;

/**
 * Class that represents the resize path fragment in an LH uri.
 */
class CFResizePathFragment {

  private const FRAGMENT_TEMPLATE = '/cdn-cgi/image/%s';

  /**
   * The resize parameters.
   *
   * @var string[]
   */
  private $params;

  /**
   * LHResizePathFragment constructor.
   *
   * @param array $params
   *   The resize parameters.
   */
  public function __construct(array $params) {
    $this->params = $params;
  }

  /**
   * Returns the path fragment as a string.
   *
   * @return string
   *   The LH resize path fragment as a string.
   */
  public function __toString(): string {

    $keyValuePairs = array_map(
      function ($param_name, $param_value) {
        return $param_name . '=' . $param_value;
      },
      array_keys($this->params),
      $this->params
    );
    return sprintf(self::FRAGMENT_TEMPLATE, implode(',', $keyValuePairs));
  }

}
