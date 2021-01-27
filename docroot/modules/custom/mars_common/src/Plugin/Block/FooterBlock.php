<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\MenuBuilder;
use Drupal\mars_common\ThemeConfiguratorParser;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Footer Block.
 *
 * @Block(
 *   id = "footer_block",
 *   admin_label = @Translation("MARS: Footer block"),
 *   category = @Translation("Global elements"),
 * )
 */
class FooterBlock extends BlockBase implements ContainerFactoryPluginInterface {


  /**
   * Menu storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $menuStorage;

  /**
   * ThemeConfiguratorParser.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  protected $themeConfiguratorParser;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * Term storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * Menu builder service.
   *
   * @var \Drupal\mars_common\MenuBuilder
   */
  private $menuBuilder;

  /**
   * Custom cache tag.
   *
   * @var string
   */
  const CUSTOM_CACHE_TAG = 'custom_region_cache';

  /**
   * Vocabulary id of taxonomy terms region.
   *
   * @var string
   */
  const VID_TAXONOMY_REGION = 'mars_regions';

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    LanguageHelper $language_helper,
    ThemeConfiguratorParser $themeConfiguratorParser,
    MenuBuilder $menu_builder
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->menuStorage = $entity_type_manager->getStorage('menu');
    $this->themeConfiguratorParser = $themeConfiguratorParser;
    $this->languageHelper = $language_helper;
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->menuBuilder = $menu_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('mars_common.language_helper'),
      $container->get('mars_common.theme_configurator_parser'),
      $container->get('mars_common.menu_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conf = $this->getConfiguration();
    $build['#logo'] = $this->themeConfiguratorParser->getLogoFromTheme();

    $theme_logo_alt = $this->themeConfiguratorParser->getLogoAltFromTheme();
    $build['#logo_alt'] = ($theme_logo_alt)
      ? $this->languageHelper->translate($theme_logo_alt)
      : $theme_logo_alt;

    // Get brand border path.
    $build['#brand_border'] = $this->themeConfiguratorParser->getBrandBorder();

    $build['#top_footer_menu'] = $this->menuBuilder->getMenuItemsArray($conf['top_footer_menu']);
    $build['#legal_links'] = $this->menuBuilder->getMenuItemsArray($conf['legal_links']);
    $build['#marketing'] = $this->languageHelper->translate($conf['marketing']['value']);
    $build['#corporate_tout_text'] = $this->languageHelper->translate($conf['corporate_tout']['title']);
    $build['#corporate_tout_url'] = [
      'href' => $conf['corporate_tout']['url'],
      'name' => $build['#corporate_tout_text'],
    ];

    $build['#social_links'] = [];
    if ($conf['social_links_toggle']) {
      $build['#social_links'] = $this->themeConfiguratorParser->socialLinks();
    }
    if ($conf['region_selector_toggle']) {
      $terms = $this->termStorage->loadTree(self::VID_TAXONOMY_REGION, 0, NULL, TRUE);
      $build['#region_selector'] = [];
      if (!empty($terms)) {
        foreach ($terms as $term) {
          $term = $this->languageHelper->getTranslation($term);
          $region_url = '#';
          $url = $term->get('field_mars_url')->first();
          if (!is_null($url)) {
            $region_url = $url->getUrl();
          }
          $build['#region_selector'][] = [
            'title' => $term->getName(),
            'url' => $region_url,
          ];
        }
        $terms_objects = $this->termStorage->loadByProperties([
          'vid' => self::VID_TAXONOMY_REGION,
          'field_default_region' => TRUE,
        ]);
        if ($terms_objects) {
          /** @var \Drupal\taxonomy\TermInterface $default_region */
          $default_region = reset($terms_objects);
          $default_region = $this->languageHelper->getTranslation($default_region);
          $build['#current_region_title'] = $default_region->getName();
        }
      }
    }

    CacheableMetadata::createFromRenderArray($build)
      ->merge(
        $this->themeConfiguratorParser->getCacheMetadataForThemeConfigurator()
      )
      ->applyTo($build);

    $build['#theme'] = 'footer_block';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['top_footer_menu'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'menu',
      '#title' => $this->t('Top Footer menu'),
      '#required' => TRUE,
      '#default_value' => isset($config['top_footer_menu']) ? $this->menuStorage->load($this->configuration['top_footer_menu']) : NULL,
    ];
    $form['legal_links'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'menu',
      '#title' => $this->t('Legal links menu'),
      '#required' => TRUE,
      '#default_value' => isset($config['legal_links']) ? $this->menuStorage->load($this->configuration['legal_links']) : NULL,
    ];
    $form['marketing'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Marketing & Copyright Messaging'),
      '#default_value' => $config['marketing']['value'] ?? '',
      '#format' => $config['marketing']['format'] ?? 'plain_text',
    ];
    $form['corporate_tout'] = [
      '#type' => 'details',
      '#title' => $this->t('Mars corporate tout'),
      '#open' => TRUE,
    ];
    $form['corporate_tout']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Link URL'),
      '#maxlength' => 2048,
      '#required' => TRUE,
      '#default_value' => $config['corporate_tout']['url'] ?? '',
    ];
    $form['corporate_tout']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link Title'),
      '#maxlength' => 2048,
      '#required' => TRUE,
      '#default_value' => $config['corporate_tout']['title'] ?? '',
    ];
    $form['social_links_toggle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display social links'),
      '#default_value' => $config['social_links_toggle'] ?? TRUE,
    ];
    $form['region_selector_toggle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display region selector'),
      '#default_value' => $config['region_selector_toggle'] ?? TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = parent::getCacheTags();
    // Include taxonomies
    // update process dependencies cache.
    $cache_tags = Cache::mergeTags($cache_tags, [self::CUSTOM_CACHE_TAG]);
    return $cache_tags;
  }

}
