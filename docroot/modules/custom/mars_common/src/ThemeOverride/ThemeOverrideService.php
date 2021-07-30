<?php

namespace Drupal\mars_common\ThemeOverride;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;

/**
 * Service for figuring out the current theme override.
 */
class ThemeOverrideService {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $currentRoute;

  /**
   * ThemeConfiguratorOverrideExtractor constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route
   *   The current route.
   */
  public function __construct(RouteMatchInterface $current_route) {
    $this->currentRoute = $current_route;
  }

  /**
   * Returns the current theme config override values.
   *
   * @return ThemeOverride
   *   The current override values.
   */
  public function getCurrentOverride(): ThemeOverride {
    $node = $this->getCurrentCampaignNode();
    if ($node) {
      return ThemeOverride::createFromNode($node);
    }
    return ThemeOverride::createEmpty();
  }

  /**
   * Extracts the campaign node from url.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The current campaign node.
   */
  private function getCurrentCampaignNode(): ?NodeInterface {
    /** @var \Drupal\node\NodeInterface|null $node */
    $node = $this->currentRoute->getParameter('node');
    if ($node instanceof NodeInterface && $node->bundle() === 'campaign') {
      return $node;
    }
    return NULL;
  }

}
