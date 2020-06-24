<?php

namespace Drupal\mars_lighthouse\Element;

use Drupal\Core\Render\Element\Radio;

/**
 * Provides a Lighthouse Gallery Item form element.
 *
 * It will help you to render a radio button widget with preview image.
 *
 * Example of usage:
 *
 * @code
 * $build['my-gallery']['assetId'] = [
 *   '#type' => 'lighthouse_gallery_radio',
 *   '#title' => 'file-name.jpg',
 *   '#uri' => 'uri://domain.example/path/to/picture.extension',
 *   '#return_value' => 'assetId',
 *   '#parents' => ['my-gallery'],
 * ];
 * @endcode
 *
 * @FormElement("lighthouse_gallery_radio")
 */
class LighthouseGalleryRadio extends Radio {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();
    $info['#theme'] = 'lighthouse_gallery_item';
    // We will provide custom wrapping HTML and label.
    // So properties which are related with title and label will not work.
    // @see also mars_lighthouse_preprocess_lighthouse_gallery_item();
    unset($info['#theme_wrappers']);
    return $info;
  }

}
