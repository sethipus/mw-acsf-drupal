<?php

namespace Drupal\mars_newsletter\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\webform\Entity\Webform;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a Newsletter Form block.
 *
 * @Block(
 *   id = "newsletter_email_form_block",
 *   admin_label = @Translation("Mars Newsletter Email Signup Form Block"),
 *   category = @Translation("Global elements"),
 * )
 */
class MarsNewsletterEmailSignupBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Contains the configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config;
    $this->entityTypeManager = $entityTypeManager;
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $config['title'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Recaptcha site key.
    $recaptcha_values = $this->configFactory->getEditable('recaptcha.settings');
    $recaptcha_site_key = $recaptcha_values->get('site_key');
    $config_newsletter_bg_color = $this->configFactory->getEditable('emulsifymars.settings');
    $config_newsletter_form_value = $this->configFactory->getEditable('mars_newsletter_form.settings')->get('newsletter.config_form');
    $webform = Webform::load('mars_newsletter_email_form');
    $view_builder = $this->entityTypeManager->getViewBuilder('webform');
    $webform = $view_builder->view($webform);
    $block_label = !empty($config_newsletter_form_value['alert_banner_newsletter_name']) ? $config_newsletter_form_value['alert_banner_newsletter_name'] : $this->t('Sign up for newsletter');
    $override_white_color = !empty($config_newsletter_form_value['override_white_color']) ? $config_newsletter_form_value['override_white_color'] : '';
    if ($override_white_color) {
      $build['#override_white_color'] = '#FFFFFF';
    }
    else {
      $build['#override_white_color'] = '';
    }
    $webform_data = [
      'form' => $webform,
    ];
    $build['#recaptcha_site_key'] = !empty($recaptcha_site_key) ? $recaptcha_site_key : "";
    $build['#webform_newsletter'] = $webform_data;
    $build['#newsletter_toggle'] = $config_newsletter_form_value['newsletter_toggle'] ?? FALSE;
    $build['#webform_block_label'] = $block_label;
    $build['#newsletter_bg_color'] = !empty($config_newsletter_bg_color->get('newsletter_bg_color')) ? $config_newsletter_bg_color->get('newsletter_bg_color') : $config_newsletter_bg_color->get('color_b');
    $build['#theme'] = 'mars_newsletter_email_signup_form';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $formStateValues = $form_state->getValues();
    $this->setConfiguration($formStateValues);
  }

}
