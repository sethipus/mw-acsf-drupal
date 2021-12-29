<?php

namespace Drupal\mars_entry_gate\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mars_entry_gate\Traits\BlockVisibilityConditionsTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Condition\ConditionManager;

/**
 * Configuration form for Entry Gate.
 */
class EntryGateConfigForm extends ConfigFormBase {

  /**
   * Date format MM DD YYYY.
   */
  const KEY_OPTION_DATE_M_D = 'mm_dd';

  /**
   * Date format DD MM YYYY.
   */
  const KEY_OPTION_DATE_D_M = 'dd_mm';

  /**
   * Date format DD MM YYYY.
   */
  const KEY_OPTION_DATE_M_Y = 'mm_yyyy';

  /**
   * The condition plugin manager service.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionPluginManager;

  use \Drupal\mars_common\Traits\OverrideThemeTextColorTrait;
  use BlockVisibilityConditionsTrait;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Condition\ConditionManager $condition_manager
   *   The condition plugin manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    ConditionManager $condition_manager
  ) {
    parent::__construct($config_factory);
    $this->conditionPluginManager = $condition_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.condition')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entry_gate_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'mars_entry_gate.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('mars_entry_gate.settings');

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $config->get('enabled') ?? FALSE,
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $config->get('title') ?? 'Our Promise',
      '#maxlength' => 55,
      '#required' => TRUE,
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $config->get('description') ?? 'As a responsible manufacturer and in accordance with our marketing code, we have to check your age at this point.',
      '#maxlength' => 150,
      '#required' => TRUE,
    ];

    $form['heading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Heading'),
      '#default_value' => $config->get('heading') ?? 'Please complete your date of birth:',
      '#maxlength' => 45,
      '#required' => TRUE,
    ];

    $form['marketing_message'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Marketing message'),
      '#default_value' => $config->get('marketing_message') ?? '<p>For more information about responsible use of our products, please follow the link to the <a target="_blank" href="https://twix.de/assets/media/Mars-Code.pdf">Mars Marketing Code</a>.</p>',
      '#required' => TRUE,
    ];

    $form['minimum_age'] = [
      '#type' => 'number',
      '#title' => $this->t('Required minimal age'),
      '#default_value' => $config->get('minimum_age') ?? 13,
      '#min' => 1,
      '#max' => 150,
      '#step' => 1,
      '#required' => TRUE,
    ];

    $form['date_format'] = [
      '#title' => $this->t('Format of date'),
      '#type' => 'select',
      '#required' => TRUE,
      '#default_value' => $config->get('date_format') ?? self::KEY_OPTION_DATE_D_M,
      '#options' => [
        self::KEY_OPTION_DATE_D_M => $this->t('DD MM YYYY'),
        self::KEY_OPTION_DATE_M_D => $this->t('MM DD YYYY'),
        self::KEY_OPTION_DATE_M_Y => $this->t('MM YYYY'),
      ],
    ];

    $form['error_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error title'),
      '#default_value' => $config->get('error_title') ?? 'We are sorry',
      '#maxlength' => 25,
      '#required' => TRUE,
    ];

    $form['error_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Error message'),
      '#default_value' => $config->get('error_message') ?? 'Our marketing code states that you are not authorized to access the content you requested. Unfortunately, you cannot view the additional content in this section of the website.',
      '#maxlength' => 180,
      '#required' => TRUE,
    ];

    $form['error_link_1'] = [
      '#type' => 'url',
      '#title' => $this->t('Error link 1 (marketing code)'),
      '#default_value' => $config->get('error_link_1') ?? 'https://twix.de/assets/media/Mars-Code.pdf',
      '#required' => TRUE,
    ];

    $form['error_link_2'] = [
      '#type' => 'url',
      '#title' => $this->t('Error link 2 (imprint)'),
      '#default_value' => $config->get('error_link_2') ?? 'https://deu.mars.com/site-owner',
      '#required' => TRUE,
    ];

    $text_color_config = $config->get('override_text_color') ? ['override_text_color' => $config->get('override_text_color')] : [];
    $this->buildOverrideColorElement($form, $text_color_config);

    $visibility_config = $config->get('visibility') ?? [];
    $this->buildVisibilityInterface($form, $form_state, $visibility_config);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('mars_entry_gate.settings');

    $config->set('enabled', $form_state->getValue('enabled'));
    $config->set('title', $form_state->getValue('title'));
    $config->set('description', $form_state->getValue('description'));
    $config->set('heading', $form_state->getValue('heading'));
    $config->set(
      'marketing_message',
      $form_state->getValue('marketing_message')['value'] ?? NULL
    );
    $config->set('minimum_age', $form_state->getValue('minimum_age'));
    $config->set('date_format', $form_state->getValue('date_format'));
    $config->set('error_title', $form_state->getValue('error_title'));
    $config->set('error_message', $form_state->getValue('error_message'));
    $config->set('error_link_1', $form_state->getValue('error_link_1'));
    $config->set('error_link_2', $form_state->getValue('error_link_2'));
    $config->set('override_text_color', ['override_color' => $form_state->getValue('override_color')]);
    $this->setVisibilityFieldsConfiguration($form_state, $config);
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
