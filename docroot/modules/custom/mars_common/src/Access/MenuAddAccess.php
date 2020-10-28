<?php

namespace Drupal\mars_common\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Menu\MenuTreeParameters;
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
   * The menu link tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTree
   */
  protected $menuLinkTree;

  /**
   * {@inheritdoc}
   */
  public function __construct(RouteMatchInterface $route_match, MenuLinkTree $menu_link_tree) {
    $this->routeMatch = $route_match;
    $this->menuLinkTree = $menu_link_tree;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    $param_menu = $this->routeMatch->getParameter('menu');
    if ($param_menu && $param_menu->id() === MenuConstants::MAIN_MENU_ID) {
      $menu_tree_params = new MenuTreeParameters();
      $menu_tree_params->setMaxDepth(1);
      $menu_tree = $this->menuLinkTree->load(MenuConstants::MAIN_MENU_ID, $menu_tree_params);
      if ($menu_tree && count($menu_tree) >= MenuConstants::MAIN_MENU_ITEM_COUNT_LIMIT) {
        return AccessResult::forbidden();
      }
    }
    return AccessResult::allowed();
  }

}
