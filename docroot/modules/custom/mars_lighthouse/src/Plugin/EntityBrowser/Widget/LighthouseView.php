<?php

namespace Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_lighthouse\LighthouseException;

/**
 * Uses a lighthouse requests to provide entity listing in a browser's widget.
 *
 * @EntityBrowserWidget(
 *   id = "lighthouse_view",
 *   label = @Translation("Lighthouse View"),
 *   description = @Translation("Uses a lighthouse requests to provide entity
 *   listing in a browser's widget."),
 *   auto_select = TRUE
 * )
 */
class LighthouseView extends LighthouseViewBase implements ContainerFactoryPluginInterface {

  /**
   * Media Type.
   *
   * @var string
   */
  protected $mediaType = 'image';

  /**
   * {@inheritdoc}
   */
  protected function getView(&$total_found, FormStateInterface $form_state) {
    $view = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#attributes' => [
        // We need this ID in order to complete AJAX request.
        'id' => 'lighthouse-gallery',
        // This class was added for styling purposes.
        'class' => ['lighthouse-gallery', 'clearfix'],
      ],
    ];

    // Get data from API.
    try {
      $text = $form_state->getValue('text');
      $filters = [
        'brand' => $form_state->getValue('brand'),
        'market' => $form_state->getValue('market'),
      ];
      $page = $this->currentRequest->query->get('page') ?? 0;
      $data = $this->lighthouseAdapter->getMediaDataList(
        $total_found,
        $text,
        $filters,
        [],
        $page * self::PAGE_LIMIT,
        self::PAGE_LIMIT,
        $this->mediaType
      );
    }
    catch (LighthouseException $e) {
      $view['markup'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $e->getMessage(),
        '#attributes' => [
          'class' => ['lighthouse-gallery__no-results'],
        ],
      ];
      return $view;
    }

    // Prepare data to render.
    if (!empty($data)) {
      foreach ($data as $item) {
        // Adds a checkbox for each image.
        $view[$item['assetId']] = [
          /* '#type' => 'lighthouse_gallery_radio', */
          '#type' => 'lighthouse_gallery_checkbox',
          '#title' => $item['name'],
          '#uri' => $item['uri'],
          /* '#return_value' => $item['assetId'],
             '#parents' => ['view'], */
        ];
      }
    }
    // Empty text.
    else {
      $view['markup'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('There are no results for this search.'),
        '#attributes' => [
          'class' => ['lighthouse-gallery__no-results'],
        ],
      ];
    }

    return $view;
  }

}
