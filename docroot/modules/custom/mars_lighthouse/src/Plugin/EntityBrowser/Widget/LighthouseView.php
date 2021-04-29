<?php

namespace Drupal\mars_lighthouse\Plugin\EntityBrowser\Widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_lighthouse\Client\LighthouseDefaultsProvider;
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
  public function getForm(array &$original_form, FormStateInterface $form_state, array $additional_widget_parameters) {
    $form = parent::getForm($original_form, $form_state, $additional_widget_parameters);
    // Disable caching on this form.
    $form_state->setCached(FALSE);

    $config = $this->configFactory->get('mars_lighthouse.settings');

    // Get filter values.
    $text_value = $form_state->getValue('text') ?? $this->currentRequest->query->get('text') ?? '';
    $brand_value = $form_state->getValue('brand') ?? $this->currentRequest->query->get('brand') ?? '';
    $market_value = $form_state->getValue('market') ?? $this->currentRequest->query->get('market') ?? '';
    $transparent_value = $form_state->getValue('transparent') ?? $this->currentRequest->query->get('transparent') ?? '';
    // Get filter options.
    try {
      $brand_options = $this->lighthouseAdapter->getBrands();
      $market_options = $this->lighthouseAdapter->getMarkets();
    }
    catch (LighthouseException $e) {
      $brand_options = $market_options = [];
    }

    $form['#attached']['library'] = [
      'mars_lighthouse/lighthouse-gallery',
    ];

    $form['filter']['text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text'),
      '#size' => 60,
      '#default_value' => $text_value,
      '#weight' => 1,
    ];
    $form_state->setValue('text', $text_value);

    $form['filter']['brand'] = [
      '#type' => 'select',
      '#title' => $this->t('Brand'),
      '#options' => $brand_options,
      '#default_value' => $brand_value,
      '#weight' => 2,
    ];
    $form['filter']['market'] = [
      '#type' => 'select',
      '#title' => $this->t('Market'),
      '#options' => $market_options,
      '#default_value' => $market_value,
      '#weight' => 3,
    ];

    if ($config->get('api_version') == LighthouseDefaultsProvider::API_KEY_VERSION_2) {
      $form['filter']['transparent'] = [
        '#type' => 'select',
        '#title' => $this->t('Is transparent'),
        '#options' => [
          '' => $this->t('-- Any --'),
          'Yes' => $this->t('Yes'),
        ],
        '#default_value' => $transparent_value,
        '#weight' => 4,
      ];
    }

    $form['filter']['is_submitted'] = [
      '#type' => 'hidden',
      '#value' => 1,
    ];

    $form['filter']['submit'] = [
      '#type' => 'submit',
      '#submit' => [[$this, 'searchCallback']],
      '#value' => $this->t('Filter'),
      '#weight' => 5,
    ];

    $total_found = 0;
    if ($form_state->getValue('is_submitted')) {
      $this->currentRequest->query->set('page', 0);
    }
    $form['view'] = $this->getView($total_found, [
      'text' => $text_value,
      'brand' => $brand_value,
      'market' => $market_value,
      'transparent' => $transparent_value,
    ]);

    if ($total_found) {
      $this->pageManager->createPager($total_found, self::PAGE_LIMIT);
      $form['pagination'] = [
        '#type' => 'pager',
        '#quantity' => 3,
        '#parameters' => [
          'text' => $text_value,
          'brand' => $brand_value,
          'market' => $market_value,
        ],
      ];
    }

    return $form;
  }

}
