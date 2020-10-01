<?php

namespace Drupal\mars_common\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Menu\MenuLinkManager;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\mars_common\Constants\MenuConstants;

/**
 * Class MenuAddAccess.
 *
 * @package Drupal\mars_common\Access
 */
class MenuAddAccess implements AccessInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The menu link manager.
   *
   * @var \Drupal\Core\Menu\MenuLinkManager
   */
  protected $menuLinkManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(RouteMatchInterface $route_match, MenuLinkManager $menu_link_manager) {
    $this->routeMatch = $route_match;
    $this->menuLinkManager = $menu_link_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    $param_menu = $this->routeMatch->getParameter('menu');
    if ($param_menu->id() === MenuConstants::MAIN_MENU_ID) {
      if ($this->menuLinkManager->countMenuLinks('main') >= MenuConstants::MAIN_MENU_ITEM_COUNT_LIMIT) {
        return AccessResult::forbidden();
      }
    }
    return AccessResult::allowed();
  }

}
