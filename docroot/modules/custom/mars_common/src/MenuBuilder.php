<?php

namespace Drupal\mars_common;

use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Template\Attribute;
use Drupal\menu_link_content\Plugin\Menu\MenuLinkContent;

/**
 * Class responsible for creating menu arrays to be used in our system.
 */
class MenuBuilder {

  /**
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  private $menuLinkTree;

  /**
   * MenuBuilder constructor.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_link_tree
   *   The menu link tree service.
   */
  public function __construct(MenuLinkTreeInterface $menu_link_tree) {
    $this->menuLinkTree = $menu_link_tree;
  }

  /**
   * Creates the menu array that is usable by our templates.
   *
   * @param string $menu_name
   *   Menu name.
   * @param int $max_depth
   *   The max menu depth to render.
   *
   * @return array
   *   Menu item array.
   */
  public function getMenuItemsArray(
    string $menu_name,
    int $max_depth = 1
  ): array {
    $menu_parameters = new MenuTreeParameters();
    $menu_parameters->setMaxDepth($max_depth);
    // Get the tree.
    $tree = $this->menuLinkTree->load($menu_name, $menu_parameters);
    // Apply some manipulators (checking the access, sorting).
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkNodeAccess'],
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuLinkTree->transform($tree, $manipulators);
    // And the last step is to actually build the tree.
    $menu = $this->menuLinkTree->build($tree);
    // Iterate menu items through translatable_menu_link module function to get
    // the correct menu link URI according to the selected site language.
    foreach ($menu['#items'] as $menu_item_id => $menu_item) {
      translatable_menu_link_uri_iterate_menu($menu_item);
      $menu['#items'][$menu_item_id] = $menu_item;
    }

    return $this->processMenuRenderArray($menu['#items'] ?? []);
  }

  /**
   * Creates a specific array structure from a given menu render array.
   *
   * @param array $menu_items
   *   The menu links render array built by menu link tree service.
   *
   * @return array
   *   The resulting array.
   */
  private function processMenuRenderArray(array $menu_items): array {
    $menu_links = [];
    if (!empty($menu_items)) {
      foreach ($menu_items as $item) {
        $attributes = $item['attributes'] ?? new Attribute();
        $menu_link_content = $item['original_link'] ?? NULL;
        if ($menu_link_content instanceof MenuLinkContent) {
          $options = $menu_link_content->getOptions();
          $menu_link_attributes = $options['attributes'] ?? [];
          $attributes->merge(new Attribute($menu_link_attributes));
        }
        $menu_links[] = [
          'title' => $item['title'],
          'url' => $item['url']->setAbsolute()->toString(),
          'below' => $this->processMenuRenderArray($item['below']),
          'item_attributes' => $attributes,
        ];
      }
    }
    return $menu_links;
  }

}
