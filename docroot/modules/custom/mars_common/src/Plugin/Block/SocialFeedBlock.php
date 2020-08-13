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
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Social feed block.
 *
 * @Block(
 *   id = "social_feed",
 *   admin_label = @Translation("Social feed"),
 *   category = @Translation("Social feed"),
 * )
 */
class SocialFeedBlock extends BlockBase implements ContainerFactoryPluginInterface {

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

    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_storage,
      $feed_factory,
      $time_service,
      $cache_backend
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
    CacheBackendInterface $cache_backend
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityStorage = $entity_storage;
    $this->feedFactory = $feed_factory;
    $this->timeService = $time_service;
    $this->cacheBackend = $cache_backend;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $configEntity = $this->getFeedConfig();
    $label = $this->configuration['label'] ?? '';
    return [
      '#theme' => 'social_feed_block',
      '#label' => $label,
      '#items' => $this->getFeedItems(),
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
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 35,
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
    catch (RuntimeException $e) {
      // Cannot set default value as feed config is missing.
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['feed'] = $form_state->getValue('feed');
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
      throw new RuntimeException('Feed id is missing from social feed block.');
    }

    /** @var \Drupal\juicer_io\Entity\FeedConfiguration|null $configEntity */
    $configEntity = $this->entityStorage->load($this->configuration['feed']);

    if (!$configEntity) {
      $message = sprintf(
        'Could not load feed config entity with id: %s',
        $this->configuration['feed']
      );
      throw new RuntimeException($message);
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
