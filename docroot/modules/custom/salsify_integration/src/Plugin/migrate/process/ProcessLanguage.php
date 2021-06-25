<?php

namespace Drupal\salsify_integration\Plugin\migrate\process;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Process entities language based on channel approach configuration.
 *
 * Available configuration keys:
 * - source: Source property.
 *
 * The plugin returns the value of the property given by the "source"
 * configuration key.
 *
 * Examples:
 *
 * @code
 * process:
 *   bar:
 *     plugin: salsify_i18n_process_language
 *     source: language
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "salsify_i18n_process_language"
 * )
 */
class ProcessLanguage extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Flag indicating whether there are multiple values.
   *
   * @var bool
   */
  protected $multiple;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LanguageManagerInterface $language_manager,
    ConfigFactoryInterface $config_factory
  ) {
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory->get('salsify_integration.settings');
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $current_source_url = $migrate_executable->getSource()->getDataParserPlugin()->currentUrl();
    return $this->getLangcodeByUrl($current_source_url);
  }

  /**
   * Provides a langcode related to the migration source URL.
   *
   * @param string $current_source_url
   *   The curent migration source URL.
   *
   * @return false|int|string
   *   Returns a langcode related to the migration source URL.
   */
  protected function getLangcodeByUrl(string $current_source_url) {
    $available_languages = $this->languageManager->getLanguages();
    $sources = [];
    foreach ($available_languages as $language) {
      if (!empty($this->configFactory->get('salsify_multichannel_approach.' . $language->getId() . '.enable')) && !$language->isDefault()) {
        $sources[$language->getId()] = $this->configFactory->get('salsify_multichannel_approach.' . $language->getId() . '.config.url');
      }
    }
    if (in_array($current_source_url, $sources)) {
      return array_search($current_source_url, $sources);
    }
    else {
      return $this->languageManager->getDefaultLanguage()->getId();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return $this->multiple;
  }

}
