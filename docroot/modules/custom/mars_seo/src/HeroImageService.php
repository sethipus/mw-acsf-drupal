<?php

namespace Drupal\mars_seo;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\node\NodeInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Hero image service.
 */
class HeroImageService {

  /**
   * Blocks structure with hero image.
   */
  const BLOCKS_IDS_HERO_IMAGES = [
    'homepage_hero_block' => [
      'background_type_field' => 'block_type',
      'hero_image_field' => 'background_image',
    ],
    'parent_page_header' => [
      'background_type_field' => 'background_options',
      'hero_image_field' => 'background_image',
    ],
  ];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The route match object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Mars Common Media Helper.
   *
   * @var \Drupal\mars_common\MediaHelper
   */
  protected $mediaHelper;

  /**
   * Constructs a HeroImageService object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RouteMatchInterface $route_match, MediaHelper $media_helper) {
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
    $this->mediaHelper = $media_helper;
  }

  /**
   * Returns hero image.
   */
  public function getHeroImage() {
    $main_image_url = NULL;
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->routeMatch->getParameter('node');

    if ($node instanceof NodeInterface) {
      $main_image_id = $this->mediaHelper->getEntityMainMediaId($node);
      $main_image_url = $this->mediaHelper->getMediaUrl($main_image_id);
    }

    // Images from block.
    if (empty($main_image_url) && $node instanceof NodeInterface) {
      foreach (self::BLOCKS_IDS_HERO_IMAGES as $key => $block_id) {
        $sections = $node->get('layout_builder__layout')->getSections();
        foreach ($sections as $section) {
          /* @var $section \Drupal\layout_builder\Section */
          $components = $section->getComponents();
          foreach ($components as $component) {
            /* @var $component \Drupal\layout_builder\SectionComponent */
            $configuration = $component->get('configuration');
            if ($configuration['id'] == $key &&
              $configuration[self::BLOCKS_IDS_HERO_IMAGES[$key]['background_type_field']] === 'image' &&
              $configuration[self::BLOCKS_IDS_HERO_IMAGES[$key]['hero_image_field']]
            ) {
              $mediaId = $this->mediaHelper->getIdFromEntityBrowserSelectValue($configuration[self::BLOCKS_IDS_HERO_IMAGES[$key]['hero_image_field']]);
              $mediaParams = $this->mediaHelper->getMediaParametersById($mediaId);
              if (!($mediaParams['error'] ?? FALSE) && ($mediaParams['src'] ?? FALSE)) {
                $main_image_url = $mediaParams['src'];
                break 3;
              }
            }
          }
        }
      }
    }
    return $main_image_url;
  }

}
