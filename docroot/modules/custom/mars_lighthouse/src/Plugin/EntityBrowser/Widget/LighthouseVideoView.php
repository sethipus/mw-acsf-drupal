<?php

namespace Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Uses a lighthouse requests to provide entity listing in a browser's widget.
 *
 * @EntityBrowserWidget(
 *   id = "lighthouse_video_view",
 *   label = @Translation("Lighthouse Video View"),
 *   description = @Translation("Uses a Lighthouse requests to provide entity
 *   listing in a browser's widget."),
 *   auto_select = TRUE
 * )
 */
class LighthouseVideoView extends LighthouseViewBase implements ContainerFactoryPluginInterface {

  /**
   * Media Type.
   *
   * @var string
   */
  protected $mediaType = 'video';

}
