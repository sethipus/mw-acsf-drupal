<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\Traits\OverrideThemeTextColorTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a contact help banner block.
 *
 * @Block(
 *   id = "contact_help_banner_block",
 *   admin_label = @Translation("MARS: Contact Help Banner"),
 *   category = @Translation("Mars Common")
 * )
 */
class ContactHelpBannerBlock extends BlockBase implements ContainerFactoryPluginInterface {
  use OverrideThemeTextColorTrait;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_common.language_helper'),
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('config.factory')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LanguageHelper $language_helper,
    ThemeConfiguratorParser $themeConfiguratorParser,
    ConfigFactoryInterface $config_factory
    ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageHelper = $language_helper;
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();

    $phone_cta_label = '';
    if ($conf['phone_cta_label']) {
      $phone_cta_label = $this->languageHelper->translate($conf['phone_cta_label']);
    }
    elseif ($conf['phone_cta_number']) {
      $phone_cta_label = $this->languageHelper->translate($conf['phone_cta_number']);
    }

    $phone_cta_number = '';
    if ($conf['phone_cta_number']) {
      $phone_cta_number = 'tel:' . $this->languageHelper->translate($conf['phone_cta_number']);
    }

    $email_cta_address = '';
    if ($conf['email_cta_address']) {
      $email_cta_address = 'mailto:' . $this->languageHelper->translate($conf['email_cta_address']);
    }

    $email_cta_label = '';
    if ($conf['email_cta_label']) {
      $email_cta_label = $this->languageHelper->translate($conf['email_cta_label']);
    }
    elseif ($conf['email_cta_address']) {
      $email_cta_label = $this->languageHelper->translate($conf['email_cta_address']);
    }

    $build['#title'] = $this->languageHelper->translate($conf['title'] ?? '');
    $build['#description'] = $this->languageHelper->translate($conf['description'] ?? '');
    $build['#social_links_label'] = $this->languageHelper->translate($conf['social_links_label'] ?? '');
    $build['#phone_cta_label'] = $phone_cta_label;
    $build['#phone_cta_link'] = $phone_cta_number;
    $build['#email_cta_label'] = $email_cta_label;
    $build['#email_cta_link'] = $email_cta_address;
    $build['#help_and_contact_cta_label'] = $this->languageHelper->translate($conf['help_and_contact_cta_label'] ?? '');
    $build['#help_and_contact_cta_url'] = $conf['help_and_contact_cta_url'] ?? '';

    $build['#social_menu_items'] = $this->themeConfiguratorParser->socialLinks();
    $build['#brand_shape'] = $this->themeConfiguratorParser->getBrandShapeWithoutFill();
    $build['#theme'] = 'contact_help_banner_block';
    $text_color_override = FALSE;
    if (!empty($this->configuration['override_text_color']['override_color'])) {
      $text_color_override = static::$overrideColor;
    }
    $build['#text_color_override'] = $text_color_override;

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $conf = $this->getConfiguration();

    return [
      'label_display' => FALSE,
      'email_cta_label' => $conf['email_cta_label'] ?? $this->t('Email Us'),
      'social_links_label' => $conf['social_links_label'] ?? $this->t('See More On'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $character_limit_config = $this->configFactory->get('mars_common.character_limit_page');

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => !empty($character_limit_config->get('contact_help_banner_title')) ? $character_limit_config->get('contact_help_banner_title') : 55,
      '#default_value' => $this->configuration['title'] ?? '',
      '#required' => TRUE,
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#maxlength' => !empty($character_limit_config->get('contact_help_banner_description')) ? $character_limit_config->get('contact_help_banner_description') : 255,
      '#default_value' => $this->configuration['description'] ?? '',
      '#required' => FALSE,
    ];

    $form['phone_cta'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Phone Contact'),
      'label' => [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#default_value' => $this->configuration['phone_cta_label'] ?? '',
        '#required' => FALSE,
      ],
      'number' => [
        '#type' => 'textfield',
        '#title' => $this->t('Phone Number'),
        '#default_value' => $this->configuration['phone_cta_number'] ?? '',
        '#placeholder' => $this->t('222-555-1616'),
        '#required' => FALSE,
      ],
    ];

    $form['email_cta'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('E-mail Contact'),
      'label' => [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#default_value' => $this->configuration['email_cta_label'] ?? '',
        '#required' => FALSE,
      ],
      'address' => [
        '#type' => 'textfield',
        '#title' => $this->t('E-mail address'),
        '#default_value' => $this->configuration['email_cta_address'] ?? '',
        '#placeholder' => $this->t('contact@mars.com'),
        '#required' => FALSE,
      ],
    ];

    $form['help_and_contact_cta'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Help & Contact CTA'),
      'label' => [
        '#type' => 'textfield',
        '#title' => $this->t('Button Label'),
        '#default_value' => $this->configuration['help_and_contact_cta_label'] ?? '',
        '#required' => FALSE,
      ],
      'url' => [
        '#type' => 'textfield',
        '#title' => $this->t('Page URL'),
        '#default_value' => $this->configuration['help_and_contact_cta_url'] ?? '',
        '#required' => FALSE,
      ],
    ];

    $form['social_links_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Social Links label'),
      '#maxlength' => !empty($character_limit_config->get('contact_help_banner_social_link_label')) ? $character_limit_config->get('contact_help_banner_social_link_label') : 35,
      '#default_value' => $this->configuration['social_links_label'] ?? '',
      '#required' => TRUE,
    ];
    $this->buildOverrideColorElement($form, $this->configuration);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->configuration['title'] = $form_state->getValue('title');
    $this->configuration['description'] = $form_state->getValue('description');
    $this->configuration['social_links_label'] = $form_state->getValue('social_links_label');

    $this->configuration['phone_cta_label'] = $form_state->getValue('phone_cta')['label'];
    $this->configuration['phone_cta_number'] = $form_state->getValue('phone_cta')['number'];

    $this->configuration['email_cta_label'] = $form_state->getValue('email_cta')['label'];
    $this->configuration['email_cta_address'] = $form_state->getValue('email_cta')['address'];

    $this->configuration['help_and_contact_cta_label'] = $form_state->getValue('help_and_contact_cta')['label'];
    $this->configuration['help_and_contact_cta_url'] = $form_state->getValue('help_and_contact_cta')['url'];
    $this->configuration['override_text_color'] = $form_state->getValue('override_text_color');
  }

}
