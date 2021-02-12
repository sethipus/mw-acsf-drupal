<?php

namespace Drupal\mars_media\Media;

/**
 * Class representing an image URI that does not support resizing.
 */
class NonResizableImageUri implements ImageUriInterface {

  /**
   * Url for the image.
   *
   * @var string
   */
  private $url;

  /**
   * NonResizableImageUrl constructor.
   *
   * @param string $url
   *   The url for the image.
   */
  public function __construct(string $url) {
    $this->url = $url;
  }

  /**
   * {@inheritdoc}
   */
  public function resizeByWidth(int $width): ImageUriInterface {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function resizeByHeight(int $height): ImageUriInterface {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function resize(int $width, int $height): ImageUriInterface {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString(): string {
    return $this->url;
  }

}
