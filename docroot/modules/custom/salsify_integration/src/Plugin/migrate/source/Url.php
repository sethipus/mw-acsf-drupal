<?php

namespace Drupal\salsify_integration\Plugin\migrate\source;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_plus\Plugin\migrate\source\Url as UrlBase;
use Drupal\salsify_integration\Form\ConfigForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin to get URL from config settings.
 *
 * @MigrateSource(
 *   id = "brand_drupal_salsify_url"
 * )
 */
class Url extends UrlBase implements ContainerFactoryPluginInterface {

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructor.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MigrationInterface $migration,
    ImmutableConfig $config,
    LanguageManagerInterface $language_manager
  ) {
    $this->languageManager = $language_manager;
    $configuration['urls'] = $this->selectMigrationSources($migration, $config);
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('config.factory')->get(ConfigForm::CONFIG_NAME),
      $container->get('language_manager')
    );
  }

  /**
   * Selects migration source url based on the migration id and configuration.
   *
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   *   The migration object.
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   Salsify's configuration object.
   *
   * @return array
   *   Returns migration sources for the target migration type.
   */
  private function selectMigrationSources(MigrationInterface $migration, ImmutableConfig $config) {
    $sources = [];
    $is_i18n_migration = strstr($migration->id(), 'i18n');
    $available_languages = $this->languageManager->getLanguages();
    foreach ($available_languages as $language) {
      if ($language->isDefault() && !$is_i18n_migration) {
        $sources[] = $config->get(ConfigForm::SALSIFY_MULTICHANNEL_APPROACH . '.url');
      }
      else {
        if (!empty($config->get(ConfigForm::SALSIFY_MULTICHANNEL_APPROACH . '.' . $language->getId() . '.enable')) && $is_i18n_migration) {
          $sources[] = $config->get(ConfigForm::SALSIFY_MULTICHANNEL_APPROACH . '.' . $language->getId() . '.config.url');
        }
      }
    }
    return $sources;
  }

}
