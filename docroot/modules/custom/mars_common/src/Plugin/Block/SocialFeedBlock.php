<?php

namespace Drupal\mars_common\Plugin\Block;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\juicer_io\Entity\FeedConfiguration;
use Drupal\juicer_io\Model\Feed;
use Drupal\juicer_io\Model\FeedFactory;
use Drupal\juicer_io\Model\FeedItem;
use Drupal\mars_common\LanguageHelper;
use Drupal\mars_common\ThemeConfiguratorParser;
use Drupal\mars_common\Traits\OverrideThemeTextColorTrait;
use Drupal\mars_common\Traits\SelectBackgroundColorTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Social feed block.
 *
 * @Block(
 *   id = "social_feed",
 *   admin_label = @Translation("MARS: Social feed"),
 *   category = @Translation("Social feed"),
 * )
 */
class SocialFeedBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use SelectBackgroundColorTrait;
  use OverrideThemeTextColorTrait;

  const MAX_AGE_1_DAY = 60 * 60 * 24;

  /**
   * The config entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $entityStorage;

  /**
   * Feed factory service.
   *
   * @var \Drupal\juicer_io\Model\FeedFactory
   */
  private $feedFactory;

  /**
   * Time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $timeService;

  /**
   * Cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cacheBackend;

  /**
   * Language helper service.
   *
   * @var \Drupal\mars_common\LanguageHelper
   */
  private $languageHelper;

  /**
   * Theme Configurator service.
   *
   * @var \Drupal\mars_common\ThemeConfiguratorParser
   */
  private $themeConfigurator;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    $entity_type_manager = $container->get('entity_type.manager');
    $entity_storage = $entity_type_manager->getStorage('juicer_io_feed');
    $feed_factory = $container->get('juicer_io.feed_factory');
    $time_service = $container->get('datetime.time');
    $cache_backend = $container->get('cache.default');
    $theme_configurator = $container->get('mars_common.theme_configurator_parser');

    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_storage,
      $feed_factory,
      $time_service,
      $cache_backend,
      $container->get('mars_common.language_helper'),
      $theme_configurator
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityStorageInterface $entity_storage,
    FeedFactory $feed_factory,
    TimeInterface $time_service,
    CacheBackendInterface $cache_backend,
    LanguageHelper $language_helper,
  ThemeConfiguratorParser $themeConfigurator
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityStorage = $entity_storage;
    $this->feedFactory = $feed_factory;
    $this->timeService = $time_service;
    $this->cacheBackend = $cache_backend;
    $this->languageHelper = $language_helper;
    $this->themeConfigurator = $themeConfigurator;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $configEntity = $this->getFeedConfig();
    $label = $this->languageHelper->translate($this->configuration['label'] ?? '');
    $background_color = '';
    if (!empty($this->configuration['select_background_color']) && $this->configuration['select_background_color'] != 'default'
       && array_key_exists($this->configuration['select_background_color'], static::$colorVariables)
    ) {
      $background_color = static::$colorVariables[$this->configuration['select_background_color']];
    }
    $text_color_override = FALSE;
    if (!empty($this->configuration['override_text_color']['override_color'])) {
      $text_color_override = static::$overrideColor;
    }

    return [
      '#theme' => 'social_feed_block',
      '#select_background_color' => $background_color,
      '#label' => $label,
      '#items' => $this->getFeedItems(),
      '#social_feed_date_toggle' => ($this->configuration['social_feed_date_toggle']) ? $this->configuration['social_feed_date_toggle'] : NULL,
      '#graphic_divider' => $this->themeConfigurator->getGraphicDivider(),
      '#brand_border' => ($this->configuration['with_brand_borders']) ? $this->themeConfigurator->getBrandBorder2() : NULL,
      '#overlaps_previous' => $this->configuration['overlaps_previous'] ?? NULL,
      '#text_color_override' => $text_color_override,
      '#cache' => [
        'tags' => $configEntity->getCacheTags(),
        'max-age' => self::MAX_AGE_1_DAY,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $character_limit_config = \Drupal::config('mars_common.character_limit_page');
    $form['label_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => !empty($character_limit_config->get('social_feed_block_title')) ? $character_limit_config->get('social_feed_block_title') : 55,
      '#default_value' => $this->configuration['label'] ?? '',
      '#required' => TRUE,
    ];

    $form['feed'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Feed'),
      '#description' => $this->t('Please select a feed with at least 4 items in it for best results.'),
      '#target_type' => 'juicer_io_feed',
      '#required' => TRUE,
    ];

    try {
      $form['feed']['#default_value'] = $this->getFeedConfig();
    }
    catch (\RuntimeException $e) {
      // Cannot set default value as feed config is missing.
    }
    $form['social_feed_date_toggle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display social feed created days'),
      '#default_value' => $this->configuration['social_feed_date_toggle'] ?? FALSE,
    ];

    // Add select background color.
    $this->buildSelectBackground($form);

    $form['with_brand_borders'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('With/without brand border'),
      '#default_value' => $this->configuration['with_brand_borders'] ?? FALSE,
    ];

    $form['overlaps_previous'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('With/without overlaps previous'),
      '#default_value' => $this->configuration['overlaps_previous'] ?? FALSE,
    ];

    // Add override text color config.
    $this->buildOverrideColorElement($form, $this->configuration);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['feed'] = $form_state->getValue('feed');
    $this->configuration['label'] = $form_state->getValue('label_title');
    $this->configuration['select_background_color'] = $form_state->getValue('select_background_color');
    $this->configuration['with_brand_borders'] = $form_state->getValue('with_brand_borders');
    $this->configuration['overlaps_previous'] = $form_state->getValue('overlaps_previous');
    $this->configuration['override_text_color'] = $form_state->getValue('override_text_color');
    $this->configuration['social_feed_date_toggle'] = $form_state->getValue('social_feed_date_toggle');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = $this->getConfiguration();
    return [
      'label_display' => FALSE,
      'with_brand_borders' => $config['with_brand_borders'] ?? FALSE,
      'overlaps_previous' => $config['overlaps_previous'] ?? FALSE,
    ];
  }

  /**
   * Returns feed configuration.
   *
   * @return \Drupal\juicer_io\Entity\FeedConfiguration
   *   Feed configuration object.
   *
   * @throws \RuntimeException
   */
  private function getFeedConfig(): FeedConfiguration {
    if (!isset($this->configuration['feed'])) {
      throw new \RuntimeException('Feed id is missing from social feed block.');
    }

    /** @var \Drupal\juicer_io\Entity\FeedConfiguration|null $configEntity */
    $configEntity = $this->entityStorage->load($this->configuration['feed']);

    if (!$configEntity) {
      $message = sprintf(
        'Could not load feed config entity with id: %s',
        $this->configuration['feed']
      );
      throw new \RuntimeException($message);
    }
    return $configEntity;
  }

  /**
   * Returns cached feed items or generate new ones if needed.
   *
   * @return array
   *   Feed items array.
   *
   * @throws \Drupal\juicer_io\Model\FeedException
   * @throws \RuntimeException
   */
  private function getFeedItems(): array {
    $feedConfig = $this->getFeedConfig();
    $cacheKey = $this->getCacheKey();

    $cacheItem = $this->cacheBackend->get($cacheKey);
    if (!$cacheItem) {
      $feedItems = $this->generateFeedItems();
      $this->cacheBackend->set(
        $cacheKey,
        $feedItems,
        $this->timeService->getCurrentTime() + self::MAX_AGE_1_DAY,
        $feedConfig->getCacheTags()
      );
    }
    else {
      $feedItems = $cacheItem->data;
    }
    return $feedItems;
  }

  /**
   * Generate feed items array from a feed.
   *
   * @return array
   *   Feed items array.
   *
   * @throws \Drupal\juicer_io\Model\FeedException
   * @throws \RuntimeException
   */
  private function generateFeedItems() {
    $feedConfig = $this->getFeedConfig();
    $feed = $this->feedFactory->initFeed($feedConfig);
    $feedItems = $feed->getLatestItems(14, [Feed::TYPE_INSTAGRAM]);

    return array_map(
      function (FeedItem $item) {
        return $item->toArray();
      },
      $feedItems
    );
  }

  /**
   * Generates a cache key for storing feed items.
   *
   * @return string
   *   The cache key.
   *
   * @throws \RuntimeException
   */
  private function getCacheKey(): string {
    return implode(':', [
      'social_feed_block',
      'feed_items',
      $this->getFeedConfig()->getFeedId(),
    ]);
  }

}
