<?php

namespace Drupal\mars_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_product\Plugin\Block\PdpHeroBlock;

/**
 * Where to buy config form class.
 */
class WtbConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wtb_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('mars_product.wtb.settings');
    $saved_vendor = $config->get('commerce_vendor') ?? NULL;
    $submitted_vendor = $form_state->getUserInput()['settings']['commerce_vendor'] ?? NULL;
    $selected_vendor = $submitted_vendor ?? $saved_vendor ?? PdpHeroBlock::VENDOR_PRICE_SPIDER;

    $form['general'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('General configuration'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    $form['general']['commerce_vendor'] = [
      '#type' => 'select',
      '#title' => $this->t('Commerce Vendor'),
      '#default_value' => $selected_vendor ?? PdpHeroBlock::VENDOR_PRICE_SPIDER,
      '#options' => [
        PdpHeroBlock::VENDOR_NONE => $this->t('None'),
        PdpHeroBlock::VENDOR_PRICE_SPIDER => $this->t('Price Spider'),
        PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR => $this->t('Commerce Connector'),
        PdpHeroBlock::VENDOR_SMART_COMMERCE => $this->t('Smart Commerce'),
      ],
      '#required' => TRUE,
    ];
    // Build PS widget settings fieldset.
    $form['general'][PdpHeroBlock::VENDOR_PRICE_SPIDER] = [
      '#type' => 'fieldset',
      '#title' => $this->t('PriceSpider configuration'),
      '#tree' => TRUE,
      '#states' => [
        'visible' => [
          [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_PRICE_SPIDER]],
        ],
      ],
    ];
    // Build CC widget settings fieldset.
    $form['general'][PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Commerce Connector configuration'),
      '#tree' => TRUE,
      '#states' => [
        'visible' => [
          [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];
    // Build SC widget settings fieldset.
    $form['general'][PdpHeroBlock::VENDOR_SMART_COMMERCE] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Smart Commerce configuration'),
      '#tree' => TRUE,
      '#states' => [
        'visible' => [
          [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_SMART_COMMERCE]],
        ],
      ],
    ];

    $this->buildCommerceVendorProductCardElement($form, PdpHeroBlock::VENDOR_PRICE_SPIDER);
    $this->buildCommerceVendorProductCardElement($form, PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR);
    $this->buildCommerceVendorProductCardElement($form, PdpHeroBlock::VENDOR_SMART_COMMERCE);

    return $form;
  }

  /**
   * Builds commerce vendor-specific configuration fields.
   *
   * @param array $form
   *   The given form to update.
   * @param string $widget_id
   *   The widget id.
   */
  protected function buildCommerceVendorProductCardElement(array &$form, $widget_id) {
    $config_entity = $this->config('mars_product.wtb.' . $widget_id . '.settings');
    $config = !empty($config_entity) && !$config_entity->isNew() ? $config_entity->getRawData() : [];
    $fieldset = &$form['general'][$widget_id];

    switch ($widget_id) {
      case PdpHeroBlock::VENDOR_PRICE_SPIDER:
        $fieldset['account_id'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Price Spider Account id'),
          '#default_value' => !empty($config['settings']['account_id']) ? $config['settings']['account_id'] : '',
          '#states' => [
            'required' => [
              [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_PRICE_SPIDER]],
            ],
          ],
        ];

        $fieldset['widget_id'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Widget id'),
          '#default_value' => !empty($config['settings']['widget_id']) ? $config['settings']['widget_id'] : '',
          '#states' => [
            'required' => [
              [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_PRICE_SPIDER]],
            ],
          ],
        ];

        break;

      case PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR:
        $fieldset['widget_id'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Widget id'),
          '#default_value' => !empty($config['settings']['widget_id']) ? $config['settings']['widget_id'] : '',
          '#states' => [
            'required' => [
              [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR]],
            ],
          ],
        ];

        $fieldset['data_token'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Token'),
          '#default_value' => !empty($config['settings']['data_token']) ? $config['settings']['data_token'] : '',
          '#states' => [
            'required' => [
              [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR]],
            ],
          ],
        ];

        $fieldset['data_subid'] = [
          '#type' => 'textfield',
          '#title' => $this->t('SubId'),
          '#default_value' => !empty($config['settings']['data_subid']) ? $config['settings']['data_subid'] : '',
        ];

        $fieldset['cta_title'] = [
          '#type' => 'textfield',
          '#title' => $this->t('CTA title'),
          '#default_value' => !empty($config['settings']['cta_title']) ? $config['settings']['cta_title'] : '',
        ];

        $fieldset['button_type'] = [
          '#type' => 'select',
          '#title' => $this->t('Commerce Connector: button type'),
          '#default_value' => !empty($config['settings']['button_type']) ? $config['settings']['button_type'] : '',
          '#options' => [
            'my_own' => $this->t('My own button'),
            'commerce_connector' => $this->t('Commerce Connector button'),
          ],
        ];

        $fieldset['data_locale'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Commerce connector data locale'),
          '#default_value' => !empty($config['settings']['data_locale']) ? $config['settings']['data_locale'] : '',
          '#states' => [
            'required' => [
              [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR]],
            ],
          ],
        ];

        break;

      case PdpHeroBlock::VENDOR_SMART_COMMERCE:
        $fieldset['carousel_widget_id'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Carousel Widget id'),
          '#default_value' => !empty($config['settings']['carousel_widget_id']) ? $config['settings']['carousel_widget_id'] : '',
          '#states' => [
            'required' => [
              [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_SMART_COMMERCE]],
            ],
          ],
        ];

        $fieldset['button_widget_id'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Smart Button Widget id'),
          '#default_value' => !empty($config['settings']['button_widget_id']) ? $config['settings']['button_widget_id'] : '',
          '#states' => [
            'required' => [
              [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_SMART_COMMERCE]],
            ],
          ],
        ];

        $fieldset['brand_js'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Smart Commerce brand specific JS file URL'),
          '#default_value' => !empty($config['settings']['brand_js']) ? $config['settings']['brand_js'] : '',
          '#states' => [
            'required' => [
              [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_SMART_COMMERCE]],
            ],
          ],
        ];

        $fieldset['brand_css'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Smart Commerce brand specific CSS file URL'),
          '#default_value' => !empty($config['settings']['brand_css']) ? $config['settings']['brand_css'] : '',
          '#states' => [
            'required' => [
              [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_SMART_COMMERCE]],
            ],
          ],
        ];
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Load configuration entities.
    $config = $this->config('mars_product.wtb.settings');
    $ps_config = $this->config('mars_product.wtb.' . PdpHeroBlock::VENDOR_PRICE_SPIDER . '.settings');
    $cc_config = $this->config('mars_product.wtb.' . PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR . '.settings');
    $sc_config = $this->config('mars_product.wtb.' . PdpHeroBlock::VENDOR_SMART_COMMERCE . '.settings');

    // Get configuration from the form fields.
    $config->set('commerce_vendor', $form_state->getValue('commerce_vendor'));
    $ps_config->set('settings', $form_state->getValue(PdpHeroBlock::VENDOR_PRICE_SPIDER));
    $cc_config->set('settings', $form_state->getValue(PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR));
    $sc_config->set('settings', $form_state->getValue(PdpHeroBlock::VENDOR_SMART_COMMERCE));

    // Save the configuration.
    $config->save();
    $ps_config->save();
    $cc_config->save();
    $sc_config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Return the configuration names.
   */
  protected function getEditableConfigNames() {
    return [
      'mars_product.wtb.settings',
      'mars_product.wtb.' . PdpHeroBlock::VENDOR_PRICE_SPIDER . '.settings',
      'mars_product.wtb.' . PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR . '.settings',
      'mars_product.wtb.' . PdpHeroBlock::VENDOR_SMART_COMMERCE . '.settings',
    ];
  }

}
