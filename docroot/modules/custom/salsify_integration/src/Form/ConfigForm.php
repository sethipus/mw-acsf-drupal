<?php

namespace Drupal\salsify_integration\Form;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\salsify_integration\Event\SalsifyGetEntityTypesEvent;
use Drupal\salsify_integration\Salsify;
use Drupal\salsify_integration\SalsifyFields;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Salsify Configuration form class.
 */
class ConfigForm extends ConfigFormBase {

  /**
   * The entity type manager interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The event dispatcher service.
   *
   * @var \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  protected $eventDispatcher;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The Salsify fields module.
   *
   * @var \Drupal\salsify_integration\SalsifyFields
   */
  protected $salsifyFields;

  /**
   * ConfigForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager interface.
   * @param \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher $event_dispatcher
   *   The event dispatcher service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\salsify_integration\SalsifyFields $salsify_fields
   *   The Salsify fields module.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    ContainerAwareEventDispatcher $event_dispatcher,
    ModuleHandlerInterface $module_handler,
    SalsifyFields $salsify_fields
  ) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->eventDispatcher = $event_dispatcher;
    $this->moduleHandler = $module_handler;
    $this->salsifyFields = $salsify_fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('event_dispatcher'),
      $container->get('module_handler'),
      $container->get('salsify_integration.salsify_fields')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'salsify_integration_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('salsify_integration.settings');

    $form['salsify_api_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Salsify API Settings'),
      '#collapsible' => TRUE,
      '#group' => 'salsify_api_settings_group',
    ];

    $form['salsify_api_settings']['product_feed_url'] = [
      '#type' => 'url',
      '#size' => 75,
      '#title' => $this->t('Salsify Product Feed'),
      '#default_value' => $config->get('product_feed_url'),
      '#description' => $this->t('The link to the product feed from a Salsify channel. For details on channels in Salsify, see <a href="@url" target="_blank">Salsify\'s documentation</a>', ['@url' => 'https://help.salsify.com/help/getting-started-with-channels']),
      '#required' => TRUE,
    ];

    $form['salsify_api_settings']['auth_method'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a bundle'),
      '#options' => [
        Salsify::AUTH_METHOD_TOKEN => $this->t('Access token'),
        Salsify::AUTH_METHOD_SECRET => $this->t('Client id and secret'),
      ],
      '#default_value' => $config->get('auth_method'),
      '#description' => $this->t('Please choose auth api method.'),
      '#required' => TRUE,
    ];

    $form['salsify_api_settings']['access_token'] = [
      '#type' => 'textfield',
      '#size' => 75,
      '#title' => $this->t('Salsify Access Token'),
      '#default_value' => $config->get('access_token'),
      '#description' => $this->t('The access token from the Salsify user account to use for this integration. For details on where to find the access token, see <a href="@url" target="_blank">Salsify\'s API documentation</a>', ['@url' => 'https://help.salsify.com/help/getting-started-api-authorization']),
      '#states' => [
        'visible' => [
          ':input[name="auth_method"]' => ['value' => Salsify::AUTH_METHOD_TOKEN],
        ],
        'required' => [
          ':input[name="auth_method"]' => ['value' => Salsify::AUTH_METHOD_TOKEN],
        ],
      ],
    ];

    $form['salsify_api_settings']['client_id'] = [
      '#type' => 'textfield',
      '#size' => 75,
      '#title' => $this->t('Client id'),
      '#default_value' => $config->get('client_id'),
      '#description' => $this->t('The client id from the Salsify user account to use for this integration.'),
      '#states' => [
        'visible' => [
          ':input[name="auth_method"]' => ['value' => Salsify::AUTH_METHOD_SECRET],
        ],
        'required' => [
          ':input[name="auth_method"]' => ['value' => Salsify::AUTH_METHOD_SECRET],
        ],
      ],
    ];

    $form['salsify_api_settings']['client_secret'] = [
      '#type' => 'textfield',
      '#size' => 75,
      '#title' => $this->t('Client secret'),
      '#default_value' => $config->get('client_secret'),
      '#description' => $this->t('The client secret from the Salsify user account to use for this integration.'),
      '#states' => [
        'visible' => [
          ':input[name="auth_method"]' => ['value' => 'client_secret'],
        ],
        'required' => [
          ':input[name="auth_method"]' => ['value' => 'client_secret'],
        ],
      ],
    ];

    // By default, this module will support the core node and taxonomy term
    // entities. More can be added by subscribing to the provided event.
    $entity_type_options = [
      'node' => $this->t('Node'),
      'taxonomy_term' => $this->t('Taxonomy Term'),
    ];

    // Dispatch the event to allow other modules to add on to the content
    // options list.
    $event = new SalsifyGetEntityTypesEvent($entity_type_options);
    $this->eventDispatcher->dispatch(SalsifyGetEntityTypesEvent::GET_TYPES, $event);
    // Get the updated entity type list from from the event.
    $entity_type_options = $event->getEntityTypesList();

    $form['salsify_api_settings']['setup_types'] = [
      '#type' => 'container',
      '#prefix' => '<div class="salsify-config-entity-types">',
      '#suffix' => '</div>',
    ];

    $form['salsify_api_settings']['setup_types']['entity_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Select an entity type'),
      '#options' => $entity_type_options,
      '#default_value' => $config->get('entity_type'),
      '#description' => $this->t('The entity type to use for product mapping from Salsify.'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::loadEntityBundles',
        'trigger' => 'change',
        'wrapper' => 'salsify-config-entity-types',
      ],
      '#cache' => [
        'tags' => [
          'salsify_config',
        ],
      ],
    ];

    if ($form_state->getValue('entity_type') || $config->get('entity_type')) {
      $entity_type = $form_state->getValue('entity_type') ? $form_state->getValue('entity_type') : $config->get('entity_type');
      // Load the entity type definition to get the bundle type name.
      $entity_type_def = $this->entityTypeManager->getDefinition($entity_type);
      $entity_bundles = $this->entityTypeManager->getStorage($entity_type_def->getBundleEntityType())->loadMultiple();
      $entity_bundles_options = [];
      foreach ($entity_bundles as $entity_bundle) {
        $entity_bundles_options[$entity_bundle->id()] = $entity_bundle->label();
      }
      $form['salsify_api_settings']['setup_types']['bundle'] = [
        '#type' => 'select',
        '#title' => $this->t('Select a bundle'),
        '#options' => $entity_bundles_options,
        '#default_value' => $config->get('bundle'),
        '#description' => $this->t('The bundle to use for product mapping from Salsify.'),
        '#required' => TRUE,
        '#cache' => [
          'tags' => [
            'salsify_config',
          ],
        ],
      ];
    }

    if ($config->get('product_feed_url') &&
      (($config->get('access_token')) || ($config->get('client_id') && $config->get('client_secret')))&&
      $config->get('bundle')) {
      $form['salsify_operations'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Operations'),
        '#collapsible' => TRUE,
        '#group' => 'salsify_operations_group',
      ];
      $form['salsify_operations']['salsify_manual_import_method'] = [
        '#type' => 'select',
        '#title' => $this->t('Manual import method'),
        '#options' => [
          'updated' => $this->t('Only process updated content from Salsify'),
          'force' => $this->t('Force sync all Salsify content'),
        ],
      ];
      $form['salsify_operations']['salsify_start_import'] = [
        '#type' => 'button',
        '#value' => $this->t('Sync with Salsify'),
        '#prefix' => '<p>',
        '#suffix' => '</p>',
      ];
      $form['salsify_operations']['salsify_import_reminder'] = [
        '#type' => 'markup',
        '#markup' => '<p><strong>' . $this->t('Not seeing your changes from Salsify?') . '</strong><br/>' . $this->t('If you just made a change, your product channel will need to be updated to reflect the change. For details on channels in Salsify, see <a href="@url" target="_blank">Salsify\'s documentation.</a >', ['@url' => 'https://help.salsify.com/help/getting-started-with-channels']) . '</p>',
      ];
    }

    $form['admin_options'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Additional options'),
      '#collapsible' => TRUE,
      '#group' => 'additional_settings',
    ];

    // Create a description for the Import Method field. This is to note the
    // issue with Drupal core, which at the time of this writing has issues
    // rendering more than 64 fields on entity edit/update forms.
    $description = '<strong>' . $this->t('Manual Mapping Only:') . '</strong> '
      . $this->t('Only Salsify fields that have been mapped to existing Drupal fields will have their values imported.') . '<br/>'
      . '<strong>' . $this->t('Hybrid Manual/Dynamic Mapping:') . '</strong> '
      . $this->t('All Salsify fields will be imported into fields. Any existing field mappings will be honored and preserved. Any fields not manually mapped will be dynamically created on import and managed via this module.') . '<br/>'
      . '<em>' . $this->t('Warning:') . ' '
      . $this->t('For imports with a large number of fields, editing the Salsify entities can result performance issues and 500 errors. It is not recommended to use the "Hybrid" option for data sets with a large number of fields.') . '</em>';

    $form['admin_options']['import_method'] = [
      '#type' => 'select',
      '#title' => $this->t('Import Method'),
      '#description' => $description,
      '#options' => [
        'manual' => $this->t('Manual Mapping Only'),
        'dynamic' => $this->t('Hybrid Manual/Dynamic Mapping'),
      ],
      '#default_value' => $config->get('import_method') ? $config->get('import_method') : 'manual',
      '#required' => TRUE,
    ];

    $form['admin_options']['entity_reference_allow'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow mapping Salsify data to entity reference fields.'),
      '#description' => $this->t('Taxonomy term entity reference fields are supported by default. <em>To get this working correctly with entities other than taxonomy terms, additional processing via custom code will likely be required. Imports performed with this checked without any custom processing are subject to failure.</em>'),
      '#default_value' => $config->get('entity_reference_allow'),
    ];

    if ($this->moduleHandler->moduleExists('media_entity')) {
      $form['admin_options']['process_media_assets'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Process Salsify media assets into media fields.'),
        '#description' => $this->t('Note: This will require media entities setup to match filetypes imported from Salsify. Importing will complete on a best effort basis.'),
        '#default_value' => $config->get('process_media_assets'),
      ];
    }
    else {
      $form['admin_options']['process_media_notice'] = [
        '#type' => 'markup',
        '#markup' => $this->t('Enable the Media Entity module to allow importing media assets.'),
        '#prefix' => '<p><em>',
        '#suffix' => '</em></p>',
      ];
    }

    $form['admin_options']['keep_fields_on_uninstall'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Leave all dynamically added fields on module uninstall.'),
      '#default_value' => $config->get('keep_fields_on_uninstall'),
    ];

    $form['admin_options']['cron_force_update'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Force entities update by cron.'),
      '#default_value' => $config->get('cron_force_update'),
    ];

    $mail_config = $this->config('user.mail');

    $email_token_help = $this->t('Available tokens are: [site:name],
    [site:url], [user:display-name], [user:account-name], [user:mail],
    [site:login-url], [site:url-brief], [salsify:validation_errors],
    [salsify:deleted_items] .');

    $form['email_salsify_import'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Email notification settings'),
      '#collapsible' => TRUE,
      '#group' => 'email_settings',
    ];

    $form['email_salsify_import']['send_email'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Send email.'),
      '#description' => $this->t('If checked, Report will be send after import.'),
      '#default_value' => $config->get('send_email'),
    ];

    $form['email_salsify_import']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#default_value' => $config->get('salsify_import.email'),
      '#maxlength' => 180,
    ];

    $form['email_salsify_import']['email_salsify_import_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#default_value' => $mail_config->get('salsify_import.subject'),
      '#maxlength' => 180,
    ];
    $form['email_salsify_import']['email_salsify_import_body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#default_value' => $mail_config->get('salsify_import.body'),
      '#description' => $this->t('Edit the import report email messages.') . ' ' . $email_token_help,
      '#rows' => 15,
    ];

    return $form;
  }

  /**
   * Handler for ajax reload of entity bundles.
   *
   * @param array $form
   *   The config form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The submitted values from the config form.
   */
  public function loadEntityBundles(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    // Add the command to update the form elements.
    $response->addCommand(new ReplaceCommand('.salsify-config-entity-types', $form['salsify_api_settings']['setup_types']));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // If the form was submitted via the "Sync" button, then run the import
    // process right away.
    $trigger = $form_state->getTriggeringElement();
    if ($trigger['#id'] == 'edit-salsify-start-import') {
      $update_method = $form_state->getValue('salsify_manual_import_method');
      $force_update = FALSE;
      if ($update_method == 'force') {
        $force_update = TRUE;
      }
      $results = $this->salsifyFields
        ->importProductData(TRUE, $force_update);
      if ($results) {
        $this->messenger()->addMessage($results['message'], $results['status']);
      }
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('salsify_integration.settings');

    // Remove the options settings if the import method was changed from fields
    // to serialized.
    $new_import_method = $form_state->getValue('import_method');
    if ($config->get('import_method') != $new_import_method && $new_import_method == 'manual') {
      $config_options = $this->configFactory->getEditable('salsify_integration.field_options');
      $config_options->delete();
    }
    $config->set('import_method', $new_import_method);

    $config->set('product_feed_url', $form_state->getValue('product_feed_url'));
    $config->set('access_token', $form_state->getValue('access_token'));
    $config->set('entity_type', $form_state->getValue('entity_type'));
    $config->set('bundle', $form_state->getValue('bundle'));
    $config->set('keep_fields_on_uninstall', $form_state->getValue('keep_fields_on_uninstall'));
    $config->set('cron_force_update', $form_state->getValue('cron_force_update'));
    $config->set('entity_reference_allow', $form_state->getValue('entity_reference_allow'));
    $config->set('process_media_assets', $form_state->getValue('process_media_assets'));
    $config->set('import_method', $form_state->getValue('import_method'));
    $config->set('salsify_import.email', $form_state->getValue('email'));
    $config->set('send_email', $form_state->getValue('send_email'));
    $config->set('auth_method', $form_state->getValue('auth_method'));
    $config->set('client_id', $form_state->getValue('client_id'));
    $config->set('client_secret', $form_state->getValue('client_secret'));
    // Save the configuration.
    $config->save();

    $this->configFactory->getEditable('user.mail')
      ->set('salsify_import.subject', $form_state->getValue('email_salsify_import_subject'))
      ->set('salsify_import.body', $form_state->getValue('email_salsify_import_body'))
      ->save();

    // Flush the cache entries tagged with 'salsify_config' to force the API
    // to lookup the field configurations again for the field mapping form.
    Cache::invalidateTags(['salsify_config']);

    parent::submitForm($form, $form_state);
  }

  /**
   * Return the configuration names.
   */
  protected function getEditableConfigNames() {
    return [
      'salsify_integration.settings',
    ];
  }

}
