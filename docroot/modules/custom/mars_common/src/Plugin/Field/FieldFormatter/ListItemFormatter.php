<?php

namespace Drupal\mars_common\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;

/**
 * Plugin implementation of the 'list_item_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "list_item_formatter",
 *   label = @Translation("List item formatter"),
 *   field_types = {
 *     "list_item"
 *   }
 * )
 */
class ListItemFormatter extends FileFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $files = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($files)) {
      return $elements;
    }

    foreach ($files as $delta => $file) {
      $cache_contexts = [];
      /* @var \Drupal\file\Entity\File $file */
      $item_uri = $file->getFileUri();
      // As a work-around, we currently add the 'url.site' cache
      // context to ensure different file URLs are generated for different
      // sites in a multisite setup, including HTTP and HTTPS versions of the
      // same site.
      $url = Url::fromUri(file_create_url($item_uri));
      $cache_contexts[] = 'url.site';

      // Extract field item attributes for the theme function, and unset them
      // from the $item so that the field template does not re-render them.
      /* @var \Drupal\mars_common\Plugin\Field\FieldType\ListItem $item */
      $item = $file->_referringItem;

      $elements[$delta] = [
        '#theme' => 'carousel_item_formatter',
        '#item_url' => $url,
        '#item_description' => $item->get('desc')->getValue(),
        '#cache' => [
          'tags' => $file->getCacheTags(),
          'contexts' => $cache_contexts,
        ],
      ];
    }

    return $elements;
  }

}
