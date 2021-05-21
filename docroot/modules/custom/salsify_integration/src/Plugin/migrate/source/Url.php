<?php

namespace Drupal\salsify_integration\Plugin\migrate\source;

use Drupal\Core\Config\ImmutableConfig;
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
   * Constructor.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MigrationInterface $migration,
    ImmutableConfig $config
  ) {
    $configuration['urls'] = $config->get(ConfigForm::SALSIFY_MULTICHANNEL_APPROACH . '.url');
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
      $container->get('config.factory')->get(ConfigForm::CONFIG_NAME)
    );
  }

}
