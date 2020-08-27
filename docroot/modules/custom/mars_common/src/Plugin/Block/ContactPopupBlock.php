<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\SocialLinks;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a contact popup block.
 *
 * @Block(
 *   id = "contact_popup_block",
 *   admin_label = @Translation("MARS: Contact Popup Block"),
 *   category = @Translation("Mars Common")
 * )
 */
class ContactPopupBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Media storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * Media storage.
   *
   * @var \Drupal\mars_common\SocialLinks
   */
  protected $socialLinks;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('mars_common.social_links')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    SocialLinks $social_links
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->socialLinks = $social_links;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();

    $build['#label'] = $conf['label'] ?? '';
    $build['#description'] = $conf['description'] ?? '';
    $build['#social_links_label'] = $conf['social_links_label'] ?? '';
    $build['#phone_cta_label'] = $conf['phone_cta_label'] ?? '';
    $build['#phone_cta_number'] = $conf['phone_cta_number'] ?? '';
    $build['#email_cta_label'] = $conf['email_cta_label'] ?? '';
    $build['#email_cta_address'] = $conf['email_cta_address'] ?? '';
    $build['#help_and_contact_cta_label'] = $conf['help_and_contact_cta_label'] ?? '';
    $build['#help_and_contact_cta_url'] = $conf['help_and_contact_cta_url'] ?? '';

    $build['#social_menu_items'] = $this->socialLinks->getRenderedItems();
    $build['#theme'] = 'contact_popup_block';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $conf = $this->getConfiguration();

    return [
      'help_and_contact_cta_label' => $conf['help_and_contact_cta_label'] ?? $this->t('Help & Contact'),
      'social_links_label' => $conf['social_links_label'] ?? $this->t('See More On'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 45,
      '#default_value' => $this->configuration['label'] ?? '',
      '#required' => TRUE,
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#maxlength' => 150,
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
        '#placeholder' => 'Phone number',
        '#required' => FALSE,
      ],
      'number' => [
        '#type' => 'textfield',
        '#title' => $this->t('Phone Number'),
        '#default_value' => $this->configuration['phone_cta_number'] ?? '',
        '#placeholder' => '222-555-1616',
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
        '#placeholder' => 'Email Us',
        '#required' => FALSE,
      ],
      'address' => [
        '#type' => 'textfield',
        '#title' => $this->t('E-mail address'),
        '#default_value' => $this->configuration['email_cta_address'] ?? '',
        '#placeholder' => 'contact@mars.com',
        '#required' => FALSE,
      ],
    ];

    $form['social_links_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Social Links label'),
      '#maxlength' => 35,
      '#default_value' => $this->configuration['social_links_label'] ?? '',
      '#required' => FALSE,
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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->configuration['label'] = $form_state->getValue('label');
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
