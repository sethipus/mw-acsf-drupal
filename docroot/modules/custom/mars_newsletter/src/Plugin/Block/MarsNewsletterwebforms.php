<?php

namespace Drupal\mars_newsletter\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\webform\Utility\WebformYaml;
use Drupal\webform\WebformInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mars_common\Traits\OverrideThemeTextColorTrait;
use Drupal\mars_common\ThemeConfiguratorParser;

/**
 * Provides a 'Webform' block.
 *
 * @Block(
 *   id = "mars_newsletter_webform_block",
 *   admin_label = @Translation("Mars newsletter forms"),
 *   category = @Translation("Mars Forms")
 * )
 */
class MarsNewsletterwebforms extends BlockBase implements ContainerFactoryPluginInterface {

  use OverrideThemeTextColorTrait;

  /**
   * Key override background color.
   */
  const KEY_OPTION_DEFAULT = 'default';

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Entity type manager.
   *
   * @var \Drupal\core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The webform token manager.
   *
   * @var \Drupal\webform\WebformTokenManagerInterface
   */
  protected $tokenManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->requestStack = $container->get('request_stack');
    $instance->routeMatch = $container->get('current_route_match');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->tokenManager = $container->get('webform.token_manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'webform_id' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $wrapper_format = $this->requestStack->getCurrentRequest()
      ->get(MainContentViewSubscriber::WRAPPER_FORMAT);
    $is_off_canvas = in_array($wrapper_format, ['drupal_dialog.off_canvas']);
    $config = $this->getConfiguration();
    $form['#attributes'] = ['class' => ['webform-block-settings-tray-form']];
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $config['title'] ?? '',
    ];
    $form['webform_id'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Webform'),
      '#description' => $this->t('Select the webform that you would like to display in this block.'),
      '#target_type' => 'webform',
      '#required' => TRUE,
      '#default_value' => $this->getWebform(),
    ];
    $this->buildOverrideColorElement($form, $config);
    // Override title font.
    $form['title_font'] = [
      '#title' => t('Select override title font'),
      '#type' => 'select',
      '#options' => ['title_heading_font' => $this->t('Heading font'), 'title_primary_font' => $this->t('Primary font'), 'title_secondary_font' => $this->t('Secondary font')],
      '#default_value' => !empty($config['title_font']) ? $config['title_font'] : 'title_heading_font',
    ];
    // Override field title font.
    $form['field_title_font'] = [
      '#title' => t('Select override field title font'),
      '#type' => 'select',
      '#options' => ['field_title_heading_font' => $this->t('Heading font'), 'field_title_primary_font' => $this->t('Primary font'), 'field_title_secondary_font' => $this->t('Secondary font')],
      '#default_value' => !empty($config['field_title_font']) ? $config['field_title_font'] : 'field_title_primary_font',
    ];
    $form['use_background_color'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Background Color Override'),
      '#default_value' => $config['use_background_color'] ?? FALSE,
      '#states' => [
        'visible' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_DEFAULT]],
        ],
      ],
    ];
    $form['background_color'] = [
      '#type' => 'jquery_colorpicker',
      '#title' => $this->t('Background Color Override'),
      '#default_value' => $config['background_color'] ?? '',
      '#states' => [
        'visible' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_DEFAULT]],
          'and',
          [':input[name="settings[use_background_color]"]' => ['checked' => TRUE]],
        ],
      ],
    ];
    $form['use_button_background_color'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Submit Button Background Color Override'),
      '#default_value' => $config['use_button_background_color'] ?? FALSE,
      '#states' => [
        'visible' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_DEFAULT]],
        ],
      ],
    ];
    $form['button_background_color'] = [
      '#type' => 'jquery_colorpicker',
      '#title' => $this->t('Submit Button Background Color Override'),
      '#default_value' => $config['button_background_color'] ?? '',
      '#states' => [
        'visible' => [
          [':input[name="settings[block_type]"]' => ['value' => self::KEY_OPTION_DEFAULT]],
          'and',
          [':input[name="settings[use_button_background_color]"]' => ['checked' => TRUE]],
        ],
      ],
    ];
    
    $this->tokenManager->elementValidate($form);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['webform_id'] = $values['webform_id'];
    $this->configuration['title'] = $values['title'];
    $this->configuration['use_background_color'] = $values['use_background_color'];
    $this->configuration['background_color'] = $values['background_color'];
    $this->configuration['use_button_background_color'] = $values['use_button_background_color'];
    $this->configuration['button_background_color'] = $values['button_background_color'];
    $this->configuration['override_text_color']['override_color'] = $values['override_text_color']['override_color'];
    $this->configuration['title_font'] = $values['title_font'];
    $this->configuration['field_title_font'] = $values['field_title_font'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $webform = $this->getWebform();
    $view_builder = $this->entityTypeManager->getViewBuilder('webform');
    $webform_value = $view_builder->view($webform);
    $config = $this->getConfiguration();
    if (!$webform) {
      if (strpos($this->routeMatch->getRouteName(), 'layout_builder.') === 0) {
        return ['#markup' => $this->t('The webform (@webform) is broken or missing.', ['@webform' => $this->configuration['webform_id']])];
      }
      else {
        return [];
      }
    }

    $webform_data = [
      'form' => $webform_value,
    ];
    $text_color_override = FALSE;
    if (!empty($config['override_text_color']['override_color'])) {
      $text_color_override = static::$overrideColor;
    }
    $background_color = !empty($config['use_background_color']) && !empty($config['background_color']) ? $config['background_color'] : '';
    $button_background_color = !empty($config['use_button_background_color']) && !empty($config['button_background_color']) ? $config['button_background_color'] : '';
    $build['#button_background_color'] = $button_background_color;
    $build['#background_color'] = $background_color;
    $build['#text_color_override'] = $text_color_override;
    $build['#webform_newsletter'] = $webform_data;
    $build['#webform_block_label'] = $config['title'] ?? $this->t('SIGN UP');
    $build['#title_font'] = !empty($config['title_font']) ? $config['title_font'] : 'title_heading_font';
    $build['#field_title_font'] = !empty($config['field_title_font']) ? $config['field_title_font'] : 'field_title_primary_font';
    $build['#theme'] = 'mars_newsletter_signup_form';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $webform = $this->getWebform();
    if (!$webform) {
      return AccessResult::forbidden();
    }

    $access_result = $webform->access('submission_create', $account, TRUE);
    if ($access_result->isAllowed()) {
      return $access_result;
    }

    $has_access_denied_message = ($webform->getSetting('form_access_denied') !== WebformInterface::ACCESS_DENIED_DEFAULT);
    return AccessResult::allowedIf($has_access_denied_message)
      ->addCacheableDependency($access_result);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();

    if ($webform = $this->getWebform()) {
      $dependencies[$webform->getConfigDependencyKey()][] = $webform->getConfigDependencyName();
    }

    return $dependencies;
  }

  /**
   * Get this block instance webform.
   *
   * @return \Drupal\webform\WebformInterface
   *   A webform or NULL.
   */
  protected function getWebform() {
    return $this->entityTypeManager->getStorage('webform')->load($this->configuration['webform_id']);
  }
}

