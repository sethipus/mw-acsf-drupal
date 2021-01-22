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
      ],
      '#required' => TRUE,
    ];

    $form['general']['account_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Price Spider Account id'),
      '#default_value' => $config->get('account_id'),
      '#required' => $selected_vendor === PdpHeroBlock::VENDOR_PRICE_SPIDER,
      '#states' => [
        'visible' => [
          [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_PRICE_SPIDER]],
        ],
        'required' => [
          [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_PRICE_SPIDER]],
        ],
      ],
    ];

    $form['product_card'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Product card configuration'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    $form['product_card']['widget_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Widget id'),
      '#default_value' => $config->get('widget_id'),
      '#required' => TRUE,
    ];

    $form['product_card']['data_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token'),
      '#default_value' => $config->get('data_token'),
      '#states' => [
        'visible' => [
          [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR]],
        ],
        'required' => [
          [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

    $form['product_card']['data_subid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SubId'),
      '#default_value' => $config->get('data_subid'),
      '#states' => [
        'visible' => [
          [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

    $form['product_card']['cta_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA title'),
      '#default_value' => $config->get('cta_title'),
      '#states' => [
        'visible' => [
          [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

    $form['product_card']['button_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Commerce Connector: button type'),
      '#default_value' => $config->get('button_type'),
      '#options' => [
        'my_own' => $this->t('My own button'),
        'commerce_connector' => $this->t('Commerce Connector button'),
      ],
      '#states' => [
        'visible' => [
          [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

    $form['product_card']['data_locale'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Commerce connector data locale'),
      '#default_value' => $config->get('data_locale'),
      '#states' => [
        'visible' => [
          [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR]],
        ],
        'required' => [
          [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('mars_product.wtb.settings');

    $config->set('commerce_vendor', $form_state->getValue('commerce_vendor'));
    $config->set('widget_id', $form_state->getValue('widget_id'));
    $config->set('account_id', $form_state->getValue('account_id'));
    $config->set('data_token', $form_state->getValue('data_token'));
    $config->set('data_subid', $form_state->getValue('data_subid'));
    $config->set('cta_title', $form_state->getValue('cta_title'));
    $config->set('button_type', $form_state->getValue('button_type'));
    $config->set('data_locale', $form_state->getValue('data_locale'));
    // Save the configuration.
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Return the configuration names.
   */
  protected function getEditableConfigNames() {
    return [
      'mars_product.wtb.settings',
    ];
  }

}
