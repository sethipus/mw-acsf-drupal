<?php

namespace Drupal\salsify_integration\Form;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\salsify_integration\Event\SalsifyGetEntityTypesEvent;
use Drupal\salsify_integration\ProductHelper;
use Drupal\salsify_integration\Salsify;
use Drupal\salsify_integration\SalsifyFields;
use Drupal\salsify_integration\SalsifyImport;
use Drupal\salsify_integration\SalsifyImportField;
use Drupal\salsify_integration\Run\RunResource;
use Drupal\salsify_integration\MigrationRunner;
use Drupal\views\Ajax\ScrollTopCommand;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Salsify Configuration form class.
 */
class ConfigForm extends ConfigFormBase {

  protected const SALSIFY_LOGGER_CHANNEL = 'salsify_integration';

  /**
   * Salsify none approach id.
   */
  public const SALSIFY_APPROACH_NONE = 'none';

  /**
   * Salsify API approach id.
   */
  public const SALSIFY_API_APPROACH = 'salsify_api_approach';

  /**
   * Salsify Multichannel approach id.
   */
  public const SALSIFY_MULTICHANNEL_APPROACH = 'salsify_multichannel_approach';

  /**
   * Setting configuration ID.
   */
  public const CONFIG_NAME = 'salsify_integration.settings';

  /**
   * Runs ID mesasge.
   */
  protected const MSG_RUNS_ID = 'Runs ID: %s';

  /**
   * Runs status mesasge.
   */
  protected const MSG_RUNS_STATUS = 'Runs status: %s';

  /**
   * Runs status mesasge.
   */
  protected const MSG_RUNS_URL = 'Export URL: %s';

  /**
   * Form `org_id` field.
   */
  public const KEY_ORG_ID = 'org_id';

  /**
   * Form `channel_id` field.
   */
  public const KEY_CHANNEL_ID = 'channel_id';

  /**
   * Form `api_key` field.
   */
  public const KEY_API_KEY = 'api_key';

  /**
   * Form `brand_name` field.
   */
  public const BRAND_NAME = 'brand_name';

  /**
   * Form `url` field.
   */
  public const KEY_URL = 'url';

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
   * The Salsify fields module.
   *
   * @var \Drupal\Core\Batch\BatchBuilder
   */
  protected $batchBuilder;

  /**
   * The Queue.
   *
   * @var \Drupal\Core\Queue\QueueInterface
   */
  protected $importQueue;


  /**
   * Salsify Runs resource.
   *
   * @var \Drupal\salsify_integration\Run\RunResource
   */
  protected $runsResource;

  /**
   * Product migration runner.
   *
   * @var \Drupal\salsify_integration\MigrationRunner
   */
  protected $migrationRunner;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

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
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The Queue factory.
   * @param \Drupal\salsify_integration\Run\RunResource $runsResource
   *   Salsify Runs resource.
   * @param \Drupal\salsify_integration\MigrationRunner $migrationRunner
   *   Product migration runner.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    ContainerAwareEventDispatcher $event_dispatcher,
    ModuleHandlerInterface $module_handler,
    SalsifyFields $salsify_fields,
    QueueFactory $queue_factory,
    RunResource $runsResource,
    MigrationRunner $migrationRunner,
    LanguageManagerInterface $language_manager
  ) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->eventDispatcher = $event_dispatcher;
    $this->moduleHandler = $module_handler;
    $this->salsifyFields = $salsify_fields;
    $this->batchBuilder = new BatchBuilder();
    $this->importQueue = $queue_factory->get('salsify_integration_content_import');
    $this->runsResource = $runsResource;
    $this->migrationRunner = $migrationRunner;
    $this->languageManager = $language_manager;
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
      $container->get('salsify_integration.salsify_fields'),
      $container->get('queue'),
      $container->get('salsify_integration.salsify.runs'),
      $container->get('salsify_integration.migrations.products'),
      $container->get('language_manager')
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
    $config = $this->config(self::CONFIG_NAME);
    $saved_approach = $config->get('approach');

    $form['general'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('General configuration'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    $form['general']['approach'] = [
      '#type' => 'select',
      '#title' => $this->t('Salsify approach'),
      '#default_value' => $saved_approach ?? static::SALSIFY_API_APPROACH,
      '#options' => [
        self::SALSIFY_APPROACH_NONE => $this->t('None'),
        self::SALSIFY_API_APPROACH => $this->t('API approach'),
        self::SALSIFY_MULTICHANNEL_APPROACH => $this->t('Multichannel approach'),
      ],
      '#required' => TRUE,
    ];
    // Build PS widget settings fieldset.
    $form['general'][self::SALSIFY_API_APPROACH] = [
      '#type' => 'fieldset',
      '#title' => $this->t('API channel approach configuration'),
      '#states' => [
        'visible' => [
          [':input[name="approach"]' => ['value' => self::SALSIFY_API_APPROACH]],
        ],
      ],
    ];
    // Build CC widget settings fieldset.
    $form['general'][self::SALSIFY_MULTICHANNEL_APPROACH] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Multichannel approach configuration'),
      '#tree' => TRUE,
      '#states' => [
        'visible' => [
          [':input[name="approach"]' => ['value' => self::SALSIFY_MULTICHANNEL_APPROACH]],
        ],
      ],
    ];
    $this->buildApiApproachElement($form, $form_state);
    $this->buildMultichannelApproachElement($form, $form_state);
    $form['#attached']['library'] = 'views/views.ajax';
    return parent::buildForm($form, $form_state);
  }

  /**
   * Builds  configuration fields.
   *
   * @param array $form
   *   The given form to update.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The Salsify approach id.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function buildApiApproachElement(array &$form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIG_NAME);
    $fieldset = &$form['general'][self::SALSIFY_API_APPROACH];
    $fieldset['salsify_api_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Salsify API Settings'),
      '#collapsible' => TRUE,
      '#group' => 'salsify_api_settings_group',
    ];

    $fieldset['salsify_api_settings']['product_feed_url'] = [
      '#type' => 'url',
      '#size' => 75,
      '#title' => $this->t('Salsify Product Feed'),
      '#default_value' => $config->get('product_feed_url'),
      '#description' => $this->t('The link to the product feed from a Salsify channel. For details on channels in Salsify, see <a href="@url" target="_blank">Salsify\'s documentation</a>', ['@url' => 'https://help.salsify.com/help/getting-started-with-channels']),
    ];

    $fieldset['salsify_api_settings']['auth_method'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a bundle'),
      '#options' => [
        Salsify::AUTH_METHOD_TOKEN => $this->t('Access token'),
        Salsify::AUTH_METHOD_SECRET => $this->t('Client id and secret'),
      ],
      '#default_value' => $config->get('auth_method'),
      '#description' => $this->t('Please choose auth api method.'),
    ];

    $fieldset['salsify_api_settings']['access_token'] = [
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
          ':input[name="approach"]' => ['value' => self::SALSIFY_API_APPROACH],
          ':input[name="auth_method"]' => ['value' => Salsify::AUTH_METHOD_TOKEN],
        ],
      ],
    ];

    $fieldset['salsify_api_settings']['client_id'] = [
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
          ':input[name="approach"]' => ['value' => self::SALSIFY_API_APPROACH],
          ':input[name="auth_method"]' => ['value' => Salsify::AUTH_METHOD_SECRET],
        ],
      ],
    ];

    $fieldset['salsify_api_settings']['client_secret'] = [
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
          ':input[name="approach"]' => ['value' => self::SALSIFY_API_APPROACH],
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

    $fieldset['salsify_api_settings']['setup_types'] = [
      '#type' => 'container',
      '#prefix' => '<div class="salsify-config-entity-types">',
      '#suffix' => '</div>',
    ];

    $fieldset['salsify_api_settings']['setup_types']['entity_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Select an entity type'),
      '#options' => $entity_type_options,
      '#default_value' => $config->get('entity_type'),
      '#description' => $this->t('The entity type to use for product mapping from Salsify.'),
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
      $fieldset['salsify_api_settings']['setup_types']['bundle'] = [
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
      $fieldset['salsify_operations'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Operations'),
        '#collapsible' => TRUE,
        '#group' => 'salsify_operations_group',
      ];
      $fieldset['salsify_operations']['salsify_manual_import_method'] = [
        '#type' => 'select',
        '#title' => $this->t('Manual import method'),
        '#options' => [
          'updated' => $this->t('Only process updated content from Salsify'),
          'force' => $this->t('Force sync all Salsify content'),
        ],
      ];
      $fieldset['salsify_operations']['import_warning'] = [
        '#type' => 'markup',
        '#markup' => '<p>' . $this->t('Use "Sync with Salsify"') .
        '<strong> ' . $this->t('only for development needs.') .
        '</strong></p>',
      ];
      $fieldset['salsify_operations']['salsify_start_import'] = [
        '#type' => 'submit',
        '#value' => $this->t('Sync with Salsify'),
        '#prefix' => '<p>',
        '#suffix' => '</p>',
      ];
      $fieldset['salsify_operations']['import_queue_items'] = [
        '#type' => 'markup',
        '#markup' => '<p>Items in the queue: <strong>' . $this->importQueue->numberOfItems() . '</strong></p>',
      ];
      $fieldset['salsify_operations']['import_stem_one'] = [
        '#type' => 'markup',
        '#markup' => '<p><strong> ' . $this->t('Step 1:') .
        '</strong></p>',
      ];
      $fieldset['salsify_operations']['salsify_create_queue'] = [
        '#type' => 'submit',
        '#name' => 'create_import_queue',
        '#value' => $this->t('Add import items to the queue'),
        '#prefix' => '<p>',
        '#suffix' => '</p>',
      ];
      $fieldset['salsify_operations']['import_stem_two'] = [
        '#type' => 'markup',
        '#markup' => '<p><strong> ' . $this->t('Step 2:') .
        '</strong></p>',
      ];
      $fieldset['salsify_operations']['import_queue_chunk_size'] = [
        '#type' => 'number',
        '#title' => $this->t('Size of batch operation'),
        '#default_value' => $config->get('import_queue_chunk_size') ?? 20,
      ];
      $fieldset['salsify_operations']['salsify_run_queue'] = [
        '#type' => 'submit',
        '#name' => 'create_run_queue',
        '#value' => $this->t('Run import queue'),
        '#prefix' => '<p>',
        '#suffix' => '</p>',
      ];
      $fieldset['salsify_operations']['import_queue_items'] = [
        '#type' => 'markup',
        '#markup' => '<p>Items in the queue: <strong>' . $this->importQueue->numberOfItems() . '</strong></p>',
      ];
      $fieldset['salsify_operations']['salsify_purge_queue'] = [
        '#type' => 'submit',
        '#name' => 'purge_import_queue',
        '#value' => $this->t('Purge import queue'),
        '#prefix' => '<p>',
        '#suffix' => '</p>',
      ];
      $fieldset['salsify_operations']['salsify_import_reminder'] = [
        '#type' => 'markup',
        '#markup' => '<p><strong>' . $this->t('Not seeing your changes from Salsify?') . '</strong><br/>' . $this->t('If you just made a change, your product channel will need to be updated to reflect the change. For details on channels in Salsify, see <a href="@url" target="_blank">Salsify\'s documentation.</a >', ['@url' => 'https://help.salsify.com/help/getting-started-with-channels']) . '</p>',
      ];
    }

    $fieldset['admin_options'] = [
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

    $fieldset['admin_options']['import_method'] = [
      '#type' => 'select',
      '#title' => $this->t('Import Method'),
      '#description' => $description,
      '#options' => [
        'manual' => $this->t('Manual Mapping Only'),
        'dynamic' => $this->t('Hybrid Manual/Dynamic Mapping'),
      ],
      '#default_value' => $config->get('import_method') ? $config->get('import_method') : 'manual',
    ];

    $fieldset['admin_options']['entity_reference_allow'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow mapping Salsify data to entity reference fields.'),
      '#description' => $this->t('Taxonomy term entity reference fields are supported by default. <em>To get this working correctly with entities other than taxonomy terms, additional processing via custom code will likely be required. Imports performed with this checked without any custom processing are subject to failure.</em>'),
      '#default_value' => $config->get('entity_reference_allow'),
    ];

    if ($this->moduleHandler->moduleExists('media_entity')) {
      $fieldset['admin_options']['process_media_assets'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Process Salsify media assets into media fields.'),
        '#description' => $this->t('Note: This will require media entities setup to match filetypes imported from Salsify. Importing will complete on a best effort basis.'),
        '#default_value' => $config->get('process_media_assets'),
      ];
    }
    else {
      $fieldset['admin_options']['process_media_notice'] = [
        '#type' => 'markup',
        '#markup' => $this->t('Enable the Media Entity module to allow importing media assets.'),
        '#prefix' => '<p><em>',
        '#suffix' => '</em></p>',
      ];
    }

    $fieldset['admin_options']['keep_fields_on_uninstall'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Leave all dynamically added fields on module uninstall.'),
      '#default_value' => $config->get('keep_fields_on_uninstall'),
    ];

    $fieldset['admin_options']['cron_force_update'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Force entities update by cron.'),
      '#default_value' => $config->get('cron_force_update'),
    ];

    $mail_config = $this->config('user.mail');

    $email_token_help = $this->t('Available tokens are: [site:name],
    [site:url], [user:display-name], [user:account-name], [user:mail],
    [site:login-url], [site:url-brief], [salsify:validation_errors],
    [salsify:deleted_items] .');

    $fieldset['email_salsify_import'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Email notification settings'),
      '#collapsible' => TRUE,
      '#group' => 'email_settings',
    ];

    $fieldset['email_salsify_import']['send_email'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Send email.'),
      '#description' => $this->t('If checked, Report will be send after import.'),
      '#default_value' => $config->get('send_email'),
    ];

    $fieldset['email_salsify_import']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#default_value' => $config->get('salsify_import.email'),
      '#maxlength' => 180,
    ];

    $fieldset['email_salsify_import']['email_salsify_import_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#default_value' => $mail_config->get('salsify_import.subject'),
      '#maxlength' => 180,
    ];
    $fieldset['email_salsify_import']['email_salsify_import_body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#default_value' => $mail_config->get('salsify_import.body'),
      '#description' => $this->t('Edit the import report email messages.') . ' ' . $email_token_help,
      '#rows' => 15,
    ];

  }

  /**
   * Handler for ajax reload of entity bundles.
   *
   * @param array $form
   *   The config form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The submitted values from the config form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
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
  protected function buildMultichannelApproachElement(array &$form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIG_NAME);
    $fieldset = &$form['general'][self::SALSIFY_MULTICHANNEL_APPROACH];

    $site_languages = $this->languageManager->getLanguages();

    foreach ($site_languages as $language) {
      $this->buildMultiChannelApproachConfiguration($fieldset, $config, $language);
    }
  }

  /**
   * Builds language-dependent configuration form fields.
   *
   * @param array $fieldset
   *   The initial configuration fieldset.
   * @param \Drupal\Core\Config\Config $config
   *   Salsify multichannel approach config.
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The given language object.
   */
  private function buildMultiChannelApproachConfiguration(array &$fieldset, Config $config, LanguageInterface $language) {

    if (!$language->isDefault()) {
      $fieldset[$language->getId()]['enable'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Migrate translation for @language', ['@language' => $language->getName()]),
        '#default_value' => $config->get(self::SALSIFY_MULTICHANNEL_APPROACH . '.' . $language->getId() . '.enable'),
      ];
      $fieldset[$language->getId()]['config'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Migrate configuration for @language', ['@language' => $language->getName()]),
        '#states' => [
          'visible' => [
            [':input[name="salsify_multichannel_approach[' . $language->getId() . '][enable]"]' => ['checked' => TRUE]],
          ],
        ],
      ];

      $fieldset[$language->getId()]['config'][static::KEY_URL] = [
        '#type' => 'textarea',
        '#title' => $this->t('JSON file URL'),
        '#default_value' => $config->get(self::SALSIFY_MULTICHANNEL_APPROACH . '.' . $language->getId() . '.config.url'),
      ];

      $fieldset[$language->getId()]['config'][static::KEY_ORG_ID] = [
        '#type' => 'textfield',
        '#title' => $this->t('Organization ID'),
        '#default_value' => $config->get(self::SALSIFY_MULTICHANNEL_APPROACH . '.' . $language->getId() . '.config.org_id'),
        '#description' => $this->t(
          'Orgratization identifier provided by Salsify. For details on channels in Salsify, see <a href="@url" target="_blank">Salsify\'s documentation</a>',
          ['@url' => 'https://help.salsify.com/help/getting-started-with-channels']
        ),
      ];

      $fieldset[$language->getId()]['config'][static::KEY_CHANNEL_ID] = [
        '#type' => 'textfield',
        '#title' => $this->t('Channel ID'),
        '#default_value' => $config->get(self::SALSIFY_MULTICHANNEL_APPROACH . '.' . $language->getId() . '.config.channel_id'),
        '#description' => $this->t(
          'Channel ID provided by Salsify. For details on channels in Salsify, see <a href="@url" target="_blank">Salsify\'s documentation</a>',
          ['@url' => 'https://help.salsify.com/help/getting-started-with-channels']
        ),
      ];

      $fieldset[$language->getId()]['config'][static::KEY_API_KEY] = [
        '#type' => 'textfield',
        '#title' => $this->t('Salsify API key'),
        '#description' => $this->t(
          'The access token from the Salsify user account to use for this integration. For details on where to find the access token, see <a href="@url" target="_blank">Salsify\'s API documentation</a>',
          ['@url' => 'https://help.salsify.com/help/getting-started-api-authorization']
        ),
        '#default_value' => $config->get(self::SALSIFY_MULTICHANNEL_APPROACH . '.' . $language->getId() . '.config.api_key'),
      ];

      $fieldset[$language->getId()]['config']['actions']['request'] = [
        '#type' => 'submit',
        '#value' => $this->t('Request new "@language" export', ['@language' => $language->getName()]),
        '#ajax' => ['callback' => [$this, 'onRequest']],
      ];

      $fieldset[$language->getId()]['config']['actions']['status'] = [
        '#type' => 'submit',
        '#value' => $this->t('Check the "@language" export status', ['@language' => $language->getName()]),
        '#ajax' => ['callback' => [$this, 'onStatus']],
      ];
    }
    else {
      $fieldset[static::KEY_URL] = [
        '#type' => 'textarea',
        '#title' => $this->t('JSON file URL'),
        '#default_value' => $config->get(self::SALSIFY_MULTICHANNEL_APPROACH . '.url'),
      ];

      $fieldset[static::KEY_ORG_ID] = [
        '#type' => 'textfield',
        '#title' => $this->t('Organization ID'),
        '#default_value' => $config->get(self::SALSIFY_MULTICHANNEL_APPROACH . '.org_id'),
        '#description' => $this->t(
          'Orgratization identifier provided by Salsify. For details on channels in Salsify, see <a href="@url" target="_blank">Salsify\'s documentation</a>',
          ['@url' => 'https://help.salsify.com/help/getting-started-with-channels']
        ),
      ];

      $fieldset[static::KEY_CHANNEL_ID] = [
        '#type' => 'textfield',
        '#title' => $this->t('Channel ID'),
        '#default_value' => $config->get(self::SALSIFY_MULTICHANNEL_APPROACH . '.channel_id'),
        '#description' => $this->t(
          'Channel ID provided by Salsify. For details on channels in Salsify, see <a href="@url" target="_blank">Salsify\'s documentation</a>',
          ['@url' => 'https://help.salsify.com/help/getting-started-with-channels']
        ),
      ];

      $fieldset[static::KEY_API_KEY] = [
        '#type' => 'textfield',
        '#title' => $this->t('Salsify API key'),
        '#description' => $this->t(
          'The access token from the Salsify user account to use for this integration. For details on where to find the access token, see <a href="@url" target="_blank">Salsify\'s API documentation</a>',
          ['@url' => 'https://help.salsify.com/help/getting-started-api-authorization']
        ),
        '#default_value' => $config->get(self::SALSIFY_MULTICHANNEL_APPROACH . '.api_key'),
      ];

      $fieldset['actions']['request'] = [
        '#type' => 'submit',
        '#value' => $this->t('Request new export'),
        '#ajax' => ['callback' => [$this, 'onRequest']],
      ];

      $fieldset['actions']['status'] = [
        '#type' => 'submit',
        '#value' => $this->t('Check the export status'),
        '#ajax' => ['callback' => [$this, 'onStatus']],
      ];
      $fieldset['migration'] = [
        '#type' => 'submit',
        '#value' => $this->t('Run migrations'),
        '#ajax' => ['callback' => [$this, 'onMigration']],
        '#weight' => 100,
      ];
    }
    $fieldset[static::BRAND_NAME] = [
      '#type' => 'textfield',
      '#title' => $this->t('Brand name'),
      '#description' => $this->t(
        'Fill out the brand name in the case when you use one channel for
        many brands in other cases please leave it empty. For example: "brand_one"
        or "brand_one, brand_two"',
      ),
      '#default_value' => $config->get(self::SALSIFY_MULTICHANNEL_APPROACH . '.' . static::BRAND_NAME),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $approach_id = $form_state->getValue('approach');
    $config = $this->config(self::CONFIG_NAME);
    $config->set('approach', $approach_id);
    $config->save();
    switch ($approach_id) {
      case self::SALSIFY_API_APPROACH:
        self::submitApiApproachElement($form, $form_state);
        break;

      case self::SALSIFY_MULTICHANNEL_APPROACH:
        $config->set(self::SALSIFY_MULTICHANNEL_APPROACH, $form_state->getValue(self::SALSIFY_MULTICHANNEL_APPROACH));
        $config->save();
        break;

    }
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitApiApproachElement(array &$form, FormStateInterface $form_state) {

    // If the form was submitted via the "Sync" button, then run the import
    // process right away.
    $trigger = $form_state->getTriggeringElement();
    $update_method = $form_state->getValue('salsify_manual_import_method');
    $force_update = FALSE;
    if ($update_method == 'force') {
      $force_update = TRUE;
    }
    $config = $this->config(self::CONFIG_NAME);

    if ($trigger['#id'] == 'edit-salsify-start-import') {
      $this->salsifyStartImport($force_update);
      return;
    }
    elseif ($trigger['#id'] == 'edit-salsify-purge-queue') {
      $this->importQueue->deleteQueue();
      $this->messenger()->addMessage($this->t('All items in the Salsify import queue are purged.'));
      return;
    }
    elseif ($trigger['#id'] == 'edit-salsify-create-queue') {
      $this->salsifyFields->importProductData($force_update);
      $this->messenger()->addMessage($this->t('Import items was added to the import queue.'));
      return;
    }
    elseif ($trigger['#id'] == 'edit-salsify-run-queue') {
      $this->salsifyProcessQueue();
      return;
    }

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
    $config->set('import_queue_chunk_size', $form_state->getValue('import_queue_chunk_size'));
    // Save the configuration.
    $config->save();

    $this->configFactory->getEditable('user.mail')
      ->set('salsify_import.subject', $form_state->getValue('email_salsify_import_subject'))
      ->set('salsify_import.body', $form_state->getValue('email_salsify_import_body'))
      ->save();

    // Flush the cache entries tagged with 'salsify_config' to force the API
    // to lookup the field configurations again for the field mapping form.
    Cache::invalidateTags(['salsify_config']);
  }

  /**
   * Process salsify import queue.
   */
  public function salsifyProcessQueue() {
    $items = [];
    $this->batchBuilder
      ->setTitle($this->t('Salsify items processing'))
      ->setInitMessage($this->t('Initializing.'))
      ->setProgressMessage($this->t('Completed @current of @total.'))
      ->setErrorMessage($this->t('An error has occurred.'));

    while ($item = $this->importQueue->claimItem()) {
      $items[] = $item->data;
      $this->importQueue->deleteItem($item);
    }

    $config = $this->config('salsify_integration.settings');
    foreach (array_chunk($items, $config->get('import_queue_chunk_size') ?? 20) as $item_chunk) {
      $this->batchBuilder
        ->addOperation(
          [$this, 'batchProcessImportQueue'],
          [$item_chunk]
        );
    }

    $this->batchBuilder->setFinishCallback([
      $this,
      'finishedQueueProcess',
    ]);
    batch_set($this->batchBuilder->toArray());
  }

  /**
   * Initiate salsify import process.
   *
   * @param bool $force_update
   *   Force update.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function salsifyStartImport($force_update) {
    try {
      // Import the taxonomy term data if needed and if any mappings are using
      // entity reference fields that point to taxonomy fields.
      $product_data = $this->salsifyFields->importProductFields();
      $this->salsifyFields->prepareTermData($product_data);

      // Import the actual product data.
      if (!empty($product_data['products'])) {
        $this->batchBuilder
          ->setTitle($this->t('Salsify items processing'))
          ->setInitMessage($this->t('Initializing.'))
          ->setProgressMessage($this->t('Completed @current of @total.'))
          ->setErrorMessage($this->t('An error has occurred.'));

        $this->batchProcessItems(
          $product_data,
          $force_update,
          ProductHelper::PRODUCT_VARIANT_CONTENT_TYPE
        );
        $this->batchProcessItems(
          $product_data,
          $force_update,
          ProductHelper::PRODUCT_CONTENT_TYPE
        );
        $this->batchProcessItems(
          $product_data,
          $force_update,
          ProductHelper::PRODUCT_MULTIPACK_CONTENT_TYPE
        );

        $product_ids = array_column($product_data['products'], 'salsify:id');
        $this->batchBuilder
          ->addOperation(
            [$this, 'batchDeleteItems'],
            [$product_ids]
          );

        $this->batchBuilder->setFinishCallback([
          $this,
          'finished',
        ]);
        batch_set($this->batchBuilder->toArray());
      }
      else {
        $message = $this->t('Could not complete Salsify data import. No product data is available')->render();
        $this->logger(static::SALSIFY_LOGGER_CHANNEL)->error($message);
        $this->messenger()->addError($message);
      }
    }
    catch (MissingDataException $e) {
      $message = $this->t('A error occurred while making the request to Salsify. Check the API settings and try again.')->render();
      $this->logger(static::SALSIFY_LOGGER_CHANNEL)->error($message);
      $this->messenger()->addError($message);
      $this->messenger()->addError($e->getMessage());
    }
  }

  /**
   * Pre-processor for batch operations.
   */
  public function batchProcessItems($items, $force_update, $content_type) {

    foreach ($items['products'] as $product) {
      // Add child entity references.
      $this->salsifyFields->addChildLinks($items['mapping'], $product);
      $product['CMS: Market'] = $items['market'] ?? NULL;

      if (ProductHelper::getProductType($product) == $content_type) {
        $this->batchBuilder
          ->addOperation(
            [$this, 'batchProcessItem'],
            [[$product], $force_update, $content_type]
          );
      }
    }
  }

  /**
   * Processor for batch operations.
   */
  public static function batchProcessItem($items, $force_update, $content_type, array &$context) {

    static::setDefaultContextValues($context, $items);

    if (!empty($context['sandbox']['items'])) {
      $product = array_shift($context['sandbox']['items']);

      if (ProductHelper::getProductType($product) == $content_type) {
        $result = SalsifyImportField::processSalsifyItem(
          $product,
          $force_update,
          $content_type
        );

        if ($result['import_result'] == SalsifyImport::PROCESS_RESULT_UPDATED) {
          $context['results']['updated_products'] = array_merge(
            $context['results']['updated_products'] ?? [],
            [$product['GTIN']]
          );
        }
        elseif ($result['import_result'] == SalsifyImport::PROCESS_RESULT_CREATED) {
          $context['results']['created_products'] = array_merge(
            $context['results']['created_products'] ?? [],
            [$product['GTIN']]
          );
        }
        $context['results']['validation_errors'] = array_merge(
          $context['results']['validation_errors'] ?? [],
          $result['validation_errors']
        );
      }

      $context['sandbox']['progress']++;
    }

    // If not finished all tasks, we count percentage of process. 1 = 100%.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Processor for batch operations.
   */
  public static function batchProcessImportQueue($items, array &$context) {

    static::setDefaultContextValues($context, $items);

    if (!empty($context['sandbox']['items'])) {
      $items = $context['sandbox']['items'];

      $import_plugin = \Drupal::service('plugin.manager.queue_worker')
        ->createInstance('salsify_integration_content_import');
      /** @var \Drupal\salsify_integration\Plugin\QueueWorker\SalsifyContentImport $import_plugin */
      foreach ($items as $item) {
        $import_plugin->processItem($item);
      }

      $context['sandbox']['progress']++;
    }

    // If not finished all tasks, we count percentage of process. 1 = 100%.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Processor for batch operations.
   *
   * @param mixed $items
   *   Items for batch processing.
   * @param array $context
   *   Context array.
   */
  public static function batchDeleteItems($items, array &$context) {
    if (!empty($items)) {
      // Unpublish products in case of deletion at Salsify side.
      $context['results']['deleted_items'] = \Drupal::service('salsify_integration.salsify_product_repository')
        ->unpublishProducts($items);
    }
  }

  /**
   * Finished callback for batch.
   */
  public static function finished($success, $results, $operations) {
    \Drupal::logger(static::SALSIFY_LOGGER_CHANNEL)
      ->info(t(
        'The Salsify data import is complete. @created @updated', [
          '@created' => 'Created products: ' . implode(', ', $results['created_products'] ?? []) . '.',
          '@updated' => 'Updated products: ' . implode(', ', $results['updated_products'] ?? []) . '.',
        ]
      ));

    // Send import report.
    if ((isset($results['validation_errors']) && !empty($results['validation_errors'])) ||
      !empty($results['deleted_items'])) {
      $validation_errors = $results['validation_errors'] ?? [];
      \Drupal::service('salsify_integration.email_report')->sendReport($validation_errors, $results['deleted_items']);
    }

    $message = t('The Salsify data import is complete.');
    \Drupal::service('messenger')->addStatus($message);
  }

  /**
   * Finished callback for batch.
   */
  public static function finishedQueueProcess($success, $results, $operations) {
    \Drupal::logger(static::SALSIFY_LOGGER_CHANNEL)->info(t('The Salsify data import is complete.'));

    $message = t('The Salsify data import is complete.');
    \Drupal::service('messenger')->addStatus($message);
  }

  /**
   * Set default context values for the batch.
   *
   * @param array $context
   *   Batch context.
   * @param array $items
   *   Product data.
   */
  private static function setDefaultContextValues(array &$context, array &$items) {

    // Set default progress values.
    if (empty($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = count($items);
    }

    // Save items to array which will be changed during processing.
    if (empty($context['sandbox']['items'])) {
      $context['sandbox']['items'] = $items;
    }
  }

  /**
   * Check the Slasify run status.
   */
  public function onStatus($form, $form_state) {
    $langcode = $this->getMigrationLanguageFromFormState($form_state);
    $response = new AjaxResponse();

    try {
      if ($run = $this->runsResource->read($langcode)) {
        $response->addCommand(new MessageCommand(sprintf(static::MSG_RUNS_URL, $run->product_export_url)));
        $response->addCommand(new ScrollTopCommand('.messages-list'));
        if (empty($run->product_export_url)) {
          $response->addCommand(new MessageCommand('New URL is assigned, please reload the page.'));
          $response->addCommand(new ScrollTopCommand('.messages-list'));
        }
      }
    }
    catch (RequestException $exception) {
      $response->addCommand(new MessageCommand($exception->getMessage(), NULL, ['type' => 'error']));
      $response->addCommand(new ScrollTopCommand('.messages-list'));
      return $response;
    }

    return $response;
  }

  /**
   * Request the new Salsify run.
   */
  public function onRequest(&$form, $form_state) {
    $langcode = $this->getMigrationLanguageFromFormState($form_state);
    $response = new AjaxResponse();

    try {
      if ($run = $this->runsResource->create($langcode)) {
        $response->addCommand(new MessageCommand(sprintf(static::MSG_RUNS_ID, $run->id)));
        $response->addCommand(new ScrollTopCommand('.messages-list'));
        return $response;
      }
    }
    catch (RequestException $exception) {
      $response->addCommand(new MessageCommand($exception->getMessage(), NULL, ['type' => 'error']));
      $response->addCommand(new ScrollTopCommand('.messages-list'));
      return $response;
    }

    return $response;
  }

  /**
   * Run salsify migrations.
   */
  public function onMigration() {
    $this->migrationRunner->runProductMigration();

    $response = new AjaxResponse();
    $response->addCommand(new MessageCommand($this->t('Migration finished.')));
    $response->addCommand(new ScrollTopCommand('.messages-list'));
    return $response;
  }

  /**
   * Return the configuration names.
   *
   * @codeCoverageIgnore
   */
  protected function getEditableConfigNames() {
    return [
      self::CONFIG_NAME,
    ];
  }

  /**
   * Gets triggered button language id from the form state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return mixed
   *   Returns language id.
   */
  protected function getMigrationLanguageFromFormState(FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();
    // The hardcoded index [1] is related to the fieldset's langcode,
    // please don't forget to change the logic if form structure is going to be
    // changed. Langcode will be double checked on migration runner to valid id.
    // If the langcode is incorrect we may consider that request is coming from
    // the base migration form.
    $langcode = $element['#parents'][1];
    return $langcode;
  }

}
