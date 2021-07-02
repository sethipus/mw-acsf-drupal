<?php

namespace Drupal\brand_drupal_salsify_bens_uk\Form;

use Drupal\brand_drupal_salsify_bens_uk\Service\MigrateToolsDecorator;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SalsifyMigrationSettingsForm.
 *
 * @package Drupal\brand_drupal_salsify_bens_uk\Form
 */
class SalsifyMigrationSettingsForm extends ConfigFormBase {

  /**
   * The migration plugin manager service.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface
   */
  protected $migrationPluginManager;

  /**
   * The migrate tools service.
   *
   * @var \Drupal\brand_drupal_salsify_bens_uk\Service\MigrateToolsDecorator
   */
  protected $migrateToolsCommands;

  /**
   * SalsifyMigrationSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migration_plugin_manager
   *   The migration plugin manager service.
   * @param \Drupal\brand_drupal_salsify_bens_uk\Service\MigrateToolsDecorator $migrate_tools_commands
   *   The migrate tools service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    MigrationPluginManagerInterface $migration_plugin_manager,
    MigrateToolsDecorator $migrate_tools_commands
  ) {
    parent::__construct($config_factory);
    $this->migrationPluginManager = $migration_plugin_manager;
    $this->migrateToolsCommands = $migrate_tools_commands;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.migration'),
      $container->get('brand_drupal_salsify_bens_uk.migrate_tools')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['salsify_integration.migrate_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bens_migrate_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('salsify_integration.migrate_settings');
    $migration_definitions = $this->migrationPluginManager->getDefinitions();

    $migrations_to_display = [];
    if (!empty($migration_definitions)) {
      foreach ($migration_definitions as $migration_definition_id => $migration_definition) {
        if (strstr($migration_definition_id, 'node_product') || strstr($migration_definition_id, 'node_product_variant')) {
          $migrations_to_display[$migration_definition_id] = $migration_definition['label'];
        }
      }
    }

    if (!empty($migrations_to_display)) {
      $form['migration_ids'] = [
        '#title' => $this->t('Available Salsify`s channel approach migrations'),
        '#type' => 'checkboxes',
        '#options' => $migrations_to_display,
        '#description' => $this->t('Please select common migrations which should be used for importing data from source JSON'),
        '#default_value' => $config->get('migration_ids') ?? [],
      ];

      $migrate_status_options = [
        'group' => '',
        'tag' => '',
        'names-only' => FALSE,
        'continue-on-failure' => FALSE,
      ];
      try {
        $status_values = $this->migrateToolsCommands->status(implode(',', array_keys($migrations_to_display)), $migrate_status_options);

        $form['migrations_info'] = [
          '#type' => 'details',
          '#title' => $this->t('Migrations status'),
        ];
        $form['migrations_info']['status'] = [
          '#type' => 'table',
          '#header' => [
            $this->t('Group'),
            $this->t('ID'),
            $this->t('Status'),
            $this->t('Total'),
            $this->t('Imported'),
            $this->t('Unprocessed'),
            $this->t('Last Imported'),
          ],
        ];
        foreach ($status_values->getArrayCopy() as $index => $migration) {
          $form['migrations_info']['status']['#rows'][$index]['data'] = $migration;
        }
      }
      catch (\Exception $e) {
        $form['migrations_info'] = [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          '#value' => $this->t('Something went wrong during retrieval of the migration information. Please check the global Salsify configuration, e.g. request a new export operation. Error message: @error', ['@error' => $e->getMessage()]),
        ];
      }

      return parent::buildForm($form, $form_state);
    }
    else {
      $form['nothing_to_select'] = [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $this->t('The active site configuration does not contain any available migration to select.'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('salsify_integration.migrate_settings');
    $configuration_values = $form_state->getValues();
    $migration_ids = !empty($configuration_values['migration_ids']) ? $configuration_values['migration_ids'] : [];
    $enabled_migration_ids = [];
    foreach ($migration_ids as $id => $value) {
      if (!empty($value)) {
        $enabled_migration_ids[] = $id;
      }
    }
    $config->set('migration_ids', $enabled_migration_ids)->save();
    parent::submitForm($form, $form_state);
  }

}
