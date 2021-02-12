<?php

namespace Drupal\mars_media\Media;

/**
 * Interface representing an URI for an image.
 */
interface ImageUriInterface {

  /**
   * Apply on the fly resize based on width to the URI if it supports it.
   *
   * @param int $width
   *   The required width in pixels.
   *
   * @return \Drupal\mars_media\Media\ImageUriInterface
   *   ImageUri instance with resize applied.
   */
  public function resizeByWidth(int $width): ImageUriInterface;

  /**
   * Apply on the fly resize based on height to the URI if it supports it.
   *
   * @param int $height
   *   The required height in pixels.
   *
   * @return \Drupal\mars_media\Media\ImageUriInterface
   *   ImageUri instance with resize applied.
   */
  public function resizeByHeight(int $height): ImageUriInterface;

  /**
   * Apply on the fly resize based on height and width if it supports it.
   *
   * @param int $width
   *   The required width in pixels.
   * @param int $height
   *   The required height in pixels.
   *
   * @return \Drupal\mars_media\Media\ImageUriInterface
   *   ImageUri instance with resize applied.
   */
  public function resize(int $width, int $height): ImageUriInterface;

  /**
   * Converts the object to string.
   *
   * @return string
   *   The url for the image.
   */
  public function __toString(): string;

}
