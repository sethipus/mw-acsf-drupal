<?php

namespace Drupal\mars_seo;

use Drupal\block\BlockInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mars_common\MediaHelper;
use Drupal\node\NodeInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Hero image service.
 */
class HeroImageService {

  /**
   * Home page route name.
   */
  const HOMEPAGE_ROUTE_NAME = 'view.frontpage.page_1';

  /**
   * Home page hero block id.
   */
  const HOMEPAGE_HERO_BLOCK_ID = 'homepageheroblock';

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
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Constructs a HeroImageService object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RouteMatchInterface $route_match, MediaHelper $media_helper, AccountInterface $account) {
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
    $this->mediaHelper = $media_helper;
    $this->account = $account;
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
    // Home page hero image.
    elseif ($this->routeMatch->getRouteName() == self::HOMEPAGE_ROUTE_NAME) {
      $homepage_hero_block = $this->entityTypeManager->getStorage('block')->load(self::HOMEPAGE_HERO_BLOCK_ID);
      if ($homepage_hero_block instanceof BlockInterface && $homepage_hero_block->access('view', $this->account)) {
        if ($homepage_hero_block->get('settings')['block_type'] === 'image') {
          if ($homepage_hero_block->get('settings')['background_image']) {
            $mediaId = $this->mediaHelper->getIdFromEntityBrowserSelectValue($homepage_hero_block->get('settings')['background_image']);
            $mediaParams = $this->mediaHelper->getMediaParametersById($mediaId);
            if (!($mediaParams['error'] ?? FALSE) && ($mediaParams['src'] ?? FALSE)) {
              $main_image_url = $mediaParams['src'];
            }
          }
        }
      }
    }
    return $main_image_url;
  }



}
