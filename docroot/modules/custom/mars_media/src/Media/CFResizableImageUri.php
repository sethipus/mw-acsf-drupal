<?php

namespace Drupal\mars_media\Media;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/**
 * Class representing an image URI that support Cloudflare image resizing.
 */
class CFResizableImageUri implements ImageUriInterface {

  /**
   * Default CF image resize parameters.
   *
   * @var array
   */
  private $defaultParams = [
    'f' => 'auto',
    'quality' => 75,
  ];

  /**
   * Url for the image.
   *
   * @var \Psr\Http\Message\UriInterface
   */
  private $uri;

  /**
   * Resize path fragment, null if resize is not applied yet.
   *
   * @var \Drupal\mars_media\Media\CFResizePathFragment|null
   */
  private $resizePathFragment;

  /**
   * Creates and instance based on a URI string.
   *
   * @param string $uri
   *   The uri string.
   *
   * @return \Drupal\mars_media\Media\CFResizableImageUri
   *   The Image uri object.
   */
  public static function createFromUriString(string $uri): self {
    return new self(new Uri($uri));
  }

  /**
   * NonResizableImageUri constructor.
   *
   * @param \Psr\Http\Message\UriInterface $uri
   *   The url for the image.
   * @param \Drupal\mars_media\Media\CFResizePathFragment|null $resize_path_fragment
   *   The CF resize fragment that should be applied to the uri.
   */
  public function __construct(
    UriInterface $uri,
    ?CFResizePathFragment $resize_path_fragment = NULL
  ) {
    $this->uri = $uri;
    $this->resizePathFragment = $resize_path_fragment;
  }

  /**
   * {@inheritdoc}
   */
  public function resizeByWidth(int $width): ImageUriInterface {
    return $this->createResizeUriWith([
      'width' => $width,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function resizeByHeight(int $height): ImageUriInterface {
    return $this->createResizeUriWith([
      'height' => $height,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function resize(int $width, int $height): ImageUriInterface {
    return $this->createResizeUriWith([
      'width' => $width,
      'height' => $height,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function resizeWithHighDpr(int $width, int $height, int $dpr): ImageUriInterface {
    return $this->createResizeUriWith([
      'width' => $width,
      'height' => $height,
      'dpr' => $dpr,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function resizeWithGravity(
    int $width,
    int $height,
    string $fit = 'cover',
    string $gravity = 'auto'
  ): ImageUriInterface {
    return $this->createResizeUriWith([
      'width' => $width,
      'height' => $height,
      'fit' => $fit,
      'g' => $gravity,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString(): string {
    return $this->uri->withPath((string) $this->resizePathFragment . $this->uri->getPath());
  }

  /**
   * Creates a new instance with the given resize parameters applied.
   *
   * @param array $resizeParams
   *   The parameters that should be applied to CF resize.
   *
   * @return \Drupal\mars_media\Media\CFResizableImageUri
   *   The new Image Uri object.
   */
  private function createResizeUriWith(array $resizeParams): self {
    return new self($this->uri, new CFResizePathFragment(array_merge($resizeParams, $this->defaultParams)));
  }

}
