<?php

namespace Drupal\mars_media\Twig;

use Drupal\mars_media\Media\ImageUriInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class responsible to define and add Image resize related twig filters.
 */
class ImageResizeFilters extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return [
      new TwigFilter('resize', [$this, 'resize'], ['is_safe' => ['html']]),
      new TwigFilter('resizeWithHighDpr', [$this, 'resizeWithHighDpr'], ['is_safe' => ['html']]),
      new TwigFilter('resizeWithGravity', [$this, 'resizeWithGravity'], ['is_safe' => ['html']]),
      new TwigFilter('resizeByWidth', [$this, 'resizeByWidth'], ['is_safe' => ['html']]),
      new TwigFilter('resizeByHeight', [$this, 'resizeByHeight'], ['is_safe' => ['html']]),
    ];
  }

  /**
   * Apply the resize based on width and height to the ImageUri instance.
   *
   * @param \Drupal\mars_media\Media\ImageUriInterface|mixed $value
   *   The image uri instance or possibly something else.
   * @param int $width
   *   The requested width in pixels.
   * @param int $height
   *   The requested height in pixels.
   *
   * @return \Drupal\mars_media\Media\ImageUriInterface|mixed
   *   The resulting object, or the original value if it's not an ImageUri.
   */
  public function resize($value, int $width, int $height) {
    if (!$value instanceof ImageUriInterface) {
      return $value;
    }
    return $value->resize($width, $height);
  }

  /**
   * Apply the resize based on width and height to the ImageUri instance.
   *
   * @param \Drupal\mars_media\Media\ImageUriInterface|mixed $value
   *   The image uri instance or possibly something else.
   * @param int $width
   *   The requested width in pixels.
   * @param int $height
   *   The requested height in pixels.
   * @param int $dpr
   *   Device Pixel Ratio multiplier.
   *
   * @return \Drupal\mars_media\Media\ImageUriInterface|mixed
   *   The resulting object, or the original value if it's not an ImageUri.
   */
  public function resizeWithHighDpr($value, int $width, int $height, int $dpr) {
    if (!$value instanceof ImageUriInterface) {
      return $value;
    }
    return $value->resizeWithHighDpr($width, $height, $dpr);
  }

  /**
   * Apply the resize based on width and height to the ImageUri instance.
   *
   * @param \Drupal\mars_media\Media\ImageUriInterface|mixed $value
   *   The image uri instance or possibly something else.
   * @param int $width
   *   The requested width in pixels.
   * @param int $height
   *   The requested height in pixels.
   *
   * @return \Drupal\mars_media\Media\ImageUriInterface|mixed
   *   The resulting object, or the original value if it's not an ImageUri.
   */
  public function resizeWithGravity($value, int $width, int $height) {
    if (!$value instanceof ImageUriInterface) {
      return $value;
    }
    return $value->resizeWithGravity($width, $height);
  }

  /**
   * Apply the resize based on width to the ImageUri instance.
   *
   * @param \Drupal\mars_media\Media\ImageUriInterface|mixed $value
   *   The image uri instance or possibly something else.
   * @param int $width
   *   The requested width in pixels.
   *
   * @return \Drupal\mars_media\Media\ImageUriInterface|mixed
   *   The resulting object, or the original value if it's not an ImageUri.
   */
  public function resizeByWidth($value, int $width) {
    if (!$value instanceof ImageUriInterface) {
      return $value;
    }
    return $value->resizeByWidth($width);
  }

  /**
   * Apply the resize based on height to the ImageUri instance.
   *
   * @param \Drupal\mars_media\Media\ImageUriInterface|mixed $value
   *   The image uri instance or possibly something else.
   * @param int $height
   *   The requested height in pixels.
   *
   * @return \Drupal\mars_media\Media\ImageUriInterface|mixed
   *   The resulting object, or the original value if it's not an ImageUri.
   */
  public function resizeByHeight($value, int $height) {
    if (!$value instanceof ImageUriInterface) {
      return $value;
    }
    return $value->resizeByHeight($height);
  }

}
