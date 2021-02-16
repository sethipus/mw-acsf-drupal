<?php

namespace Drupal\mars_product\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\mars_media\MediaHelper;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides a controllers for wtb widget.
 */
class WtbProductController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The Media helper.
   *
   * @var \Drupal\mars_media\MediaHelper
   */
  private $mediaHelper;

  /**
   * Return product info.
   *
   * @param \Drupal\mars_media\MediaHelper $media_helper
   *   The renderer.
   */
  public function __construct(
    MediaHelper $media_helper
  ) {
    $this->mediaHelper = $media_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mars_media.media_helper')
    );
  }

  /**
   * Page callback: Retrieves autocomplete suggestions.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The autocompletion response.
   */
  public function productInfo(NodeInterface $node) {
    $variants = $node->get('field_product_variants')
      ->referencedEntities();

    $result = [];
    foreach ($variants as $variant) {

      /* @var \Drupal\node\NodeInterface $variant */
      $media_override_id = $variant->get('field_product_key_image_override')
        ->target_id;
      $media_params = $this->mediaHelper->getMediaParametersById($media_override_id);

      // Override media missing or has error try the normal version.
      if ($media_params['error'] ?? FALSE) {
        $media_id = $variant->get('field_product_key_image')->target_id;
        $media_params = $this->mediaHelper->getMediaParametersById($media_id);
      }

      $image_src = $image_alt = NULL;
      // Override media and the normal version both failed, we should skip this.
      if (isset($media_params['src'])) {
        $image_src = (string) $media_params['src'];
        $image_alt = $media_params['alt'];
      }

      $result[] = [
        'size' => $variant->get('field_product_size')->value,
        'image_src' => $image_src,
        'image_alt' => $image_alt,
        'gtin' => $variant->get('field_product_sku')->value,
      ];
    }

    return new JsonResponse($result);
  }

}
