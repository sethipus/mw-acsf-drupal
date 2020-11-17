<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Where To Buy block.
 *
 * @Block(
 *   id = "where_to_buy_block",
 *   admin_label = @Translation("MARS: Where To Buy"),
 *   category = @Translation("Mars Common")
 * )
 */
class WhereToBuyBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Price spider id.
   */
  const VENDOR_PRICE_SPIDER = 'price_spider';

  /**
   * Commerce connector id.
   */
  const VENDOR_COMMERCE_CONNECTOR = 'commerce_connector';

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    LanguageManagerInterface $language_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $config_factory;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['commerce_vendor'] = [
      '#type' => 'select',
      '#title' => $this->t('Commerce Vendor'),
      '#default_value' => $this->configuration['commerce_vendor'],
      '#options' => [
        self::VENDOR_PRICE_SPIDER => $this->t('Price Spider'),
        self::VENDOR_COMMERCE_CONNECTOR => $this->t('Commerce Connector'),
      ],
      '#required' => TRUE,
    ];

    $form['widget_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Widget id'),
      '#default_value' => $this->configuration['widget_id'],
      '#required' => TRUE,
    ];

    $form['data_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token'),
      '#default_value' => $this->configuration['data_token'],
      '#states' => [
        'visible' => [
          [':input[name="settings[commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
        'required' => [
          [':input[name="settings[commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

    $form['data_subid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SubId'),
      '#default_value' => $this->configuration['data_subid'],
      '#states' => [
        'visible' => [
          [':input[name="settings[commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

    $form['product_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product ID'),
      '#default_value' => $this->configuration['product_id'],
      'visible' => [
        [':input[name="settings[commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
      ],
    ];

    $form['cta_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA title'),
      '#default_value' => $this->configuration['cta_title'],
      'visible' => [
        [':input[name="settings[commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
      ],
    ];

    $form['button_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Commerce Connector: button type'),
      '#default_value' => $this->configuration['button_type'],
      '#options' => [
        'my_own' => $this->t('My own button'),
        'commerce_connector' => $this->t('Commerce Connector button'),
      ],
      '#states' => [
        'visible' => [
          [':input[name="settings[commerce_vendor]"]' => ['value' => self::VENDOR_COMMERCE_CONNECTOR]],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * This method processes the blockForm() form fields when the block
   * configuration form is submitted.
   *
   * The blockValidate() method can be used to validate the form submission.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $config = $this->getConfiguration();
    return [
      'commerce_vendor' => $config['commerce_vendor'] ?? '',
      'widget_id' => $config['widget_id'] ?? '',
      'data_token' => $config['data_token'] ?? '',
      'data_subid' => $config['data_subid'] ?? '',
      'cta_title' => $config['cta_title'] ?? '',
      'product_id' => $config['product_id'] ?? '',
      'button_type' => $config['button_type'] ?? '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#theme'] = 'where_to_buy_block';
    $this->pageAttachments($build);

    $build['#commerce_vendor'] = $this->configuration['commerce_vendor'];
    $build['#product_id'] = $this->configuration['product_id'];
    $build['#cta_title'] = $this->configuration['cta_title'];
    $build['#button_type'] = $this->configuration['button_type'];
    $build['#widget_id'] = $this->configuration['widget_id'];
    $build['#data_subid'] = $this->configuration['data_subid'];
    $build['#data_token'] = $this->configuration['data_token'];

    $locale = $this->languageManager->getCurrentLanguage()->getId();
    $country = $this->config->get('system.date')
      ->get('country.default');
    $build['#data_locale'] = $locale . '-' . $country;
    $build['#data_displaylanguage'] = $locale;

    return $build;
  }

  /**
   * Add page attachments.
   *
   * @param array $build
   *   Build array.
   *
   * @return array
   *   Return build.
   */
  public function pageAttachments(array &$build) {
    if ($this->configuration['commerce_vendor'] == self::VENDOR_PRICE_SPIDER) {
      $metatags = [
        'ps-key' => [
          '#tag' => 'meta',
          '#attributes' => [
            'name' => 'ps-key',
            'content' => $this->configuration['widget_id'],
          ],
        ],
        'ps-country' => [
          '#tag' => 'meta',
          '#attributes' => [
            'name' => 'ps-country',
            'content' => $this->config->get('system.date')
              ->get('country.default'),
          ],
        ],
        'ps-language' => [
          '#tag' => 'meta',
          '#attributes' => [
            'name' => 'ps-language',
            'content' => strtolower($this->languageManager->getCurrentLanguage()
              ->getId()),
          ],
        ],
        'price-spider' => [
          '#tag' => 'script',
          '#attributes' => [
            'src' => '//cdn.pricespider.com/1/lib/ps-widget.js',
            'async' => TRUE,
          ],
        ],
      ];
      foreach ($metatags as $key => $metatag) {
        $build['#attached']['html_head'][] = [$metatag, $key];
      }
    }
    return $build;
  }

}
