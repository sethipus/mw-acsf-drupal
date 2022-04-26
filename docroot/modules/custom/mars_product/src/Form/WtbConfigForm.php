<?php

namespace Drupal\mars_product\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_product\Plugin\Block\PdpHeroBlock;
use Drupal\mars_common\LanguageHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Where to buy config form class.
 */
class WtbConfigForm extends ConfigFormBase {

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * WtbConfigForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\mars_common\LanguageHelper $language_helper
   *   The language helper service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageHelper $language_helper) {
    parent::__construct($config_factory);
    $this->languageHelper = $language_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('mars_common.language_helper'),
    );
  }

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
        PdpHeroBlock::VENDOR_MANUAL_LINK_SELECTION => $this->t('Manual link selection'),
        PdpHeroBlock::VENDOR_MIK_MAK => $this->t('MikMak'),
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
    // Build MS widget settings fieldset.
    $form['general'][PdpHeroBlock::VENDOR_MANUAL_LINK_SELECTION] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Manual link selection configuration'),
      '#tree' => TRUE,
      '#states' => [
        'visible' => [
          [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_MANUAL_LINK_SELECTION]],
        ],
      ],
    ];
    // Build MK widget settings fieldset.
    $form['general'][PdpHeroBlock::VENDOR_MIK_MAK] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Mik Mak configuration'),
      '#tree' => TRUE,
      '#states' => [
        'visible' => [
          [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_MIK_MAK]],
        ],
      ],
    ];

    $this->buildCommerceVendorProductCardElement($form, PdpHeroBlock::VENDOR_PRICE_SPIDER);
    $this->buildCommerceVendorProductCardElement($form, PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR);
    $this->buildCommerceVendorProductCardElement($form, PdpHeroBlock::VENDOR_SMART_COMMERCE);
    $this->buildCommerceVendorProductCardElement($form, PdpHeroBlock::VENDOR_MANUAL_LINK_SELECTION);
    $this->buildCommerceVendorProductCardElement($form, PdpHeroBlock::VENDOR_MIK_MAK);

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
        $fieldset['option'] = [
          '#type' => 'radios',
          '#title' => $this->t('Where to buy options'),
          '#options' => [
            'default' => $this->t('Price spider modal window (default)'),
            'cta_button' => $this->t('Cta button with product GTIN parameter'),
          ],
          '#default_value' => !empty($config['settings']['option']) ? $config['settings']['option'] : 'default',
          '#states' => [
            'required' => [
              [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_PRICE_SPIDER]],
            ],
          ],
        ];
        $fieldset['price_spider_button_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Button name'),
          '#default_value' => !empty($config['settings']['price_spider_button_name']) ? $this->languageHelper->translate($config['settings']['price_spider_button_name']) : '',
          '#states' => [
            'visible' => [
              [':input[name="price_spider[option]"]' => ['value' => 'cta_button']],
            ],
          ],
        ];
        $fieldset['price_spider_button_url'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Button URL'),
          '#default_value' => !empty($config['settings']['price_spider_button_url']) ? $this->languageHelper->translate($config['settings']['price_spider_button_url']) : '',
          '#description' => $this->languageHelper->translate('Please use relative path like /where-to-buy.'),
          '#states' => [
            'visible' => [
              [':input[name="price_spider[option]"]' => ['value' => 'cta_button']],
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
          '#default_value' => 'my_own',
          '#options' => [
            'my_own' => $this->t('My own button'),
            'commerce_connector' => $this->t('Commerce Connector button'),
          ],
          '#disabled' => TRUE,
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

        $fieldset['add_class'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Add class to the button'),
          '#default_value' => !empty($config['settings']['add_class']) ? $config['settings']['add_class'] : FALSE,
        ];

        $fieldset['button_class'] = [
          '#type' => 'select',
          '#title' => $this->t('Button class'),
          '#default_value' => !empty($config['settings']['button_class']) ? $config['settings']['button_class'] : 'link',
          '#options' => [
            'link' => $this->t('Link'),
            'button' => $this->t('Button'),
          ],
          '#states' => [
            'visible' => [
              [':input[name="' . PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR . '[add_class]"]' => ['checked' => TRUE]],
            ],
          ],
        ];

        $fieldset['data_displaylanguage'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Commerce connector data display language'),
          '#description' => $this->t('Please use this field to specify widget display language once it is different from the site common language. Field value format sample for German sites: <b>de</b>'),
          '#default_value' => !empty($config['settings']['data_displaylanguage']) ? $config['settings']['data_displaylanguage'] : '',
        ];

        $fieldset['hide_size_dropdown'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Hide size dropdown'),
          '#default_value' => !empty($config['settings']['hide_size_dropdown']) ? $config['settings']['hide_size_dropdown'] : FALSE,
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

      case PdpHeroBlock::VENDOR_MANUAL_LINK_SELECTION:
        $fieldset['button_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Button name'),
          '#default_value' => !empty($config['settings']['button_name']) ? $this->languageHelper->translate($config['settings']['button_name']) : '',
          '#states' => [
            'required' => [
              [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_MANUAL_LINK_SELECTION]],
            ],
          ],
        ];
        $fieldset['button_url'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Button URL'),
          '#default_value' => !empty($config['settings']['button_url']) ? $this->languageHelper->translate($config['settings']['button_url']) : '',
          '#states' => [
            'required' => [
              [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_MANUAL_LINK_SELECTION]],
            ],
          ],
        ];
        $fieldset['button_new_tab'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Open in new tab'),
          '#default_value' => $config['settings']['button_new_tab'] ?? TRUE,
        ];
        $fieldset['button_style'] = [
          '#type' => 'select',
          '#title' => $this->t('Button style'),
          '#default_value' => !empty($config['settings']['button_style']) ? $config['settings']['button_style'] : 'link',
          '#options' => [
            'link' => $this->t('Link'),
            'button' => $this->t('Button'),
          ],
        ];
        break;

      case PdpHeroBlock::VENDOR_MIK_MAK:
        $fieldset['widget_id'] = [
          '#type' => 'textfield',
          '#title' => $this->t('MikMak Widget id'),
          '#default_value' => !empty($config['settings']['widget_id']) ? $config['settings']['widget_id'] : '',
          '#states' => [
            'required' => [
              [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_MIK_MAK]],
            ],
          ],
        ];
        $fieldset['button_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Button name'),
          '#default_value' => !empty($config['settings']['button_name']) ? $this->languageHelper->translate($config['settings']['button_name']) : '',
          '#states' => [
            'visible' => [
              [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_MIK_MAK]],
            ],
          ],
        ];
        $fieldset['button_url'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Button URL'),
          '#default_value' => !empty($config['settings']['button_url']) ? $this->languageHelper->translate($config['settings']['button_url']) : '',
          '#description' => $this->languageHelper->translate('Please use relative path like /where-to-buy.'),
          '#states' => [
            'visible' => [
              [':input[name="commerce_vendor"]' => ['value' => PdpHeroBlock::VENDOR_MIK_MAK]],
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
    $ml_config = $this->config('mars_product.wtb.' . PdpHeroBlock::VENDOR_MANUAL_LINK_SELECTION . '.settings');
    $mk_config = $this->config('mars_product.wtb.' . PdpHeroBlock::VENDOR_MIK_MAK . '.settings');

    // Get configuration from the form fields.
    $config->set('commerce_vendor', $form_state->getValue('commerce_vendor'));
    $ps_config->set('settings', $form_state->getValue(PdpHeroBlock::VENDOR_PRICE_SPIDER));
    $cc_config->set('settings', $form_state->getValue(PdpHeroBlock::VENDOR_COMMERCE_CONNECTOR));
    $sc_config->set('settings', $form_state->getValue(PdpHeroBlock::VENDOR_SMART_COMMERCE));
    $ml_config->set('settings', $form_state->getValue(PdpHeroBlock::VENDOR_MANUAL_LINK_SELECTION));
    $mk_config->set('settings', $form_state->getValue(PdpHeroBlock::VENDOR_MIK_MAK));

    // Save the configuration.
    $config->save();
    $ps_config->save();
    $cc_config->save();
    $sc_config->save();
    $ml_config->save();
    $mk_config->save();

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
      'mars_product.wtb.' . PdpHeroBlock::VENDOR_MANUAL_LINK_SELECTION . '.settings',
      'mars_product.wtb.' . PdpHeroBlock::VENDOR_MIK_MAK . '.settings',
    ];
  }

}
