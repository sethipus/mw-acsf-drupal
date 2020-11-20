<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mars_common\ThemeConfiguratorParser;

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

  /**
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mars_common.theme_configurator_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ThemeConfiguratorParser $themeConfiguratorParser
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->themeConfiguratorParser = $themeConfiguratorParser;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();

    $phone_cta_label = '';
    if ($conf['phone_cta_label']) {
      $phone_cta_label = $conf['phone_cta_label'];
    }
    elseif ($conf['phone_cta_number']) {
      $phone_cta_label = $conf['phone_cta_number'];
    }

    $phone_cta_number = '';
    if ($conf['phone_cta_number']) {
      $phone_cta_number = 'tel:' . $conf['phone_cta_number'];
    }

    $email_cta_address = '';
    if ($conf['email_cta_address']) {
      $email_cta_address = 'mailto:' . $conf['email_cta_address'];
    }

    $email_cta_label = '';
    if ($conf['email_cta_label']) {
      $email_cta_label = $conf['email_cta_label'];
    }
    elseif ($conf['email_cta_address']) {
      $email_cta_label = $conf['email_cta_address'];
    }

    $build['#title'] = $conf['title'] ?? '';
    $build['#description'] = $conf['description'] ?? '';
    $build['#social_links_label'] = $conf['social_links_label'] ?? '';
    $build['#phone_cta_label'] = $phone_cta_label;
    $build['#phone_cta_link'] = $phone_cta_number;
    $build['#email_cta_label'] = $email_cta_label;
    $build['#email_cta_link'] = $email_cta_address;
    $build['#help_and_contact_cta_label'] = $conf['help_and_contact_cta_label'] ?? '';
    $build['#help_and_contact_cta_url'] = $conf['help_and_contact_cta_url'] ?? '';

    $build['#social_menu_items'] = $this->themeConfiguratorParser->socialLinks();
    $build['#brand_shape'] = $this->themeConfiguratorParser->getBrandShape();
    $build['#theme'] = 'contact_help_banner_block';

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

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 55,
      '#default_value' => $this->configuration['title'] ?? '',
      '#required' => TRUE,
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#maxlength' => 160,
      '#default_value' => $this->configuration['description'] ?? '',
      '#required' => FALSE,
    ];

    $form['phone_cta'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Phone Contact'),
      'label' => [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#maxlength' => 20,
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
        '#maxlength' => 20,
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
        '#maxlength' => 15,
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
      '#maxlength' => 35,
      '#default_value' => $this->configuration['social_links_label'] ?? '',
      '#required' => TRUE,
    ];

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
  }

}
